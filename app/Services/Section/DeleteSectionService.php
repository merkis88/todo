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
            $chat->message("❌ Раздел не найден или не принадлежит вам.")->send();
            return;
        }

        if ($section->tasks()->exists()) {
            $chat->message("⚠️ Раздел содержит задачи. Сначала удалите их.")->send();
            return;
        }

        $section->delete();
        $chat->message("🗑️ Раздел удалён.")->send();
    }
}
