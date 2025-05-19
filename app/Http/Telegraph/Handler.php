<?php

namespace App\Http\Telegraph;

use Illuminate\Support\Stringable;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use App\Services\TaskService;
use App\Services\DeepSeekService;

class Handler extends WebhookHandler
{
    protected TaskService $taskService;
    protected DeepSeekService $deepSeekService;

    public function __construct()
    {
        $this->taskService = app(TaskService::class);
        $this->deepSeekService = app(DeepSeekService::class);
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

        match ($command) {
            'start' => $this->startChat(),
            'add' => $this->taskService->addTask($args ?? '', $this->chat),
            'list' => $this->taskService->listTasks($this->chat),
            'delete' => $this->taskService->deleteTask($args ?? '', $this->chat),
            'done' => $this->taskService->doneTask($args ?? '', $this->chat),
            'edit' => $this->taskService->editTask($args ?? '', $this->chat),
            default => $this->chat->message("Неизвестная команда")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\n\n📌 Команды:\n/add <задача> — добавить задачу\n/list — список задач\n/delete <id> — удалить\n/done <id> — отметить выполненной\n/edit <id> <новый текст> — изменить\n\n🧠 А можешь просто спросить что-то, и я подключу мозги 😉")->send();
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->chat->action('typing')->send();

        try {
            $response = $this->deepSeekService->ask($text->toString());
            $this->chat->message(substr($response, 0, 4000))->send(); // Telegram limit
        } catch (\Throwable $e) {
            $this->chat->message("❌ Ошибка при обращении к DeepSeek")->send();
        }
    }

    public function handleUnknownCommand(Stringable $text): void
    {
        $this->handleChatMessage($text);
    }
}
