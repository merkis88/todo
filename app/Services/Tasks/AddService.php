<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\Section;
use DefStudio\Telegraph\Models\TelegraphChat;

class AddService
{
    /**
     * @param string $title - название задачи
     * @param TelegraphChat $chat - текущий чат
     * @param int $sectionId - ID раздела, в который добавляется задача
     */
    public function handleWithSection(string $title, TelegraphChat $chat, int $sectionId): void
    {
        $title = trim($title);

        if (empty($title)) {
            $chat->message("⚠️ Нельзя создать пустую задачу")->send();
            return;
        }

        $section = Section::where('id', $sectionId)->where('telegraph_chat_id', $chat->id)->first();

        if (!$section) {
            $chat->message("❌ Ошибка: Выбранный раздел не найден.")->send();
            return;
        }

        Task::create([
            'title' => $title,
            'telegraph_chat_id' => $chat->id,
            'section_id' => $sectionId,
            'is_done' => false,
        ]);

        $chat->message("✅ Задача добавлена в раздел '{$section->name}':\n$title")->send();
    }
}
