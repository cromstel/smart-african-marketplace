<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Models\VendorSubscription;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Console\Command;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring';

    protected $description = 'Check for expiring subscriptions and send notifications to vendors';

    public function handle()
    {
        $this->info('Checking for expiring subscriptions...');

        $expiringUserSubscriptions = UserSubscription::with(['user', 'plan'])
            ->whereIn('status', ['trialing', 'active'])
            ->get()
            ->filter(function (UserSubscription $subscription) {
                if (! $subscription->user || ! $subscription->user->isVendor()) {
                    return false;
                }

                $endDate = $subscription->trial_ends_at ?? $subscription->current_period_end;

                if (! $endDate || ! $subscription->isActive()) {
                    return false;
                }

                $daysLeft = now()->diffInDays($endDate, false);

                return $daysLeft <= 7 && $daysLeft >= 0;
            });

        $legacyExpiringSubscriptions = VendorSubscription::where('status', 'trialing')
            ->orWhere('status', 'active')
            ->get()
            ->filter(function ($subscription) {
                return $subscription->shouldNotifyExpiring();
            });

        if ($expiringUserSubscriptions->isEmpty() && $legacyExpiringSubscriptions->isEmpty()) {
            $this->info('No subscriptions require notifications.');

            return 0;
        }

        $count = 0;

        foreach ($expiringUserSubscriptions as $subscription) {
            $user = $subscription->user;
            $endDate = $subscription->trial_ends_at ?? $subscription->current_period_end;
            $daysLeft = max(0, now()->diffInDays($endDate, false));

            if ($user) {
                $user->notify(new SubscriptionExpiringNotification(
                    $subscription->plan?->name ?? 'Subscription',
                    $daysLeft,
                    $subscription->id
                ));

                $count++;
                $this->info("Notified vendor: {$user->email} ({$daysLeft} days left)");
            }
        }

        foreach ($legacyExpiringSubscriptions as $subscription) {
            $vendor = $subscription->vendor;
            $daysLeft = $subscription->daysUntilExpiry();

            if ($vendor && $vendor->user) {
                $vendor->user->notify(new SubscriptionExpiringNotification(
                    $subscription->plan_name ?? 'Subscription',
                    $daysLeft,
                    $subscription->id
                ));

                $subscription->markNotificationSent();
                $count++;

                $this->info("Notified legacy vendor: {$vendor->business_name} ({$daysLeft} days left)");
            }
        }

        $this->info("Successfully sent {$count} notifications.");

        return 0;
    }
}
