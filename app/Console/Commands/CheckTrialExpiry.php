<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class CheckTrialExpiry extends Command
{
    protected $signature = 'subscriptions:check-trial-expiry';

    protected $description = 'Check for expired trials and convert them to paid subscriptions or expire them';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Checking for expired trials...');

        $expiredTrials = $subscriptionService->getExpiredTrials();

        if ($expiredTrials->isEmpty()) {
            $this->info('No expired trials found.');

            return Command::SUCCESS;
        }

        $converted = 0;
        $expired = 0;

        foreach ($expiredTrials as $subscription) {
            try {
                if ($subscription->plan->price > 0) {
                    $subscriptionService->activateSubscription($subscription);
                    $converted++;
                    $this->line("✓ Converted trial to paid subscription for user #{$subscription->user_id}");
                } else {
                    $subscriptionService->expireSubscription($subscription);
                    $expired++;
                    $this->line("✓ Expired free trial for user #{$subscription->user_id}");
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to process subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->line("Converted to paid: $converted");
        $this->line("Expired: $expired");

        return Command::SUCCESS;
    }
}
