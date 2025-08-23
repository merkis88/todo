<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;

class FilterService
{
    public function handle(string $name, TelegraphChat $chat): void
    {
        $filters = [ 'is_done' => null, 'word' => null, 'after' => null ];

        if (str_contains($name, 'выполненные')) $filters['is_done'] = true;
        if (str_contains($name, 'невыполненные')) $filters['is_done'] = false;

        if (preg_match('/после (\d{2}\.\d{2}\.\d{4})/', $name, $match)) {
            $filters['after'] = \Carbon\Carbon::createFromFormat('d.m.Y', $match[1]);
        }

        $clean = str_replace(['выполненные', 'невыполненные'], '', $name);
        $clean = preg_replace('/после \d{2}\.\d{2}\.\d{4}/', '', $clean);
        $clean = trim($clean);

        if (!empty($clean)) {
            $filters['word'] = $clean;
        }

        $query = Task::where('telegraph_chat_id', $chat->id);

        if (!is_null($filters['is_done'])) {
            $query->where('is_done', $filters['is_done']);
        }

        if (!empty($filters['word'])) {
            $query->where('title', 'like', '%' . $filters['word'] . '%');
        }

        if (!empty($filters['after'])) {
            $query->whereDate('created_at', '>=', $filters['after']);
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $chat->message("Ничего не найдено по фильтру.")->send();
            return;
        }

        $message = "📎 Результаты фильтрации:\n";
        foreach ($tasks as $task) {
            $status = $task->is_done ? '✅' : '⏳';
            $message .= "{$task->id}. {$task->title} {$status}\n";
        }

        $chat->message($message)->send();
    }
}
