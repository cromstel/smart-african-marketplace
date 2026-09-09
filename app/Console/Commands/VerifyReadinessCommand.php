<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class VerifyReadinessCommand extends Command
{
    protected $signature = 'app:verify-readiness {--strict : Fail on warnings (production gate mode)}';

    protected $description = 'Run deployment readiness checks (env, db, routes, health, queue, migrations)';

    public function handle(): int
    {
        $strict = (bool) $this->option('strict');
        $results = [];

        $appEnv = (string) config('app.env');
        $appDebug = (bool) config('app.debug');
        $results[] = $this->result(
            'Environment Safety',
            $appEnv === 'production' && $appDebug === false ? 'pass' : 'warn',
            "APP_ENV={$appEnv}, APP_DEBUG=".($appDebug ? 'true' : 'false')
        );

        try {
            DB::select('SELECT 1');
            $results[] = $this->result('Database Connectivity', 'pass', 'Connection OK');
        } catch (\Throwable $e) {
            $results[] = $this->result('Database Connectivity', 'fail', $e->getMessage());
        }

        try {
            /** @var Migrator $migrator */
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            $pending = collect($files)
                ->map(fn (string $filePath) => $migrator->getMigrationName($filePath))
                ->reject(fn (string $name) => in_array($name, $ran, true))
                ->count();
            $results[] = $this->result(
                'Migration State',
                $pending === 0 ? 'pass' : 'fail',
                $pending === 0 ? 'No pending migrations' : "{$pending} pending migration(s)"
            );
        } catch (\Throwable $e) {
            $results[] = $this->result('Migration State', 'fail', $e->getMessage());
        }

        $queueConnection = (string) config('queue.default');
        $results[] = $this->result(
            'Queue Driver',
            $queueConnection === 'sync' ? 'warn' : 'pass',
            "QUEUE_CONNECTION={$queueConnection}"
        );

        $kernelSource = @file_get_contents(app_path('Console/Kernel.php')) ?: '';
        $schedulerConfigured = str_contains($kernelSource, '$schedule->command(');
        $results[] = $this->result(
            'Scheduler Events',
            $schedulerConfigured ? 'pass' : 'warn',
            $schedulerConfigured ? 'Scheduled events registered' : 'No scheduled events found'
        );

        $requiredRoutes = ['webhooks.stripe', 'webhooks.paystack', 'healthz'];
        $missing = collect($requiredRoutes)->filter(fn ($name) => ! Route::has($name))->values()->all();
        $results[] = $this->result(
            'Critical Routes',
            empty($missing) ? 'pass' : 'fail',
            empty($missing) ? 'All critical routes exist' : 'Missing: '.implode(', ', $missing)
        );

        try {
            /** @var HttpKernel $kernel */
            $kernel = app(HttpKernel::class);
            $request = Request::create('/healthz', 'GET');
            $response = $kernel->handle($request);
            $kernel->terminate($request, $response);

            $results[] = $this->result(
                'Health Endpoint',
                $response->getStatusCode() === 200 ? 'pass' : 'fail',
                "HTTP {$response->getStatusCode()}"
            );
        } catch (\Throwable $e) {
            $results[] = $this->result('Health Endpoint', 'fail', $e->getMessage());
        }

        $secretChecks = [
            'STRIPE_KEY',
            'STRIPE_SECRET',
            'PAYSTACK_PUBLIC_KEY',
            'PAYSTACK_SECRET_KEY',
            'MAIL_HOST',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
        ];

        $badSecrets = [];
        foreach ($secretChecks as $key) {
            $value = trim((string) env($key, ''));
            if ($value === '' || str_contains($value, '...') || str_contains(strtolower($value), 'your-')) {
                $badSecrets[] = $key;
            }
        }
        $results[] = $this->result(
            'Secrets Sanity',
            empty($badSecrets) ? 'pass' : 'warn',
            empty($badSecrets) ? 'Critical env keys look populated' : 'Missing/placeholder: '.implode(', ', $badSecrets)
        );

        // The mobile money callback endpoint marks orders paid. It refuses every
        // request when this is unset (fails closed), so an installation that
        // accepts MTN or M-Pesa will silently stop confirming those payments.
        // Surfaced as its own check rather than folded into the secrets sanity
        // row, because the consequence is specific and easy to miss.
        $momoSecret = trim((string) config('payment.mobile_money.webhook_secret', ''));
        $results[] = $this->result(
            'Mobile Money Callback Secret',
            $momoSecret === '' ? 'warn' : 'pass',
            $momoSecret === ''
                ? 'MOMO_WEBHOOK_SECRET is not set — /webhooks/momo/* will refuse all callbacks (503). Required if you accept MTN or M-Pesa.'
                : 'Configured; mobile money callbacks can be authenticated'
        );

        $this->table(
            ['Check', 'Status', 'Detail'],
            array_map(fn ($row) => [$row['check'], strtoupper($row['status']), $row['detail']], $results)
        );

        $hasFail = collect($results)->contains(fn ($row) => $row['status'] === 'fail');
        $hasWarn = collect($results)->contains(fn ($row) => $row['status'] === 'warn');

        if ($hasFail || ($strict && $hasWarn)) {
            $this->error($strict
                ? 'Readiness failed in strict mode (warnings treated as blocking).'
                : 'Readiness failed due to one or more blocking checks.');

            return self::FAILURE;
        }

        $this->info('Readiness checks passed.');

        return self::SUCCESS;
    }

    private function result(string $check, string $status, string $detail): array
    {
        return compact('check', 'status', 'detail');
    }
}
