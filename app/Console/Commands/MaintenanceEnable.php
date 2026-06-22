<?php

namespace App\Console\Commands;

use App\Services\MaintenanceService;
use Illuminate\Console\Command;

class MaintenanceEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:enable 
                            {message : The maintenance message to display}
                            {--ips= : Comma-separated list of IP addresses to allow access}
                            {--emails= : Comma-separated list of email addresses to allow access}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable maintenance mode for the application';

    /**
     * Execute the console command.
     */
    public function handle(MaintenanceService $maintenanceService): int
    {
        $message = $this->argument('message');

        // Set custom message
        config(['maintenance.message' => $message]);

        // Set allowed IPs if provided
        if ($this->option('ips')) {
            $ips = array_filter(array_map('trim', explode(',', $this->option('ips'))));
            config(['maintenance.allowed_ips' => $ips]);
            $this->info('Allowed IPs: '.implode(', ', $ips));
        }

        // Set allowed emails if provided
        if ($this->option('emails')) {
            $emails = array_filter(array_map('trim', explode(',', $this->option('emails'))));
            config(['maintenance.allowed_emails' => $emails]);
            $this->info('Allowed emails: '.implode(', ', $emails));
        }

        // Enable maintenance mode
        $maintenanceService->enable();

        $this->info('Maintenance mode has been enabled.');
        $this->line('Message: '.$message);

        if ($this->confirm('Do you want to enable Laravel\'s built-in maintenance mode as well?')) {
            $this->call('down', [
                '--message' => $message,
                '--retry' => config('maintenance.retry', 3600),
            ]);
        }

        return Command::SUCCESS;
    }
}
