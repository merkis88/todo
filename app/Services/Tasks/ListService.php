<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class ListService
{
    public function handle(TelegraphChat $chat): void
    {
        $tasks = Task::where('telegraph_chat_id', $chat->id)->get();

        if ($tasks->isEmpty()) {
            $chat->message("Список задач пуст.")->send();
            return;
        }

        $message = "📝 Список задач:\n";
        foreach ($tasks as $task) {
            $status = $task->is_done ? '✅' : '⏳';
            $message .= "{$task->id}. {$task->title} {$status}\n";
        }

        $chat->message($message)->send();
    }
}
