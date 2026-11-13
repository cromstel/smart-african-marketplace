# Queue Workers and Background Jobs

This runbook standardizes production queue worker setup for Laravel background processing.

## Required Runtime Commands

- Queue worker:
  - `php artisan queue:work --queue=payouts,campaigns,media,chatbot,default --sleep=3 --tries=3 --max-time=3600`
- Scheduler (every minute from cron/system timer):
  - `php artisan schedule:run`

### Named queues (Roadmap 3.2)

Jobs in `app/Jobs/` dispatch onto named queues rather than the implicit
`default` queue, listed above in worker priority order (`--queue` processes
queues left-to-right, only moving to the next when the current one is
empty):

| Queue | Jobs |
| --- | --- |
| `payouts` | `ProcessVendorPayout` |
| `campaigns` | `SendAbandonedCartRecoveryEmail`, `SendWelcomeSeriesEmail`, `SendWinBackCampaignEmail` |
| `media` | `OptimizeMediaLibraryItem` |
| `chatbot` | `ReindexChatbotKnowledgeJob` |

**A worker started without `--queue=...` only processes the `default`
queue and will never pick up any of the jobs above** -- if a deploy target
still runs the old `queue:work --sleep=3 --tries=3 --max-time=3600` command
from before this change, jobs will accumulate in the `jobs` table and
silently never run. Confirm the running command matches the one above after
any deploy.

## Supervisor Example (Linux)

```ini
[program:african-market-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/african-market/artisan queue:work --queue=payouts,campaigns,media,chatbot,default --sleep=3 --tries=3 --max-time=3600
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
