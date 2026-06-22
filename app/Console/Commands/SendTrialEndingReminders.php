<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionTrialEnding;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTrialEndingReminders extends Command
{
    protected $signature = 'subscriptions:send-trial-reminders {--days=7 : Days before trial ends}';

    protected $description = 'Send email reminders to users whose trials are ending soon';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $days = (int) $this->option('days');
        $this->info("Checking for trials ending in {$days} days...");

        $endingTrials = $subscriptionService->getTrialEndingSubscriptions($days);

        if ($endingTrials->isEmpty()) {
            $this->info('No trials ending soon.');

            return Command::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($endingTrials as $subscription) {
            try {
                if ($subscription->daysUntilTrialEnds() <= $days) {
                    Mail::to($subscription->user->email)->send(new SubscriptionTrialEnding($subscription));
                    $sent++;
                    $this->line("✓ Sent reminder to {$subscription->user->email}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Failed to send email to {$subscription->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->line("Emails sent: $sent");
        $this->line("Failed: $failed");

        return Command::SUCCESS;
    }
}
