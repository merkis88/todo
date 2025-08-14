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

    public int $tries = 3;
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
        $chat = TelegraphChat::find($this->chatId);
        if (!$chat) {
            Log::warning("[Job] Чат {$this->chatId} не найден.");
            return;
        }

        $tempOggPath = null;
        $tempWavPath = null;

        try {
            $fileUrl = $this->getTelegramFileUrl($this->fileId);
            $fileContent = Http::get($fileUrl)->body();
            // Используем file_id для уникальности имени файла
            $tempOggPath = storage_path('app/voices/' . $this->fileId . '.ogg');
            Storage::disk('local')->put('voices/' . $this->fileId . '.ogg', $fileContent);

            $recognizedText = $speechService->recognize($tempOggPath);
            $tempWavPath = str_replace('.ogg', '.wav', $tempOggPath);

            if (empty($recognizedText)) {
                $chat->message("Не смог распознать речь в сообщении. 🤫")->send();
                return;
            }

            $response = $deepSeekService->ask($recognizedText, "Отвечай на русском языке. Будь полезным ассистентом.");

            $chat->message($response)->send();

        } catch (\Throwable $e) {
            Log::error("[Job] Ошибка обработки голоса: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $chat->message("Произошла ошибка при обработке вашего голосового сообщения. 😥")->send();
        } finally {
            if ($tempOggPath && file_exists($tempOggPath)) {
                unlink($tempOggPath);
            }
            if ($tempWavPath && file_exists($tempWavPath)) {
                unlink($tempWavPath);
            }
        }
    }

    private function getTelegramFileUrl(string $fileId): string
    {
        $token = config('telegraph.bot_token');
        if (empty($token)) {
            $token = config('telegraph.bots.default.token');
        }

        $response = Http::get("https://api.telegram.org/bot{$token}/getFile", ['file_id' => $fileId]);

        if (!$response->successful() || !$response->json('ok')) {
            throw new \Exception('Telegram API: Не удалось получить информацию о файле. ' . $response->body());
        }

        $filePath = $response->json('result.file_path');
        return "https://api.telegram.org/file/bot{$token}/{$filePath}";
    }
}
