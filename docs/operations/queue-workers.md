# Queue Workers and Background Jobs

This runbook standardizes production queue worker setup for Laravel background processing.

## Required Runtime Commands

- Queue worker:
  - `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`
- Scheduler (every minute from cron/system timer):
  - `php artisan schedule:run`

## Supervisor Example (Linux)

```ini
[program:african-market-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/african-market/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/african-market-queue.log
```

## Post-Deploy Validation

Run these checks after each deploy:

1. `php artisan queue:failed` returns no new failed jobs.
2. Worker process list shows expected worker count (`supervisorctl status` or equivalent).
3. `php artisan app:verify-readiness --strict` passes when production secrets are set.

## Common Failure Signals

- Rising `failed_jobs` table count.
- Jobs stuck in `jobs` table without processing.
- Timeout/retry loops in worker logs.

## Remediation

- Restart workers after deploy: `php artisan queue:restart`
- Re-run failed jobs if safe: `php artisan queue:retry all`
- Clear failed queue entries only after triage: `php artisan queue:flush`
