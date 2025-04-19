<?php

namespace App\Http\Telegraph;

use App\Models\Task;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Log;
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
        $input = trim($text->toString());

        if (empty($input)) {
            $this->chat->message("Команда пуста")->send();
            return;
        }

        [$command, $args] = $this->parseCommand($text);
        $command = ltrim($command, '/');
        Log::info('Parsed command', ['command' => $command, 'arguments' => $args]);

        match ($command) {
            'add' => $this->addTask($args ?? ''),
            'list' => $this->listTasks(),
            'delete' => $this->deleteTask($args ?? ''),
            'done' => $this->doneTask($args ?? ''),
            default => $this->chat->message("Неизвестная команда")->send(),
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
            $status = $task->is_done ? '✅' : '⏳';
            $message .= "{$task->id}. {$task->title} {$status}\n";

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

    protected function doneTask(string $id): void
    {
        $task = Task::find($id);

        if (!$task) {
            $this->chat->message("Задача с ID {$id} не найдена.")->send();
            return;
        }

        $task->is_done = true;
        $task->save();

        $this->chat->message("Задача {$id} отмечена как выполненная ✅")->send();
    }
}

