<?php

namespace Tests\Feature\Vendor\Concerns;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;

/**
 * Shared fixture builders for vendor cross-tenant isolation tests.
 *
 * A vendor's `vendors.id` is deliberately never made to equal its owning
 * user's `users.id` here (uniqid()-based emails/slugs mean Eloquent
 * auto-increments diverge in the normal case anyway, but see
 * WithdrawalTest for why this distinction matters: any controller that
 * compares `auth()->id()` against a `vendor_id` column is only caught by a
 * test when the two ids genuinely differ).
 */
trait MakesIsolatedVendors
{
    /**
     * @return array{0: User, 1: Vendor}
     */
    private function makeVendor(array $featureKeys = []): array
    {
        $user = User::factory()->vendor()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->entitle($user, $featureKeys);

        return [$user->fresh(), $vendor];
    }

    /**
     * Gives the vendor an active plan including the given module feature
     * keys, so requests reach the controller/policy instead of being
     * redirected by the `module.access` gate.
     */
    private function entitle(User $user, array $featureKeys = []): void
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Isolation Test Plan '.uniqid(),
            'slug' => 'isolation-test-plan-'.uniqid(),
            'feature_keys' => array_values(array_unique(array_merge([
                'multiple_product_category',
                'vendor_dashboard_announcements',
            ], $featureKeys))),
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }
}
