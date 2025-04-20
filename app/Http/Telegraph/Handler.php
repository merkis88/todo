<?php

namespace App\Http\Telegraph;

use App\Models\Task;
use App\Services\OpenAIService;
use Illuminate\Support\Stringable;
use Illuminate\Support\Facades\Log;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use DefStudio\Telegraph\Handlers\WebhookHandler;

class Handler extends WebhookHandler
{

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
            'start' =>$this->startChat(),
            'add' => $this->addTask($args ?? ''),
            'list' => $this->listTasks(),
            'delete' => $this->deleteTask($args ?? ''),
            'done' => $this->doneTask($args ?? ''),
            'edit' =>$this->editTask($args ?? ''),
            'ask' =>$this->askGPT($args ?? ''),
            default => $this->chat->message("Неизвестная команда")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\nНапиши /add <задача> чтобы добавить задачу.\nНапиши /list чтобы посмотреть список.\nНапиши /delete <id> чтобы удалить задачу.")->send();
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
            $this->chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->delete();
        $this->chat->message("Задача № {$id} удалена.")->send();
    }

    protected function doneTask(string $id): void
    {
        $task = Task::find($id);

        if (!$task) {
            $this->chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        $task->is_done = true;
        $task->save();

        $this->chat->message("Задача № {$id} отмечена как выполненная ✅")->send();
    }

    protected function editTask(string $args): void
    {
        $parts = explode(' ', $args, 2);

        if (count($parts) < 2) {
            $this->chat->message("🟥 Возможно вы ввели не тот номер, либо не ввели новую задачу")->send();
            return;
        }

        [$id, $newTitile] = $parts;

        $task = Task::find($id);
        if (!$task) {
            $this->chat->message("🟥 Задча № {$id} не найдена")->send();
            return;
        }

        $task->title = $newTitile;
        $task->save();
        $this->chat->message("🟩 Задача № {$id} успешна изменна")->send();
    }


    protected function handleChatMessage(Stringable $text): void
    {
        $this->chat->action('typing')->send();

        $gpt = new OpenAIService();
        $response = $gpt->ask($text->toString());

        $this->chat->message($response)->send();
    }

    protected function askGPT()
    {

    }
}

