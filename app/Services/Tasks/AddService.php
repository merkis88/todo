<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class AddService
{
    public function handle(string $title, TelegraphChat $chat): void
    {
        if (empty(trim($title))) {
            $chat->message("Нельзя создать пустую задачу!")->send();
            return;
        }

        Task::create([
            'title' => $title,
            'telegraph_chat_id' => $chat->id,
        ]);

        $chat->message("Задача добавлена: $title")->send();
    }
}
