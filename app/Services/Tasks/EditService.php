<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class EditService
{
    public function handle(int $id, string $newTitle, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)->where('telegraph_chat_id', $chat->id)->first();

        if (!$task) {
            $chat->message("🟥 Задача № {$id} не найдена")->send();
            return;
        }

        $task->title = $newTitle;
        $task->save();

        $chat->message("🟩 Задача № {$id} успешно изменена")->send();
    }
}
