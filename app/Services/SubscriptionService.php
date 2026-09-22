<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function createSubscription(User $user, SubscriptionPlan $plan, ?string $paymentProvider = null, ?string $providerSubscriptionId = null): UserSubscription
    {
        DB::beginTransaction();

        try {
            $existingSubscription = $user->subscription;
            if ($existingSubscription && $existingSubscription->isActive()) {
                throw new \Exception('User already has an active subscription');
            }

            $trialEndsAt = now()->addDays($plan->trial_days);

            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => 'trialing',
                'trial_ends_at' => $trialEndsAt,
                'current_period_start' => now(),
                'current_period_end' => $trialEndsAt,
                'payment_provider' => $paymentProvider,
                'payment_provider_subscription_id' => $providerSubscriptionId,
            ]);

            DB::commit();

            Log::info("Subscription created for user {$user->id} on plan {$plan->name}");

            return $subscription;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create subscription: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Give a vendor a working subscription for free as soon as they're
     * approved, instead of leaving them with none. Without this, an
     * approved vendor had no UserSubscription at all: every module-gated
     * vendor route (order management, product categories, announcements,
     * ...) redirected to "Upgrade Subscription", and that page itself
     * dead-ended -- Vendor\SubscriptionController::processUpgrade() only
     * updates an *existing* subscription, it can't create the first one.
     *
     * Deliberately assigned as 'active' rather than 'trialing': a trialing
     * subscription on a $0 plan gets auto-expired by
     * subscriptions:check-trial-expiry after its trial period, which would
     * silently lock the vendor back out. 'active' has no such cliff here,
     * so the free plan holds until the vendor actually upgrades via
     * Vendor\SubscriptionController::processUpgrade().
     */
    public function assignFreePlan(User $user): ?UserSubscription
    {
        $existing = $user->subscription;

        if ($existing && $existing->isActive()) {
            return $existing;
        }

        $freePlan = SubscriptionPlan::active()
            ->where('price', 0)
            ->orderBy('sort_order')
            ->first();

        if (! $freePlan) {
            Log::warning("No free subscription plan configured; approved vendor (user #{$user->id}) left without a subscription.");

            return null;
        }

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $freePlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        Log::info("Assigned free plan '{$freePlan->name}' to user {$user->id} on vendor approval");

        return $subscription;
    }

    public function activateSubscription(UserSubscription $subscription): bool
    {
        if ($subscription->status !== 'trialing') {
            return false;
        }

        $plan = $subscription->plan;
        $periodStart = now();
        $periodEnd = $this->calculatePeriodEnd($periodStart, $plan->billing_cycle);

        $subscription->update([
            'status' => 'active',
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
        ]);

        Log::info("Subscription {$subscription->id} activated");

        return true;
    }

    public function renewSubscription(UserSubscription $subscription): bool
    {
        DB::beginTransaction();

        try {
            $plan = $subscription->plan;
            $newPeriodStart = $subscription->current_period_end;
            $newPeriodEnd = $this->calculatePeriodEnd($newPeriodStart, $plan->billing_cycle);

            $subscription->update([
                'status' => 'active',
                'current_period_start' => $newPeriodStart,
                'current_period_end' => $newPeriodEnd,
            ]);

            SubscriptionPayment::create([
                'user_subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'status' => 'pending',
                'payment_provider' => $subscription->payment_provider,
            ]);

            DB::commit();

            Log::info("Subscription {$subscription->id} renewed");

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to renew subscription: {$e->getMessage()}");

            return false;
        }
    }

    public function cancelSubscription(UserSubscription $subscription, bool $immediately = false): bool
    {
        if ($immediately) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'cancel_at_period_end' => false,
            ]);

            Log::info("Subscription {$subscription->id} canceled immediately");
        } else {
            $subscription->update([
                'cancel_at_period_end' => true,
            ]);

            Log::info("Subscription {$subscription->id} scheduled to cancel at period end");
        }

        return true;
    }

    public function resumeSubscription(UserSubscription $subscription): bool
    {
        if (! $subscription->onGracePeriod()) {
            return false;
        }

        $subscription->update([
            'cancel_at_period_end' => false,
        ]);

        Log::info("Subscription {$subscription->id} resumed");

        return true;
    }

    public function expireSubscription(UserSubscription $subscription): bool
    {
        $subscription->update([
            'status' => 'expired',
        ]);

        Log::info("Subscription {$subscription->id} expired");

        return true;
    }

    public function markPastDue(UserSubscription $subscription): bool
    {
        $subscription->update([
            'status' => 'past_due',
        ]);

        Log::info("Subscription {$subscription->id} marked as past_due");

        return true;
    }

    public function recordPayment(UserSubscription $subscription, float $amount, string $status, string $provider, ?string $providerPaymentId = null): SubscriptionPayment
    {
        $attributes = [
            'user_subscription_id' => $subscription->id,
            'amount' => $amount,
            'currency' => $subscription->plan->currency,
            'status' => $status,
            'payment_provider' => $provider,
            'payment_provider_payment_id' => $providerPaymentId,
            'paid_at' => $status === 'paid' ? now() : null,
        ];

        if ($providerPaymentId === null) {
            $payment = SubscriptionPayment::create($attributes);
        } else {
            $payment = SubscriptionPayment::firstOrCreate(
                [
                    'payment_provider' => $provider,
                    'payment_provider_payment_id' => $providerPaymentId,
                ],
                $attributes
            );
        }

        if (! $payment->wasRecentlyCreated) {
            return $payment;
        }

        if ($status === 'paid' && $subscription->status === 'past_due') {
            $subscription->update(['status' => 'active']);
        }

        return $payment;
    }

    public function getTrialEndingSubscriptions(int $days = 7)
    {
        return UserSubscription::expiringSoon($days)->with(['user', 'plan'])->get();
    }

    public function getExpiredTrials()
    {
        return UserSubscription::where('status', 'trialing')
            ->where('trial_ends_at', '<=', now())
            ->with(['user', 'plan'])
            ->get();
    }

    public function getExpiredGracePeriods()
    {
        return UserSubscription::where('cancel_at_period_end', true)
            ->where('current_period_end', '<=', now())
            ->with(['user', 'plan'])
            ->get();
    }

    protected function calculatePeriodEnd($start, string $billingCycle)
    {
        return match ($billingCycle) {
            'monthly' => $start->copy()->addMonth(),
            'yearly' => $start->copy()->addYear(),
            'lifetime' => $start->copy()->addYears(100),
            default => $start->copy()->addMonth(),
        };
    }
}
