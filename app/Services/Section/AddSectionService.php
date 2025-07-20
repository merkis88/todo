<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Models\TelegraphChat;

class AddSectionService
{
    /**
     * Метод создаёт раздел (секцию) по введённому пользоватлем названию
     *
     * @param string $name - название раздела
     * @param TelegraphChat $chat - текущий чат
     * @return void
     */

    public function handle(string $name, TelegraphChat $chat): void
    {
        Section::create([
            'name' => $name,
            'telegraph_chat_id' => $chat->id,
        ]);

        $chat->message("✅ Раздел $name добавлен!\nТеперь можно добавлять задачи.")->send();
    }
}
