<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('market:setup', function () {
    $this->info('Setting up '.config('app.name').'...');
    $this->call('migrate');
    $this->call('db:seed');
    $this->info(config('app.name').' setup complete!');
})->purpose('Set up '.config('app.name').' database');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Roadmap 3.3: moved from app/Console/Kernel.php's schedule() method, which
| has no equivalent on the slim skeleton -- this file is loaded on every
| Artisan boot, and Schedule::command() calls just register tasks against
| the Schedule singleton for `schedule:run` to read later, so this works
| identically to the old Kernel-based definition. Timing is unchanged.
|
*/

Schedule::command('inventory:check-low-stock')->dailyAt('09:00');
Schedule::command('sitemap:generate')->daily();
Schedule::command('subscriptions:check-expiring')->dailyAt('09:00');
Schedule::command('chatbot:reindex-knowledge')->dailyAt('02:00');
Schedule::command('booking:send-reminders')->hourly();
Schedule::command('media:optimize-pending --limit=200')->dailyAt('01:00');
Schedule::command('loyalty:expire-points')->dailyAt('03:00');
Schedule::command('campaigns:send-abandoned-cart --hours=2 --max=500')->hourly();
Schedule::command('campaigns:send-welcome-series --max=500')->dailyAt('10:00');
Schedule::command('campaigns:send-winback --days=60 --max=500')->weeklyOn(1, '11:00');
Schedule::command('chatbot:purge-old')->dailyAt('02:30');
Schedule::command('auction:process')->everyFiveMinutes();
