<?php

namespace App\Console\Commands;

use App\Jobs\SendWelcomeSeriesEmail;
use App\Models\Newsletter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWelcomeEmailSeriesCommand extends Command
{
    protected $signature = 'campaigns:send-welcome-series {--max=500}';

    protected $description = 'Queue welcome email series sends for subscribed newsletter users';

    public function handle(): int
    {
        $max = max(1, (int) $this->option('max'));

        $subscribers = Newsletter::query()
            ->where('status', 'subscribed')
            ->whereNotNull('email')
            ->orderBy('created_at')
            ->limit($max)
            ->get();

        $queued = 0;
        foreach ($subscribers as $subscriber) {
            $step = $this->determineStep($subscriber->created_at);
            if ($step === null) {
                continue;
            }

            $alreadySent = DB::table('newsletter_welcome_email_logs')
                ->where('email', $subscriber->email)
                ->where('series_step', $step)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            SendWelcomeSeriesEmail::dispatch($subscriber, $step);
            $queued++;
        }

        $this->info("Welcome email series complete. Queued: {$queued}");

        return self::SUCCESS;
    }

    /**
     * Roadmap 3.2: a queue-dispatch test exposed that Carbon 3 (this app
     * runs on Laravel 12) made diffInDays() signed rather than absolute by
     * default. For a subscriber created moments ago, the unsigned call used
     * to sometimes return a tiny negative float (e.g. -4.6e-7) depending on
     * which side of the two now() calls landed first, which fell through
     * every match arm to null and silently skipped that subscriber's welcome
     * email entirely -- a real bug, not a test artifact. Forcing absolute
     * and flooring to an int fixes it for both this run and production.
     */
    private function determineStep($createdAt): ?int
    {
        $days = (int) now()->diffInDays($createdAt, true);

        return match (true) {
            $days >= 7 => 3,
            $days >= 3 => 2,
            $days >= 0 => 1,
            default => null,
        };
    }
}
