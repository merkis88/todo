<?php
namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;
use App\Services\Tasks\ListService;

class FilterService
{
    protected ListService $listService;

    public function __construct(ListService $listService) {
        $this->listService = $listService;
    }

    public function handle(TelegraphChat $chat, string $rawFilterText): void
    {
        $filters = [ 'is_done' => null, 'word' => null, 'after' => null ];

        if (str_contains($rawFilterText, 'выполненные')) $filters['is_done'] = true;
        if (str_contains($rawFilterText, 'невыполненные')) $filters['is_done'] = false;

        if (preg_match('/после (\d{2}\.\d{2}\.\d{4})/', $rawFilterText, $match)) {
            $filters['after'] = \Carbon\Carbon::createFromFormat('d.m.Y', $match[1]);
        }

        $clean = str_replace(['выполненные', 'невыполненные'], '', $rawFilterText);
        $clean = preg_replace('/после \d{2}\.\d{2}\.\d{4}/', '', $clean);
        $clean = trim($clean);

        if (!empty($clean)) {
            $filters['word'] = $clean;
        }

        $query = Task::where('telegraph_chat_id', $chat->id);

        if (!is_null($filters['is_done'])) {
            $query->where('is_done', $filters['is_done']);
        }

        if (!empty($filters['word'])) {
            $query->where('title', 'like', '%' . $filters['word'] . '%');
        }

        if (!empty($filters['after'])) {
            $query->whereDate('created_at', '>=', $filters['after']);
        }

        $tasks = $query->orderBy('id', 'desc')->get();

        if ($tasks->isEmpty()) {
            $chat->message("Ничего не найдено по фильтру")->send();
            return;
        }

        $chat->message("📎 Результаты фильтрации:")->send();
        $this->listService->handle($chat, null, $tasks);
    }
}
