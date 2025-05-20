<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class FilterService
{
    public function handle(TelegraphChat $chat, array $filters): void
    {
        $query = Task::where('telegraph_chat_id', $chat->id);

        if (!is_null($filters['is_done'])) {
            $query->where('is_done', $filters['is_done']);
        }

        if (!empty($filters['word'])) {
            $query->where('title', 'like', '%' . $filters['word'] . '%');
        }

        if (!empty($filters['after'])) {
            $query->whereDate('created_at', '>=', $filters['after']);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $chat->message("Ничего не найдено по фильтру.")->send();
            return;
        }

        $message = "📎 Результаты фильтрации:\n";
        foreach ($tasks as $task) {
            $status = $task->is_done ? '✅' : '⏳';
            $message .= "{$task->id}. {$task->title} {$status}\n";
        }

        $chat->message($message)->send();
    }
}
