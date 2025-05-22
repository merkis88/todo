<?php

namespace App\Http\Telegraph;

use Illuminate\Support\Stringable;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use App\Services\Tasks\AddService;
use App\Services\Tasks\ListService;
use App\Services\Tasks\DeleteService;
use App\Services\Tasks\DoneService;
use App\Services\Tasks\EditService;
use App\Services\Tasks\FilterService;
use App\Services\Tasks\ExportService;
use App\Services\Tasks\ImportService;
use App\Services\Tasks\RemindService;
use App\Services\DeepSeekService;

class Handler extends WebhookHandler
{
    protected AddService $addService;
    protected ListService $listService;
    protected DeleteService $deleteService;
    protected DoneService $doneService;
    protected EditService $editService;
    protected FilterService $filterService;
    protected ExportService $exportService;
    protected ImportService $importService;
    protected RemindService $remindService;
    protected DeepSeekService $deepSeekService;

    public function __construct()
    {
        $this->addService = app(AddService::class);
        $this->listService = app(ListService::class);
        $this->deleteService = app(DeleteService::class);
        $this->doneService = app(DoneService::class);
        $this->editService = app(EditService::class);
        $this->filterService = app(FilterService::class);
        $this->exportService = app(ExportService::class);
        $this->importService = app(ImportService::class);
        $this->remindService = app(RemindService::class);
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
            'add' => $this->addService->handle($args ?? '', $this->chat),
            'list' => $this->listService->handle($this->chat),
            'delete' => $this->deleteService->handle((int) $args, $this->chat),
            'done' => $this->doneService->handle((int) $args, $this->chat),
            'edit' => $this->handleEditCommand($args),
            'filter' => $this->handleFilterCommand($args),
            'export' => $this->exportService->handle($this->chat),
            'import' => $this->handleImportCommand($args), //
            'remind' => $this->handleRemindCommand($args),
            default => $this->chat->message("Неизвестная команда")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\n\n📌 Команды:\n/add <задача> — добавить задачу\n/list — список задач\n/delete <id> — удалить\n/done <id> — отметить выполненной\n/edit <id> <новый текст> — изменить\n/remind <id> <через сколько> — установить напоминание")->send();
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

        $this->editService->handle((int) $id, $newTitle, $this->chat);
    }

    protected function handleFilterCommand(?string $args): void
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

        $this->filterService->handle($this->chat, $filters);
    }

    protected function handleImportCommand(?string $args): void
    {
        if (empty($args)) {
            $this->chat->message("📥 Используйте: /import <имя_файла.json>\n\nПример: /import tasks_1.json")->send();
            return;
        }

        $filename = trim($args);
        $path = "exports/{$filename}";
        $this->importService->handle($this->chat, $path);
    }

    protected function handleRemindCommand(?string $args): void
    {
        if (empty($args)) {
            $this->chat->message("⚠️ Используй: /remind <id> <через сколько>")->send();
            return;
        }

        $parts = explode(' ', $args, 2);
        if (count($parts) < 2 || !is_numeric($parts[0])) {
            $this->chat->message("⚠️ Пример: /remind 3 10 minutes")->send();
            return;
        }

        [$id, $delay] = $parts;
        $this->remindService->handle((int) $id, $delay, $this->chat);
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
}
