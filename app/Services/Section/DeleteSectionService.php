<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Models\TelegraphChat;

class DeleteSectionService
{
    public function handleByName(string $name, TelegraphChat $chat): void
    {
        $section = Section::where('name', $name)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$section) {
            $chat->message("❌ Раздел с названием '{$name}' не найден.")->send();
            return;
        }

        if ($section->tasks()->exists()) {
            $chat->message("⚠️ Раздел содержит задачи. Сначала удалите их.")->send();
            return;
        }

        $section->delete();
        $chat->message("🗑️ Раздел '{$name}' удалён.")->send();
    }
}
