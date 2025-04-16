<?php

namespace App\Http\Telegraph;

use App\Models\Task;
use Illuminate\Support\Stringable;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Handlers\WebhookHandler;

class Handler extends WebhookHandler
{
    public function start(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\nНапиши /add <задача> чтобы добавить задачу.\nНапиши /list чтобы посмотреть список.\nНапиши /delete <id> чтобы удалить задачу.")->send();
    }

    public function handleCommand(Stringable $text): void
    {
        [$command, $args] = explode(' ', $text->toString(), 2) + [null, null];

        match ($command) {
            'add' => $this->addTask($args ?? ''),
            'list' => $this->listTasks(),
            'delete' => $this->deleteTask($args ?? ''),
            default => $this->chat->message("Неизвестная команда ")->send(),
        };
    }

    protected function addTask(string $title): void
    {
        if (empty(trim($title))) {
            $this->chat->message("Нельзя создать пустую задачу!")->send();
            return;
        }

        Task::create(['title' => $title]);
        $this->chat->message("Задача добавлена: $title")->send();
    }

    protected function listTasks(): void
    {
        $tasks = Task::all();

        if ($tasks->isEmpty()) {
            $this->chat->message("Список задач пуст.")->send();
            return;
        }

        $message = "📝 Список задач:\n";
        foreach ($tasks as $task) {
            $message .= "{$task->id}. {$task->title}\n";
        }

        $this->chat->message($message)->send();
    }

    protected function deleteTask(string $id): void
    {
        $task = Task::find($id);
        if (!$task) {
            $this->chat->message("Задача с ID {$id} не найдена.")->send();
            return;
        }

        $task->delete();
        $this->chat->message("Задача {$id} удалена.")->send();
    }
}

