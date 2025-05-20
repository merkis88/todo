<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class DeleteService
{
    public function handle(int $id, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)->where('telegraph_chat_id', $chat->id)->first();

        if (!$task) {
            $chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->delete();
        $chat->message("Задача № {$id} удалена.")->send();
    }
}

