<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    public function handle(TelegraphChat $chat, string $path): void
    {
        if (!Storage::disk('local')->exists($path)) {
            $chat->message("❌ Файл не найден: {$path}")->send();
            return;
        }

        $content = Storage::disk('local')->get($path);
        $tasks = json_decode($content, true);

        if (!is_array($tasks)) {
            $chat->message("❌ Неверный формат JSON-файла.")->send();
            return;
        }

        $imported = 0;

        foreach ($tasks as $taskData) {
            if (!isset($taskData['title'])) continue;

            Task::create([
                'title' => $taskData['title'],
                'is_done' => $taskData['is_done'] ?? false,
                'created_at' => isset($taskData['created_at']) ? Carbon::parse($taskData['created_at']) : now(),
                'telegraph_chat_id' => $chat->id,
            ]);

            $imported++;
        }

        $chat->message("✅ Импортировано задач: {$imported}")->send();
    }
}
