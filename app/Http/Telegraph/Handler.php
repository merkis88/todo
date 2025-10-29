<?php

namespace App\Http\Telegraph;

use App\Jobs\ProcessVoiceMessage;
use App\Models\Section;
use App\Services\DeepSeekService;
use App\Services\Section\AddSectionService;
use App\Services\Section\DeleteSectionService;
use App\Services\Section\ListSectionService;
use App\Services\Tasks\AddService;
use App\Services\Tasks\DeleteService;
use App\Services\Tasks\DoneService;
use App\Services\Tasks\EditService;
use App\Services\Tasks\ExportService;
use App\Services\Tasks\FilterService;
use App\Services\Tasks\ImportService;
use App\Services\Tasks\ListService as TasksListService;
use App\Services\Tasks\RemindService;
use DefStudio\Telegraph\DTO\Document;
use DefStudio\Telegraph\DTO\Voice;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Illuminate\Support\Str;


class Handler extends WebhookHandler
{
    protected AddService $addService;
    protected TasksListService $listService;
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
        $this->listService = app(TasksListService::class);
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

    public function handle(Request $request, TelegraphBot $bot): void
    {
        $telegramMessage = $request->input('message');
        $isCommand = isset($telegramMessage['text']) && Str::startsWith($telegramMessage['text'], '/');

        if ($isCommand) {
            $this->bot = $bot;
            $chatId = $telegramMessage['chat']['id'] ?? null;
            if ($chatId) {
                $this->chat = $this->bot->chats()->firstOrNew(['chat_id' => $chatId]);
            }
            $this->processCommand(new Stringable($telegramMessage['text']));
            return;
        }

        parent::handle($request, $bot);

        if ($this->message?->document()) {
            $this->processDocumentMessage($this->message->document());
            return;
        }

        if ($this->message?->voice()) {
            $this->processVoiceMessage($this->message->voice());
            return;
        }

        if ($this->message?->text()) {
            $this->processTextMessage(new Stringable($this->message->text()));
            return;
        }
    }

    protected function processDocumentMessage(Document $document): void
    {
        $cacheKeyAwaitingImport = "chat_{$this->chat->chat_id}_awaiting_import";

        if (cache()->pull($cacheKeyAwaitingImport)) {
            if (!Str::endsWith($document->fileName(), '.json')) {
                $this->chat->message("❗️Поддерживаются только JSON файлы для импорта.")->send();
                return;
            }

            $this->importService->handle($this->chat, $document);

        } else {
            $this->chat->message("📄 Я получил файл, но не знаю, что с ним делать. Если вы хотите импортировать задачи, сначала используйте команду /import.")->send();
        }
    }

    protected function handleImportCommand(?string $args): void
    {
        if (empty($args)) {
            cache()->put("chat_{$this->chat->chat_id}_awaiting_import", true, now()->addMinutes(5));
            $this->chat->message("➡️ Отправьте мне JSON файл с задачами для импорта: ")->send();
            return;
        }

        $this->chat->message("Импорт по имени файла с сервера временно отключен. Используйте `/import` и отправьте файл.")->send();
    }

    public function processVoiceMessage(Voice $voice): void
    {
        $this->chat->message('Принял, обрабатываю в фоне... 🎤')->send();
        ProcessVoiceMessage::dispatch($voice->id(), $this->chat->id);
    }

    protected function processTextMessage(Stringable $text): void
    {
        $cacheKeyAwaitingSection = "chat_{$this->chat->chat_id}_awaiting_section";
        $cacheKeyEditId = "chat_{$this->chat->chat_id}_edit_id";
        $cacheKeyTaskSection = "chat_{$this->chat->chat_id}_selected_section_for_task";
        $cacheKeyAwaitingFilter = "awaiting_filter_{$this->chat->chat_id}";
        $awaitingRemindKey = "chat_{$this->chat->chat_id}_remind";

        if (cache()->has($cacheKeyEditId)) {
            $id = cache()->pull($cacheKeyEditId);
            $this->editService->handle((int)$id, $text->toString(), $this->chat);
            return;
        }

        if (cache()->pull($cacheKeyAwaitingSection)) {
            $this->addSectionService->handle($text->toString(), $this->chat);
            return;
        }

        if (cache()->has($cacheKeyTaskSection)) {
            $sectionId = cache()->pull($cacheKeyTaskSection);
            $this->addService->handle($text->toString(), $this->chat, (int)$sectionId);
            return;
        }

        if (cache()->pull($cacheKeyAwaitingFilter)) {
            $this->filterService->handle($this->chat, $text->toString());
            return;
        }

        if (cache()->has($awaitingRemindKey)) {
            $id = cache()->pull($awaitingRemindKey);
            $delay = $text->toString();
            $this->remindService->handle($id, $delay, $this->chat);
            return;
        }

        $this->chat->action('typing')->send();
        try {
            $response = $this->deepSeekService->ask($text->toString());
            $this->chat->message($response)->send();
        } catch (\Throwable $e) {
            $this->chat->message("Ошибка при обращении к нейросети.")->send();
        }
    }

    protected function processCommand(Stringable $text): void
    {
        $fullText = $text->toString();
        $parts = explode(' ', $fullText, 2);
        $command = ltrim($parts[0], '/');
        $args = $parts[1] ?? '';

        match ($command) {
            'start' => $this->startChat(),
            'add' => $this->add_task_mode(),
            'list' => $this->listService->handle($this->chat),
            'listsection' => $this->list_sections(),
            'addsection' => $this->add_section_mode(),
            'deletesection' => $this->delete_section_mode(),
            'export' => $this->exportService->handle($this->chat),

            'delete' => $this->handleDeleteCommand($args),
            'done' => $this->handleDoneCommand($args),
            'edit' => $this->handleEditCommand($args),
            'filter' => $this->handleFilterCommand(),
            'import' => $this->handleImportCommand($args),

            default => $this->chat->message("Неизвестная команда: `/$command`")->send(),
        };
    }

    protected function handleDeleteCommand(?string $args): void
    {
        if (empty($args) || !is_numeric($args)) {
            $this->chat->message("Пожалуйста, укажите ID задачи. Например: `/delete 123`")->send();
            return;
        }
        $this->deleteService->handle((int)$args, $this->chat);
    }

    protected function handleDoneCommand(?string $args): void
    {
        if (empty($args) || !is_numeric($args)) {
            $this->chat->message("Пожалуйста, укажите ID задачи. Например: `/done 123`")->send();
            return;
        }
        $this->doneService->handle((int)$args, $this->chat);
    }

    public function startChat(): void
    {
        $this->chat->message(
            "Здравствуйте! Я ваш Telegram-менеджер для дел \n\n" .
            "Вы можете:\n" .
            "•  говорить голосом — я пойму и создам задачу\n" .
            "•  задавать текстом — и я сам определю раздел\n" .
            "•  управлять задачами и разделами\n\n" .
            "Давайте начнём с разделов!"
        )->keyboard(
            Keyboard::make()->buttons([
                Button::make("Создать раздел")->action('add_section_mode'),
            ])
        )->send();
    }

    public function add_section_mode(): void
    {
        cache()->put("chat_{$this->chat->chat_id}_awaiting_section", true, now()->addMinutes(5));
        $this->chat->message("Введите название нового раздела:")->send();
    }

    public function delete_section_mode(): void
    {
        $sections = Section::where('telegraph_chat_id', $this->chat->id)->get();

        if ($sections->isEmpty()) {
            $this->chat->message("У вас нет ни одного раздела для удаления")->send();
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($sections as $section) {
            $keyboard->buttons([
                Button::make(" " . $section->name)
                    ->action('confirm_delete_section')
                    ->param('section_id', $section->id),
            ]);
        }

        $this->chat->message("Выберите раздел, который хотите удалить:")->keyboard($keyboard)->send();
    }

    public function confirm_delete_section(): void
    {
        $sectionId = (int)$this->data->get('section_id');
        $this->deleteSectionService->handle($sectionId, $this->chat);
    }

    public function add_task_mode(): void
    {
        $sections = Section::where('telegraph_chat_id', $this->chat->id)->get();

        if ($sections->isEmpty()) {
            $this->chat->message("У вас пока нет разделов. Сначала создайте один.")->send();
            return;
        }

        $keyboard = Keyboard::make();
        foreach ($sections as $section) {
            $keyboard->buttons([
                Button::make($section->name)->action('select_section_for_task')->param('section_id', $section->id),
            ]);
        }

        $this->chat->message("Выберите раздел, в который хотите добавить задачу:")->keyboard($keyboard)->send();
    }

    public function select_section_for_task(): void
    {
        $sectionId = $this->data->get('section_id');
        cache()->put("chat_{$this->chat->chat_id}_selected_section_for_task", $sectionId, now()->addMinutes(5));
        $this->chat->message("Введите текст задачи:")->send();
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
        $this->doneService->handle((int)$this->data->get('id'), $this->chat);
    }

    public function delete_task(): void
    {
        $this->deleteService->handle((int)$this->data->get('id'), $this->chat);
    }

    public function edit_task(): void
    {
        $id = (int)$this->data->get('id');
        cache()->put("chat_{$this->chat->chat_id}_edit_id", $id, now()->addMinutes(5));
        $this->chat->message("Введите новый текст задачи:")->send();
    }

    public function remind_task(): void
    {
        $id = (int)$this->data->get('id');
        cache()->put("chat_{$this->chat->chat_id}_remind", $id, now()->addMinutes(5));
        $this->chat->message("Через сколько надо напомнить о задаче ? (Например: через 10 минут, завтра в 12)")->send();
    }

    protected function handleEditCommand(?string $args): void
    {
        if (empty($args)) {
            $this->chat->message("Используй: /edit <id> <новый текст>")->send();
            return;
        }
        $parts = explode(' ', $args, 2);
        $id = $parts[0];
        $newTitle = $parts[1] ?? '';

        if (!is_numeric($id) || empty($newTitle)) {
            $this->chat->message("Формат: /edit <id> <новый текст>")->send();
            return;
        }
        $this->editService->handle((int)$id, $newTitle, $this->chat);
    }

    protected function handleFilterCommand(): void
    {
        cache()->put("awaiting_filter_{$this->chat->chat_id}", true, now()->addMinutes(5));
        $this->chat->message("Введите критерии фильтрации (например: после 20.06.2025 выполненные отчет):")->send();
    }
}
