<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Notifications\SubscriptionExpiringNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Vendor\SubscriptionController::processUpgrade() keeps a legacy
 * VendorSubscription row in sync (same current_period_end) with the native
 * UserSubscription on every upgrade, so that EffectiveSubscriptionResolver's
 * bridge stays populated for vendors who predate it. subscriptions:check-
 * expiring queries both tables independently with no de-duplication, so any
 * vendor with both records -- anyone who has ever upgraded -- got the same
 * "expiring soon" notification twice.
 */
class CheckExpiringSubscriptionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_vendor_with_only_a_legacy_subscription_is_still_notified(): void
    {
        Notification::fake();

        $vendor = $this->makeVendor();
        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_name' => 'Starter',
            'status' => 'active',
            'current_period_end' => now()->addDays(3),
            'notification_sent' => false,
        ]);

        $this->artisan('subscriptions:check-expiring')->assertSuccessful();

        Notification::assertSentTo($vendor->user, SubscriptionExpiringNotification::class);
    }

    public function test_a_vendor_with_both_a_native_and_synced_legacy_subscription_is_notified_only_once(): void
    {
        Notification::fake();

        $vendor = $this->makeVendor();
        $plan = SubscriptionPlan::create(['name' => 'Starter '.uniqid(), 'slug' => 'starter-'.uniqid()]);

        UserSubscription::create([
            'user_id' => $vendor->user_id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addDays(3),
        ]);

        // The exact pattern processUpgrade() writes: both rows, same expiry.
        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_name' => $plan->name,
            'status' => 'active',
            'current_period_end' => now()->addDays(3),
            'notification_sent' => false,
        ]);

        $this->artisan('subscriptions:check-expiring')->assertSuccessful();

        Notification::assertSentToTimes($vendor->user, SubscriptionExpiringNotification::class, 1);
    }

    private function makeVendor(): Vendor
    {
        $user = User::factory()->vendor()->create();

        return Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }
}
