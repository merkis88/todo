<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendReminders;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('tasks:send-reminders')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
