<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
    $this->info('Setting up Smart African Marketplace...');
    $this->call('migrate');
    $this->call('db:seed');
    $this->info('Smart African Marketplace setup complete!');
})->purpose('Set up Smart African Marketplace database');
