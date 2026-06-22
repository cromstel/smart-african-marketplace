<?php

namespace App\Services\Subscription;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Log;

class EffectiveSubscriptionResolver
{
    public function resolveFor(User $user): ?UserSubscription
    {
        $subscription = $user->subscription;

        if ($subscription && ($subscription->isActive() || $subscription->onGracePeriod())) {
            return $subscription;
        }

        if (! $user->isVendor()) {
            return $subscription;
        }

        $legacySubscription = $user->vendor?->subscription;

        if (! $legacySubscription || ! $legacySubscription->isActive()) {
            return $subscription;
        }

        $plan = $this->resolvePlanFromLegacy($legacySubscription->plan_name);

        if (! $plan) {
            return $subscription;
        }

        $status = $legacySubscription->status === 'trialing' ? 'trialing' : 'active';

        $bridged = UserSubscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_plan_id' => $plan->id,
                'status' => $status,
                'trial_ends_at' => $legacySubscription->trial_ends_at,
                'current_period_start' => $legacySubscription->current_period_start,
                'current_period_end' => $legacySubscription->current_period_end,
                'cancel_at_period_end' => (bool) $legacySubscription->cancel_at_period_end,
                'canceled_at' => $legacySubscription->canceled_at,
            ]
        );

        Log::info('Bridged vendor subscription to user subscription', [
            'user_id' => $user->id,
            'legacy_vendor_subscription_id' => $legacySubscription->id,
            'subscription_plan_id' => $plan->id,
            'status' => $status,
        ]);

        return $bridged;
    }

    private function resolvePlanFromLegacy(?string $planName): ?SubscriptionPlan
    {
        $normalized = strtolower(trim((string) $planName));

        $slugMap = [
            'trial' => 'free-trial',
            'free trial' => 'free-trial',
            'starter' => 'starter',
            'professional' => 'professional',
            'business' => 'business',
            'enterprise' => 'enterprise',
        ];

        $slug = $slugMap[$normalized] ?? null;

        if ($slug) {
            return SubscriptionPlan::where('slug', $slug)->first();
        }

        return SubscriptionPlan::whereRaw('LOWER(name) = ?', [$normalized])->first();
    }
}
