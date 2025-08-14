<?php

namespace App\Jobs;

use App\Services\DeepSeekService;
use App\Services\Speech\SpeechToTextService;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVoiceMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Ставим 1 попытку, чтобы не ждать повторов при отладке
    public int $timeout = 120;
    protected string $fileId;
    protected int $chatId;

    public function __construct(string $fileId, int $chatId)
    {
        $this->fileId = $fileId;
        $this->chatId = $chatId;
    }

    public function handle(SpeechToTextService $speechService, DeepSeekService $deepSeekService): void
    {
        Log::info('------------------- [JOB STARTED] -------------------');
        Log::info("[JOB] Обработка файла {$this->fileId} для чата {$this->chatId}");

        $chat = TelegraphChat::find($this->chatId);
        if (!$chat) {
            Log::warning("[JOB FAILED] Чат с ID {$this->chatId} не найден.");
            return;
        }

        $tempOggPath = null;
        $tempWavPath = null;

        try {
            Log::info("[1/5] Получение URL файла из Telegram...");
            $fileUrl = $this->getTelegramFileUrl($this->fileId);
            Log::info("[1/5] URL получен: " . $fileUrl);

            Log::info("[2/5] Скачивание файла...");
            $fileContent = Http::get($fileUrl)->body();
            $tempOggPath = storage_path('app/voices/' . $this->fileId . '.ogg');
            Storage::disk('local')->put('voices/' . $this->fileId . '.ogg', $fileContent);
            Log::info("[2/5] Файл сохранен в: " . $tempOggPath);

            Log::info("[3/5] Обращение к SpeechToTextService для распознавания...");
            $recognizedText = $speechService->recognize($tempOggPath);
            $tempWavPath = str_replace('.ogg', '.wav', $tempOggPath);
            Log::info("[3/5] Текст распознан: '{$recognizedText}'");

            if (empty($recognizedText)) {
                Log::warning("[JOB] Распознавание речи не дало результата. Завершаю работу.");
                $chat->message("Не смог распознать речь в вашем сообщении. 🤫")->send();
                return;
            }

            Log::info("[4/5] Обращение к DeepSeekService...");
            $response = $deepSeekService->ask($recognizedText, "Отвечай на русском языке. Будь полезным ассистентом.");
            Log::info("[4/5] Ответ от DeepSeek получен.");

            Log::info("[5/5] Отправка ответа пользователю...");
            $chat->message($response)->send();
            Log::info("[5/5] Ответ успешно отправлен.");

        } catch (\Throwable $e) {
            Log::error("[JOB FAILED] Критическая ошибка при обработке голоса: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $chat->message("Произошла ошибка при обработке вашего голосового сообщения. 😥")->send();
        } finally {
            if ($tempOggPath && file_exists($tempOggPath)) unlink($tempOggPath);
            if ($tempWavPath && file_exists($tempWavPath)) unlink($tempWavPath);
            Log::info("[CLEANUP] Временные файлы удалены.");
            Log::info('------------------- [JOB FINISHED] ------------------');
        }
    }

    private function getTelegramFileUrl(string $fileId): string
    {
        $token = config('telegraph.bot_token') ?? config('telegraph.bots.default.token');
        $response = Http::get("https://api.telegram.org/bot{$token}/getFile", ['file_id' => $fileId]);

        if (!$response->successful() || !$response->json('ok')) {
            throw new \Exception('Telegram API: Не удалось получить информацию о файле. ' . $response->body());
        }

        $filePath = $response->json('result.file_path');
        return "https://api.telegram.org/file/bot{$token}/{$filePath}";
    }
}
