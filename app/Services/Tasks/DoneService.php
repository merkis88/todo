<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class DoneService
{
    public function handle(int $id, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)->where('telegraph_chat_id', $chat->id)->first();

        $taskTitle = $task->title;

        if (!$task) {
            $chat->message("Задача с № '{$taskTitle}' не найдена.")->send();
            return;
        }

        $task->is_done = true;
        $task->save();

        $chat->message("Задача № '{$taskTitle}' отмечена как выполненная ✅")->send();
    }
}
