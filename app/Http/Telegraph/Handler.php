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
            'delete' => $this->taskService->deleteTask((int) $args, $this->chat),
            'done' => $this->taskService->doneTask((int) $args, $this->chat),
            'edit' => $this->handleEditCommand($args),
            'filter' => $this->handleFilterCommand($args),
            'export' => $this->taskService->exportTasks($this->chat),
            default => $this->chat->message("Неизвестная команда")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\n\n📌 Команды:\n/add <задача> — добавить задачу\n/list — список задач\n/delete <id> — удалить\n/done <id> — отметить выполненной\n/edit <id> <новый текст> — изменить")->send();
    }

    protected function handleEditCommand(?string $args): void
    {
        if (empty($args)) {
            $this->chat->message("⚠️ Используй: /edit <id> <новый текст>")->send();
            return;
        }

        $parts = explode(' ', $args);
        $id = array_shift($parts);
        $newTitle = implode(' ', $parts);

        if (!is_numeric($id) || empty($newTitle)) {
            $this->chat->message("⚠️ Формат: /edit <id> <новый текст>")->send();
            return;
        }

        $this->taskService->editTask((int)$id, $newTitle, $this->chat);
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->chat->action('typing')->send();

        try {
            $response = $this->deepSeekService->ask($text->toString());
            $this->chat->message(substr($response, 0, 4000))->send();
        } catch (\Throwable $e) {
            $this->chat->message("❌ Ошибка при обращении к GPT")->send();
        }
    }

    public function handleUnknownCommand(Stringable $text): void
    {
        $this->handleChatMessage($text);
    }

    protected function handleFilterCommand(?string $args): void
    {
        $filters = $this->parseFilters($args ?? '');
        $this->taskService->filterTasks($this->chat, $filters);
    }

    protected function parseFilters(string $args): array
    {
        $filters = [
            'is_done' => null,
            'word' => null,
            'after' => null,
        ];

        if (str_contains($args, 'выполненные')) {
            $filters['is_done'] = true;
        }

        if (str_contains($args, 'невыполненные')) {
            $filters['is_done'] = false;
        }

        if (preg_match('/после (\d{2}\.\d{2}\.\d{4})/', $args, $match)) {
            $filters['after'] = \Carbon\Carbon::createFromFormat('d.m.Y', $match[1]);
        }

        $clean = str_replace(['выполненные', 'невыполненные'], '', $args);
        $clean = preg_replace('/после \d{2}\.\d{2}\.\d{4}/', '', $clean);
        $clean = trim($clean);

        if (!empty($clean)) {
            $filters['word'] = $clean;
        }

        return $filters;
    }

}
