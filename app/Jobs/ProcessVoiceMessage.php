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
            Ты — умный ассистент для менеджера задач. Проанализируй сообщение пользователя: "{$recognizedText}".
            Определи его НАМЕРЕНИЕ. Возможны два намерения:
            1. 'create_task': Пользователь хочет создать новую задачу, напоминание, встречу.
            2. 'chat': Пользователь просто задает вопрос, здоровается или хочет пообщаться.

            Если намерение — 'create_task', то выполни следующие действия:
            1. Извлеки суть задачи и сформулируй ее краткое название.
            2. Посмотри на список существующих разделов пользователя: [{$sectionsList}].
            3. Определи, какой из существующих разделов подходит для задачи. ВАЖНОЕ ПРАВИЛО: если ты не уверен в выборе на 95% или более, СЧИТАЙ, ЧТО ПОДХОДЯЩЕГО РАЗДЕЛА НЕТ.
            4. Если подходящий раздел найден, используй action "add_to_existing_section".
            5. Если подходящий раздел НЕ найден (или ты не уверен), придумай название для нового раздела и используй action "suggest_new_section".
            6. Верни JSON в формате:
               {
                 "intent": "create_task",
                 "task_title": "Название задачи",
                 "action": "add_to_existing_section" | "suggest_new_section",
                 "section_name": "Название раздела"
               }

            Если намерение — 'chat', просто верни JSON в формате:
               {
                 "intent": "chat"
               }

            Верни ответ ТОЛЬКО в формате JSON, без каких-либо других слов и пояснений.
            PROMPT;

            $rawDeepSeekResponse = $deepSeekService->ask($prompt);

            if (preg_match('/\{.*\}/s', $rawDeepSeekResponse, $matches)) {
                $jsonResponse = $matches[0];
            } else {
                throw new \Exception("Не удалось найти JSON в ответе DeepSeek.");
            }

            $data = json_decode($jsonResponse, true,JSON_THROW_ON_ERROR);

            $intent = $data['intent'] ?? null;

            if ($intent === 'create_task') {
                $taskTitle = $data['task_title'] ?? null;
                $action = $data['action'] ?? null;
                $sectionName = $data['section_name'] ?? null;

                if (!$taskTitle || !$action || !$sectionName) {
                    throw new \Exception("DeepSeek отдал некорректный JSON для создания задачи");
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

            } elseif ($intent === 'chat') {
                $chatResponse = $deepSeekService->ask($recognizedText, "Отвечай на русском языке. Будь полезным ассистентом.");
                $chat->message($chatResponse)->send();

            } else {
                throw new \Exception("DeepSeek не смог определить намерение пользователя.");
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
