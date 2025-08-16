<?php

namespace App\Jobs;

use App\Models\Section;
use App\Services\DeepSeekService;
use App\Services\Speech\SpeechToTextService;
use App\Services\Tasks\AddService;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
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

    public int $tries = 1;
    public int $timeout = 120;
    protected string $fileId;
    protected int $chatId;

    public function __construct(string $fileId, int $chatId)
    {
        $this->fileId = $fileId;
        $this->chatId = $chatId;
    }

    public function handle(SpeechToTextService $speechService, DeepSeekService $deepSeekService, AddService $addService): void
    {
        $chat = TelegraphChat::find($this->chatId);
        if (!$chat) {
            return;
        }

        $tempOggPath = null;
        $tempWavPath = null;
        $rawDeepSeekResponse = '';

        try {
            $fileUrl = $this->getTelegramFileUrl($this->fileId);
            $fileContent = Http::get($fileUrl)->body();
            $tempOggPath = storage_path('app/voices/' . $this->fileId . '.ogg');
            Storage::disk('local')->put('voices/' . $this->fileId . '.ogg', $fileContent);

            $recognizedText = $speechService->recognize($tempOggPath);
            $tempWavPath = str_replace('.ogg', '.wav', $tempOggPath);

            if (empty($recognizedText)) {
                $chat->message("Не смог распознать речь в вашем сообщении. 🤫")->send();
                return;
            }

            $sections = Section::where('telegraph_chat_id', $this->chatId)->pluck('name')->toArray();
            $sectionsList = !empty($sections) ? '"' . implode('", "', $sections) . '"' : 'Нет';

            $prompt = <<<PROMPT
            Ты - умный ассистент для менеджера задач. Пользователь сказал: "{$recognizedText}". Проанализируй этот текст и выполни следующие действия:
            1. Извлеки суть задачи. Сформулируй краткое название для этой задачи.
            2. Посмотри на список существующих разделов пользователя: [{$sectionsList}].
            3. Определи, какой из существующих разделов лучше всего подходит для этой задачи.
            4. Если ни один раздел не подходит, предложи название для нового, подходящего раздела.
            Верни ответ ТОЛЬКО в формате JSON, без каких-либо других слов и пояснений. Структура JSON должна быть следующей:
            {
              "task_title": "Название задачи",
              "action": "add_to_existing_section" | "suggest_new_section",
              "section_name": "Название существующего или нового раздела"
            }
            PROMPT;

            $rawDeepSeekResponse = $deepSeekService->ask($prompt);

            if (preg_match('/\{.*\}/s', $rawDeepSeekResponse, $matches)) {
                $jsonResponse = $matches[0]; // Забираем найденный чистый JSON
            } else {
                throw new \Exception("Не удалось найти JSON в ответе DeepSeek.");
            }

            $data = json_decode($jsonResponse, true, 512, JSON_THROW_ON_ERROR);

            $taskTitle = $data['task_title'] ?? null;
            $action = $data['action'] ?? null;
            $sectionName = $data['section_name'] ?? null;

            if (!$taskTitle || !$action || !$sectionName) {
                throw new \Exception("DeepSeek отдал некорректный JSON");
            }

            if ($action === 'add_to_existing_section') {
                $section = Section::where('telegraph_chat_id', $this->chatId)
                    ->where('name', $sectionName)
                    ->first();

                if ($section) {
                    $addService->handle($taskTitle, $chat, $section->id);
                } else {
                    $chat->message("🤔 ИИ-агент предложил добавить задачу '{$taskTitle}' в раздел '{$sectionName}', но я его не нашел. Попробуйте добавить вручную.")->send();
                }

            } elseif ($action === 'suggest_new_section') {
                $keyboard = Keyboard::make()->buttons([
                    Button::make("✅ Создать раздел и добавить")
                        ->action('confirm_add_task_with_new_section')
                        ->param('task_title', $taskTitle)
                        ->param('section_name', $sectionName),
                    Button::make("✍️ Добавить вручную")
                        ->action('add_task_mode'),
                ]);

                $chat->message("Я думаю, задача '{$taskTitle}' относится к новому разделу '{$sectionName}'. Что делаем?")
                    ->keyboard($keyboard)
                    ->send();
            }

        } catch (\Throwable $e) {
            Log::error("[JOB FAILED] Критическая ошибка при обработке голоса.", [
                'error' => $e->getMessage(),
                'deepseek_raw_response' => $rawDeepSeekResponse
            ]);
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
        $token = config('telegraph.bot_token') ?? config('telegraph.bots.default.token');
        $response = Http::get("https://api.telegram.org/bot{$token}/getFile", ['file_id' => $fileId]);

        if (!$response->successful() || !$response->json('ok')) {
            throw new \Exception('Telegram API: Не удалось получить информацию о файле. ' . $response->body());
        }

        $filePath = $response->json('result.file_path');
        return "https://api.telegram.org/file/bot{$token}/{$filePath}";
    }
}
