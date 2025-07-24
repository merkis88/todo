<?php

namespace App\Services\Section;

use App\Models\Section;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class ListSectionService
{
    public function handle(TelegraphChat $chat): void
    {
        $sections = Section::where('telegraph_chat_id', $chat->id)->get();

        if ($sections->isEmpty()) {
            $chat->message("У вас пока нет ни одного раздела 📕")->send();
            return;
        }

        $keyboard = Keyboard::make();

        foreach ($sections as $section) {
            $keyboard->row([
                Button::make("📂 {$section->name}")
                    ->action('list_tasks') // или открой задачи в этом разделе
                    ->param('section_id', $section->id),
            ]);
        }

        $chat->message("📚 Ваши разделы:")->keyboard($keyboard)->send();
    }
}
