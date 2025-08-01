<?php

namespace App\Http\Telegraph;

use Illuminate\Support\Stringable;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\Button;
use App\Services\Section\AddSectionService;
use App\Services\Section\ListSectionService;
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
use App\Services\Section\DeleteSectionService;
use App\Models\Section;

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
    protected AddSectionService $addSectionService;
    protected ListSectionService $listSectionService;
    protected DeleteSectionService $deleteSectionService;

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
        $this->addSectionService = app(AddSectionService::class);
        $this->listSectionService = app(ListSectionService::class);
        $this->deleteSectionService = app(DeleteSectionService::class);
    }

    public function handleCommand(Stringable $text): void
    {
        $input = trim($text->toString());

        if (empty($input)) {
            $this->chat->message("⚠️ Команда пуста")->send();
            return;
        }

        [$command, $args] = $this->parseCommand($text);
        $command = ltrim($command, '/');

        match ($command) {
            'start' => $this->startChat(),
            'add' => $this->add(),
            'list' => $this->listService->handle($this->chat),
            'delete' => $this->deleteService->handle((int)$args, $this->chat),
            'done' => $this->doneService->handle((int)$args, $this->chat),
            'edit' => $this->handleEditCommand($args),
            'filter' => $this->handleFilterCommand($args),
            'export' => $this->exportService->handle($this->chat),
            'import' => $this->handleImportCommand($args),
            'remind' => $this->handleRemindCommand($args),
            'addsection' => $this->add_section_mode(),
            'deletesection' => $this->delete_section_mode(),
            default => $this->chat->message("❓ Неизвестная команда: /$command")->send(),
        };
    }

    public function startChat(): void
    {
        $this->chat->message(
            "👋 Здравствуйте! Я ваш Telegram-менеджер для дел 👾\n\n" .
            "Вы можете:\n" .
            "• 🎙 говорить голосом — я пойму и создам задачу\n" .
            "• 📝 задавать текстом — и я сам определю раздел\n" .
            "• 📂 управлять задачами и разделами\n\n" .
            "Давайте начнём с разделов!"
        )->keyboard(
            Keyboard::make()->buttons([
                Button::make("➕ Создать раздел")->action('add_section_mode'),
            ])
        )->send();
    }

    public function add_section_mode(): void
    {
        cache()->put("chat_{$this->chat->chat_id}_awaiting_section", true, now()->addMinutes(5));
        $this->chat->message("📝 Введите название нового раздела:")->send();
    }

    public function add(): void
    {
        $sections = Section::where('telegraph_chat_id', $this->chat->id)->get();

        if ($sections->isEmpty()) {
            $this->chat->message("Сначала нужно создать раздел.")->send();
            return;
        }

        $keyboard = Keyboard::make();

        foreach ($sections as $section) {
            $keyboard->row([
                Button::make($section->name)
                    ->action('choose_section_for_add')
                    ->param('section_id', $section->id),
            ]);
        }

        $this->chat->message("📂 Выберите раздел, в который вы хотите добавить задачу:")
            ->keyboard($keyboard)
            ->send();
    }

    public function choose_section_for_add(): void
    {
        $sectionId = (int) $this->data->get('section_id');

        if (!$sectionId) {
            $this->chat->message("❌ Ошибка: Не удалось определить раздел. Попробуйте снова.")->send();
            return;
        }

        $cacheKey = "chat_{$this->chat->chat_id}_add_task_section_id";
        cache()->put($cacheKey, $sectionId, now()->addMinutes(30));

        \Log::info("DEBUG: Установлен кэш-ключ для чата {$this->chat->chat_id}: {$cacheKey} со значением {$sectionId}");

        $this->chat->message("📝 Введите название задачи, которую нужно добавить в выбранный раздел:")->send();
    }


    public function delete_section_mode(): void
    {
        cache()->put("chat_{$this->chat->chat_id}_awaiting_section_delete", true, now()->addMinutes(5));
        $this->chat->message("✏️ Введите название раздела, который хотите удалить:")->send();
    }

    public function list_sections(): void
    {
        $this->listSectionService->handle($this->chat);
    }

    public function list_tasks(): void
    {
        $sectionId = $this->data->get('section_id');
        $this->listService->handle($this->chat, $sectionId ? (int)$sectionId : null);
    }

    public function done_task(): void
    {
        $this->doneService->handle((int) $this->data->get('id'), $this->chat);
    }

    public function delete_task(): void
    {
        $this->deleteService->handle((int) $this->data->get('id'), $this->chat);
    }

    public function edit_task(): void
    {
        $id = (int) $this->data->get('id');
        cache()->put("chat_{$this->chat->chat_id}_edit_id", $id, now()->addMinutes(5));
        $this->chat->message("✏️ Введите новый текст задачи:")->send();
    }

    public function handleText(Stringable $text): void
    {
        $editKey = "chat_{$this->chat->chat_id}_edit_id";
        $addSectionKey = "chat_{$this->chat->chat_id}_awaiting_section";
        $sectionDeleteKey = "chat_{$this->chat->chat_id}_awaiting_section_delete";
        $addTaskInSectionKey = "chat_{$this->chat->chat_id}_add_task_section_id";

        // 🔍 Лог при вызове handleText
        \Log::info("DEBUG: handleText() вызван. Попытка извлечь кэш-ключ: {$addTaskInSectionKey}");

        if ($editId = cache()->pull($editKey)) {
            $this->editService->handle((int)$editId, $text->toString(), $this->chat);
            return;
        }

        if (cache()->pull($addSectionKey)) {
            try {
                $this->addSectionService->handle($text->toString(), $this->chat);
            } catch (\Throwable $e) {
                $this->chat->message("❌ Ошибка: " . $e->getMessage())->send();
            }
            return;
        }

        if (cache()->pull($sectionDeleteKey)) {
            $this->deleteSectionService->handleByName($text->toString(), $this->chat);
            return;
        }

        if ($sectionId = cache()->pull($addTaskInSectionKey)) {
            \Log::info("DEBUG: Кэш-ключ для задачи найден. SectionId: {$sectionId}. Обрабатываем задачу.");

            try {
                $this->addService->handleWithSection($text->toString(), $this->chat, (int)$sectionId);
            } catch (\Throwable $e) {
                $this->chat->message("❌ Ошибка при добавлении задачи: " . $e->getMessage())->send();
            }

            return;
        }

        \Log::info("DEBUG: Кэш-ключ для задачи не найден. Переходим к следующим обработчикам.");

        if ($text->startsWith('/')) {
            $this->handleCommand($text);
        } else {
            $this->handleChatMessage($text);
        }
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
            'after' => null
        ];

        if (str_contains($args, 'выполненные')) $filters['is_done'] = true;
        if (str_contains($args, 'невыполненные')) $filters['is_done'] = false;

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
            $this->chat->message("📥 Используйте: /import <имя_файла.json>\nПример: /import tasks.json")->send();
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
        $this->remindService->handle((int)$id, $delay, $this->chat);
    }

    public function delete_section(): void
    {
        $sectionId = (int)$this->data->get('section_id');
        $this->deleteSectionService->handle($sectionId, $this->chat);
    }
}
