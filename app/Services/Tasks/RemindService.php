<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class RemindService
{
    public function handle(int $id, string $delay, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        if (!$task) {
            $chat->message("Задача с № {$id} не найдена.")->send();
            return;
        }

        try {
            $remindAt = now()->add(CarbonInterval::make($delay));
        } catch (\Exception $e) {
            $chat->message("❌ Неверный формат времени (например, '10 minutes', '2 hours')")->send();
            return;
        }

        $task->remind_at = $remindAt;
        $task->save();

        $chat->message("🔔 Напоминание установлено на " . $remindAt->format('H:i d.m.Y'))->send();
    }
}
