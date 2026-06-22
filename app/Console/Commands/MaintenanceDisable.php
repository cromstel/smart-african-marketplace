<?php

namespace App\Console\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

class MaintenanceDisable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable maintenance mode for the application';

    /**
     * Execute the console command.
     */
    public function handle(MaintenanceService $maintenanceService): int
    {
        // Disable maintenance mode
        $maintenanceService->disable();

        $this->info('Maintenance mode has been disabled.');

        if ($this->confirm('Do you want to disable Laravel\'s built-in maintenance mode as well?')) {
            $this->call('up');
        }

        return Command::SUCCESS;
    }
}
