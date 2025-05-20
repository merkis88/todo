<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    public function handle(TelegraphChat $chat): void
    {
        $tasks = Task::where('telegraph_chat_id', $chat->id)->get();

        if ($tasks->isEmpty()) {
            $chat->message("У тебя пока нет задач для экспорта.")->send();
            return;
        }

        $data = $tasks->map(fn($task) => [
            'title' => $task->title,
            'is_done' => $task->is_done,
            'created_at' => $task->created_at->toDateTimeString(),
        ]);

        $filename = "exports/tasks_{$chat->id}.json";
        Storage::disk('local')->put($filename, $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $fullPath = storage_path("app/{$filename}");
        $chat->document($fullPath, "tasks_{$chat->id}.json")->send();
        $chat->message("📎 Задачи экспортированы, смотри файл выше")->send();
    }
}
