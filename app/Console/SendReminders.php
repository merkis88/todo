<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class SendReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Отправить напоминания пользователям в Telegram';

    public function handle()
    {
        $now = Carbon::now()->startOfMinute();

        $tasks = Task::whereNotNull('remind_at')
            ->where('remind_at', '<=', $now)
            ->where('is_done', false)
            ->with('chat')
            ->get();

        foreach ($tasks as $task) {
            if ($task->chat) {
                $task->chat->message("🔔 Напоминание: {$task->title}")->send();
                $task->remind_at = null;
                $task->save();
            }
        }

        $this->info("Отправлено напоминаний: " . $tasks->count());
    }
}
