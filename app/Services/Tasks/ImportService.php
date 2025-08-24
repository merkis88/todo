<?php

namespace App\Services\Tasks;

use App\Models\Section;
use App\Models\Task;
use Carbon\Carbon;
use DefStudio\Telegraph\DTO\Document;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportService
{
    public function handle(TelegraphChat $chat, Document $document): void
    {
        $chat->message("⏳ Начинаю импорт из файла `{$document->fileName()}`... ")->send();

        try {
            $bot = $chat->bot;
            $token = $bot->token;
            $fileId = $document->id();

            $fileInfoResponse = Http::get("https://api.telegram.org/bot{$token}/getFile", [
                'file_id' => $fileId,
            ]);

            if (!$fileInfoResponse->successful() || $fileInfoResponse->json('ok') !== true) {
                throw new \Exception('Не удалось получить информацию о файле от Telegram.');
            }

            $filePath = $fileInfoResponse->json('result.file_path');

            if (empty($filePath)) {
                throw new \Exception('Не удалось получить путь к файлу в ответе от Telegram.');
            }

            $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

            $response = Http::get($fileUrl);

            if (!$response->successful()) {
                throw new \Exception('Не удалось скачать файл по полученной ссылке.');
            }

            $this->processJsonContent($chat, $response->body());

        } catch (\Throwable $e) {
            Log::error("Ошибка при импорте файла", [
                'error' => $e->getMessage(),
                'chat_id' => $chat->id,
            ]);
            $chat->message("❌ Произошла ошибка при обработке файла: " . $e->getMessage())->send();
        }
    }

    private function processJsonContent(TelegraphChat $chat, string $content): void
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $chat->message("❌ Ошибка: Неверный формат JSON-файла.")->send();
            return;
        }

        $imported = 0;
        $skipped = 0;

        if (isset($data[0]['title'])) {
            $section = $this->getOrCreateSection($chat, 'Импортированные');
            foreach ($data as $taskData) {
                if ($this->createTask($chat, $taskData, $section->id)) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }
        } elseif (isset($data[0]['name']) && isset($data[0]['tasks'])) {
            foreach ($data as $sectionData) {
                $section = $this->getOrCreateSection($chat, $sectionData['name'] ?? 'Без названия');
                foreach ($sectionData['tasks'] as $taskData) {
                    if ($this->createTask($chat, $taskData, $section->id)) {
                        $imported++;
                    } else {
                        $skipped++;
                    }
                }
            }
        } else {
            $chat->message("❌ Не удалось распознать структуру JSON. Он должен быть либо массивом задач, либо массивом разделов с задачами.")->send();
            return;
        }

        $message = "✅ Импорт завершен!\n";
        $message .= "Импортировано задач: {$imported}";
        if ($skipped > 0) {
            $message .= "\nПропущено (без заголовка): {$skipped}";
        }

        $chat->message($message)->send();
    }

    private function createTask(TelegraphChat $chat, array $taskData, int $sectionId): bool
    {
        if (empty($taskData['title'])) {
            return false;
        }

        Task::create([
            'title' => $taskData['title'],
            'is_done' => $taskData['is_done'] ?? false,
            'created_at' => isset($taskData['created_at']) ? Carbon::parse($taskData['created_at']) : now(),
            'completed_at' => (isset($taskData['completed_at']) && $taskData['is_done']) ? Carbon::parse($taskData['completed_at']) : null,
            'telegraph_chat_id' => $chat->id,
            'section_id' => $sectionId,
        ]);

        return true;
    }

    private function getOrCreateSection(TelegraphChat $chat, string $name): Section
    {
        return Section::firstOrCreate(
            ['name' => $name, 'telegraph_chat_id' => $chat->id]
        );
    }
}
