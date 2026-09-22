<?php

namespace Tests\Feature\Vendor;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use App\Services\Payment\StripePaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Vendor\SubscriptionController::processUpgrade() previously just flipped
 * subscription_plan_id and reset current_period_end locally, with no call
 * to any payment gateway at all -- any vendor could switch straight to the
 * most expensive plan for free by submitting the "Choose {plan}" form,
 * despite StripePaymentService::changePlan() already existing and doing the
 * real Stripe-side work with proration. These tests cover the fix: a paid
 * plan now requires either an existing Stripe-billed subscription (whose
 * Stripe side gets updated) or a trip through the real checkout flow.
 */
class SubscriptionUpgradeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_vendor_can_switch_to_a_free_plan_without_any_payment_gate(): void
    {
        [$user, $subscription] = $this->makeVendorWithSubscription();
        $freePlan = $this->makePlan(['price' => 0]);

        $response = $this->actingAs($user)->post(route('vendor.subscription.process'), [
            'plan_id' => $freePlan->id,
        ]);

        $response->assertRedirect(route('vendor.subscription.index'));
        $subscription->refresh();
        $this->assertSame($freePlan->id, $subscription->subscription_plan_id);
    }

    public function test_switching_to_a_paid_plan_with_no_payment_provider_on_file_is_redirected_to_checkout(): void
    {
        [$user, $subscription] = $this->makeVendorWithSubscription();
        $originalPlanId = $subscription->subscription_plan_id;
        $paidPlan = $this->makePlan(['price' => 79.00]);

        $response = $this->actingAs($user)->post(route('vendor.subscription.process'), [
            'plan_id' => $paidPlan->id,
        ]);

        $response->assertRedirect(route('subscriptions.checkout', $paidPlan));
        $subscription->refresh();
        $this->assertSame(
            $originalPlanId,
            $subscription->subscription_plan_id,
            'The local plan must not change until payment is actually collected.'
        );
    }

    public function test_switching_to_a_paid_plan_when_stripe_is_unconfigured_fails_without_granting_the_plan(): void
    {
        [$user, $subscription] = $this->makeVendorWithSubscription([
            'payment_provider' => 'stripe',
            'payment_provider_subscription_id' => 'sub_fake_'.uniqid(),
        ]);
        $originalPlanId = $subscription->subscription_plan_id;
        $paidPlan = $this->makePlan(['price' => 129.00]);

        // Stripe has no API keys configured in the test environment, so this
        // exercises the real changePlan() -> ensureConfigured() failure path,
        // not a mock.
        $response = $this->actingAs($user)->post(route('vendor.subscription.process'), [
            'plan_id' => $paidPlan->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $subscription->refresh();
        $this->assertSame($originalPlanId, $subscription->subscription_plan_id);
    }

    public function test_switching_to_a_paid_plan_updates_the_local_record_once_stripe_confirms_the_change(): void
    {
        [$user, $subscription] = $this->makeVendorWithSubscription([
            'payment_provider' => 'stripe',
            'payment_provider_subscription_id' => 'sub_fake_'.uniqid(),
        ]);
        $paidPlan = $this->makePlan(['price' => 199.00]);

        $this->mock(StripePaymentService::class, function ($mock) use ($subscription, $paidPlan) {
            $mock->shouldReceive('changePlan')
                ->once()
                ->with($subscription->payment_provider_subscription_id, \Mockery::on(
                    fn ($plan) => $plan->id === $paidPlan->id
                ))
                ->andReturn(true);
        });

        $response = $this->actingAs($user)->post(route('vendor.subscription.process'), [
            'plan_id' => $paidPlan->id,
        ]);

        $response->assertRedirect(route('vendor.subscription.index'));
        $subscription->refresh();
        $this->assertSame($paidPlan->id, $subscription->subscription_plan_id);
    }

    private function makeVendorWithSubscription(array $subscriptionAttrs = []): array
    {
        $user = User::factory()->vendor()->create();

        Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $plan = $this->makePlan(['price' => 0]);

        $subscription = UserSubscription::create(array_merge([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ], $subscriptionAttrs));

        return [$user->fresh(), $subscription];
    }

    private function makePlan(array $attrs = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Plan '.uniqid(),
            'slug' => 'plan-'.uniqid(),
            'price' => 0,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ], $attrs));
    }
}
