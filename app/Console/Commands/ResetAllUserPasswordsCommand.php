<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAllUserPasswordsCommand extends Command
{
    protected $signature = 'users:reset-passwords
                            {password : Plain-text password to set for all users}
                            {--env-guard=local,staging,testing : Allowed environments unless --force is provided}
                            {--force : Bypass environment guard}';

    protected $description = 'Reset all user account passwords to a single value (non-production use).';

    public function handle(): int
    {
        $targetPassword = (string) $this->argument('password');

        if (mb_strlen($targetPassword) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $allowedEnvironments = collect(explode(',', (string) $this->option('env-guard')))
            ->map(fn (string $env) => trim($env))
            ->filter()
            ->values();

        $currentEnvironment = app()->environment();
        $forced = (bool) $this->option('force');

        if (! $forced && ! $allowedEnvironments->contains($currentEnvironment)) {
            $this->error(sprintf(
                'Environment "%s" is not in allowed env guard [%s]. Use --force to override.',
                $currentEnvironment,
                $allowedEnvironments->implode(', ')
            ));

            return self::FAILURE;
        }

        $hashedPassword = Hash::make($targetPassword);
        $updated = User::query()->update(['password' => $hashedPassword]);

        $this->info(sprintf(
            'Updated %d user passwords in "%s" at %s.',
            $updated,
            $currentEnvironment,
            now()->toDateTimeString()
        ));

        return self::SUCCESS;
    }
}
