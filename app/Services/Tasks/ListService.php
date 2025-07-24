<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;

class ListService
{
    public function handle(TelegraphChat $chat, ?int $sectionId = null): void
    {
        $query = Task::where('telegraph_chat_id', $chat->id);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $chat->message("Список задач пуст.")->send();
            return;
        }

        $i = 1;

        foreach ($tasks as $task) {
            $status = $task->is_done ? '✅' : '⏳';
            $message = "{$i}. {$task->title} {$status}";

            $keyboard = Keyboard::make()->row([
                Button::make("✅")->action('done_task')->param('id', $task->id),
                Button::make("🗑️")->action('delete_task')->param('id', $task->id),
                Button::make("✏️")->action('edit_task')->param('id', $task->id),
            ]);

            $chat->message($message)->keyboard($keyboard)->send();
            $i++;
        }
    }

}
