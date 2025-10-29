<?php

namespace App\Services\Tasks;

use App\Models\Task;
use DefStudio\Telegraph\Models\TelegraphChat;
use Carbon\Carbon;

class RemindService
{
    public function handle(int $id, string $delay, TelegraphChat $chat): void
    {
        $task = Task::where('id', $id)
            ->where('telegraph_chat_id', $chat->id)
            ->first();

        try {
            $remindAt = $this->parseDelay($delay);
        } catch (\Exception $e) {
            $chat->message("❌ Неверный формат времени. Примеры: 'через 10 минут', 'завтра в 12:00', 'через 2 часа'")->send();
            return;
        }

        $task->remind_at = $remindAt;
        $task->save();

        $chat->message("🔔 Напоминание установлено на " . $remindAt->format('H:i d.m.Y'))->send();
    }

    private function parseDelay(string $delay): Carbon
    {
        $delay = mb_strtolower(trim($delay));

        $patterns = [

            '/через\s+(\d+)\s+минут/' => function($matches) {
                return now()->addMinutes((int)$matches[1]);
            },

            '/через\s+(\d+)\s+час/' => function($matches) {
                return now()->addHours((int)$matches[1]);
            },

            '/через\s+(\d+)\s+дн/' => function($matches) {
                return now()->addDays((int)$matches[1]);
            },

            '/завтра\s+в\s+(\d{1,2}):(\d{2})/' => function($matches) {
                return now()->addDay()->setTime((int)$matches[1], (int)$matches[2]);
            },

            '/сегодня\s+в\s+(\d{1,2}):(\d{2})/' => function($matches) {
                return now()->setTime((int)$matches[1], (int)$matches[2]);
            },
        ];

        foreach ($patterns as $pattern => $callback) {
            if (preg_match($pattern, $delay, $matches)) {
                return $callback($matches);
            }
        }

        try {
            $englishDelay = str_replace([
                'минут', 'минуты', 'минута',
                'часов', 'часа', 'час',
                'дней', 'дня', 'день'
            ], [
                'minutes', 'minutes', 'minute',
                'hours', 'hours', 'hour',
                'days', 'days', 'day'
            ], $delay);

            return now()->add(\Carbon\CarbonInterval::make($englishDelay));
        } catch (\Exception $e) {
            throw new \Exception("Не удалось распарсить время: {$delay}");
        }
    }
}
