<?php

namespace App\Services;

use App\Models\Task;
use DefStudio\Telegraph\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class TaskService
{
    public function addTask(string $title, TelegraphChat $chat): void
    {
        if (empty(trim($title))) {
            $chat->message("Нельзя создать пустую задачу!")->send();
            return;
        }

        Task::create(['title' => $title]);
        $chat->message("Задача добавлена: $title")->send();
    }

    public function listTasks(TelegraphChat $chat): void
    {
        $tasks = Task::all();

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
        $task = Task::find($id);
        if (!$task) {
            $chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->delete();
        $chat->message("Задача № {$id} удалена.")->send();
    }

    public function doneTask(int $id, TelegraphChat $chat): void
    {
        $task = Task::find($id);
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
        $task = Task::find($id);
        if (!$task) {
            $chat->message("🟥 Задача № {$id} не найдена")->send();
            return;
        }

        $task->title = $newTitle;
        $task->save();
        $chat->message("🟩 Задача № {$id} успешно изменена")->send();
    }
}
