<?php

namespace App\Services;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function addTask(string $title, TelegraphChat $chat): void
    {
        if (empty(trim($title))) {
            $chat->message("Нельзя создать пустую задачу!")->send();
            return;
        }

        Task::create([
            'title' => $title,
            'telegraph_chat_id' => $chat->id,
        ]);

        $chat->message("Задача добавлена: $title")->send();
    }

    public function listTasks(TelegraphChat $chat): void
    {
        $tasks = Task::where('telegraph_chat_id', $chat->id)->get();

        if ($tasks->isEmpty()) {
            $chat->message("Список задач пуст.")->send();
            return;
        }

        $message = "📝 Список задач:\n";
        foreach ($tasks as $task) {
            $status = $task->is_done ? '✅' : '⏳';
            $message .= "{$task->id}. {$task->title} {$status}\n";
        }

        $chat->message($message)->send();
    }

    public function deleteTask(int $id, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$task) {
            $chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->delete();
        $chat->message("Задача № {$id} удалена.")->send();
    }

    public function doneTask(int $id, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$task) {
            $chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->is_done = true;
        $task->save();
        $chat->message("Задача № {$id} отмечена как выполненная ✅")->send();
    }

    public function editTask(int $id, string $newTitle, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$task) {
            $chat->message("🟥 Задача № {$id} не найдена")->send();
            return;
        }

        $task->title = $newTitle;
        $task->save();
        $chat->message("🟩 Задача № {$id} успешно изменена")->send();
    }

    public function filterTasks(TelegraphChat $chat, array $filters): void
    {
        $query = Task::query()->where('telegraph_chat_id', $chat->id);

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

    public function exportTasks(TelegraphChat $chat): void
    {
        $tasks = Task::where('telegraph_chat_id', $chat->id)->get();

        if ($tasks->isEmpty()) {
            $chat->message("У тебя пока нет задач для экспорта.")->send();
            return;
        }

        $data = $tasks->map(function ($task) {
            return [
                'title' => $task->title,
                'is_done' => $task->is_done,
                'created_at' => $task->created_at->toDateTimeString(),
            ];
        });

        $filename = "exports/tasks_{$chat->id}.json";
        \Storage::disk('local')->put($filename, $data->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $url = \URL::to("/download-tasks/{$chat->id}");

        $chat->message("📤 Готово! Вот ссылка на экспорт задач:\n{$url}")->send();
    }

}
