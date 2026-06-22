<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inventory:check-low-stock')->dailyAt('09:00');
        $schedule->command('sitemap:generate')->daily();
        $schedule->command('subscriptions:check-expiring')->dailyAt('09:00');
        $schedule->command('chatbot:reindex-knowledge')->dailyAt('02:00');
        $schedule->command('booking:send-reminders')->hourly();
        $schedule->command('media:optimize-pending --limit=200')->dailyAt('01:00');
        $schedule->command('loyalty:expire-points')->dailyAt('03:00');
        $schedule->command('campaigns:send-abandoned-cart --hours=2 --max=500')->hourly();
        $schedule->command('campaigns:send-welcome-series --max=500')->dailyAt('10:00');
        $schedule->command('campaigns:send-winback --days=60 --max=500')->weeklyOn(1, '11:00');
        $schedule->command('chatbot:purge-old')->dailyAt('02:30');
        $schedule->command('auction:process')->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
