<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Models\TelegraphChat;

class AddSectionService
{
    /**
     * Метод создаёт раздел (секцию) по введённому пользователем названию
     *
     * @param string $name - название раздела
     * @param TelegraphChat $chat - текущий чат
     * @return void
     */
    public function handle(string $name, TelegraphChat $chat): void
    {
        $name = trim($name);

        // Проверка на пустое название
        if (empty($name)) {
            $chat->message("⚠️ Название раздела не может быть пустым.")->send();
            return;
        }

        // Защита от дубликатов
        if (Section::where('telegraph_chat_id', $chat->id)->where('name', $name)->exists()) {
            $chat->message("⚠️ Раздел '$name' уже существует.")->send();
            return;
        }

        Section::create([
            'name' => $name,
            'telegraph_chat_id' => $chat->id,
        ]);

        $chat->message("✅ Раздел '$name' добавлен!\nТеперь можно добавлять задачи.")->send();
    }
}
