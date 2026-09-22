<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';

    protected $description = 'Process subscription renewals and handle grace period expirations';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Processing subscription renewals...');

        // cancel_at_period_end must be excluded here: without it, a customer
        // who explicitly canceled "at period end" gets auto-renewed (and
        // re-billed via the SubscriptionPayment row below) the moment their
        // period lapses, instead of being caught by the grace-period
        // cancellation pass further down -- silently reversing their
        // cancellation.
        $toRenew = UserSubscription::where('status', 'active')
            ->where('cancel_at_period_end', false)
            ->where('current_period_end', '<=', now())
            ->whereDoesntHave('payments', function ($query) {
                $query->where('created_at', '>=', now()->subDays(1))
                    ->where('status', 'pending');
            })
            ->pluck('id');

        $renewed = 0;
        $failed = 0;

        foreach ($toRenew as $subscriptionId) {
            try {
                $renewedSubscription = DB::transaction(function () use ($subscriptionId, $subscriptionService) {
                    $subscription = UserSubscription::whereKey($subscriptionId)
                        ->lockForUpdate()
                        ->with(['user', 'plan'])
                        ->first();

                    if (! $subscription || $subscription->status !== 'active' || $subscription->current_period_end->gt(now())) {
                        return null;
                    }

                    $hasRecentPending = $subscription->payments()
                        ->where('created_at', '>=', now()->subDays(1))
                        ->where('status', 'pending')
                        ->exists();

                    if ($hasRecentPending) {
                        return null;
                    }

                    return $subscriptionService->renewSubscription($subscription) ? $subscription : null;
                });

                if (! $renewedSubscription) {
                    continue;
                }

                $renewed++;
                $this->line("Renewed subscription for user #{$renewedSubscription->user_id}");
            } catch (\Exception $e) {
                $failed++;
                $subscription = UserSubscription::find($subscriptionId);
                if ($subscription) {
                    $subscriptionService->markPastDue($subscription);
                }
                $this->error("Failed to renew subscription #{$subscriptionId}: {$e->getMessage()}");
            }
        }

        $this->info("\nProcessing grace period expirations...");

        $gracePeriodExpired = $subscriptionService->getExpiredGracePeriods();
        $canceled = 0;

        foreach ($gracePeriodExpired as $subscription) {
            try {
                $subscriptionService->cancelSubscription($subscription, true);
                $canceled++;
                $this->line("Canceled subscription for user #{$subscription->user_id} (grace period expired)");
            } catch (\Exception $e) {
                $this->error("Failed to cancel subscription #{$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->line("Renewed: $renewed");
        $this->line("Failed renewals (marked past due): $failed");
        $this->line("Canceled (grace period expired): $canceled");

        return Command::SUCCESS;
    }
}
