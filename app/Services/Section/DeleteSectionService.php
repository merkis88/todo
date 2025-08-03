<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Models\TelegraphChat;

class DeleteSectionService
{
    public function handle(int $sectionId, TelegraphChat $chat): void
    {
        $section = Section::where('id', $sectionId)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$section) {
            $chat->message("❌ Раздел не найден")->send();
            return;
        }

        // Удаляем сам раздел и все задачи внутри него
        $section->tasks()->delete(); // удаляем задачи
        $section->delete(); // удаляем сам раздел

        $chat->message("🗑 Раздел и все его задачи удалены")->send();
    }
}
