<?php

namespace App\Http\Telegraph;

use App\Models\Task;
use App\Services\DeepSeekService;
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
            'start' => $this->startChat(),
            'add' => $this->addTask($args ?? ''),
            'list' => $this->listTasks(),
            'delete' => $this->deleteTask($args ?? ''),
            'done' => $this->doneTask($args ?? ''),
            'edit' => $this->editTask($args ?? ''),
            default => $this->chat->message("Неизвестная команда")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message("Привет! Я твой TODO-бот 🤖\n\n📌 Команды:\n/add <задача> — добавить задачу\n/list — список задач\n/delete <id> — удалить\n/done <id> — отметить выполненной\n/edit <id> <новый текст> — изменить\n\n🧠 А можешь просто спросить что-то, и я подключу мозги 😉")->send();
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
            $this->chat->message("🟥 Введите номер и новый текст задачи")->send();
            return;
        }

        [$id, $newTitle] = $parts;

        $task = Task::find($id);
        if (!$task) {
            $this->chat->message("🟥 Задача № {$id} не найдена")->send();
            return;
        }

        $task->title = $newTitle;
        $task->save();
        $this->chat->message("🟩 Задача № {$id} успешно изменена")->send();
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $this->chat->action('typing')->send();

        try {
            Log::info('🧠 Запрос к GPT', ['text' => $text->toString()]);

            $ds = new DeepSeekService();
            $response = $ds->ask($text->toString());

            Log::info('📥 Ответ от GPT', ['response' => $response]);

            $this->chat->message(substr($response, 0, 4000))->send(); // Telegram limit
        } catch (\Throwable $e) {
            Log::error('❌ Ошибка при обращении к DeepSeek', ['error' => $e->getMessage()]);
            $this->chat->message("❌ Ошибка при обращении к DeepSeek")->send();
        }
    }

    public function handleUnknownCommand(Stringable $text): void
    {
        $this->handleChatMessage($text);
    }
}
