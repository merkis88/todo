<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
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

        $section = Section::create([
            'name' => $name,
            'telegraph_chat_id' => $chat->id,
        ]);

        // Клавиатура после создания раздела
        $keyboard = Keyboard::make()->row([
            Button::make("➕ Добавить раздел")->action("add_section_mode"),
            Button::make("📝 Добавить задачу")->action("add_task_to_section")->param("section_id", $section->id),
        ])->row([
            Button::make("📚 Список разделов")->action("list_sections"),
        ]);

        $chat->message("✅ Раздел '$name' добавлен!\nТеперь можно добавлять задачи.")->keyboard($keyboard)->send();
    }
}
