<?php

namespace Tests\Feature;

use App\Mail\SubscriptionTrialEnding;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    // Regression: subscriptions:check-trial-expiry, subscriptions:process-renewals
    // and subscriptions:send-trial-reminders existed as commands but were never
    // scheduled anywhere -- not in routes/console.php, and not in the pre-3.3
    // Kernel.php it replaced. Without process-renewals in particular, no active
    // subscription's current_period_end ever advanced and no grace-period
    // cancellation ever ran, silently and indefinitely, because
    // UserSubscription::isActive() only checks `status`, never `current_period_end`.
    public function test_the_subscription_lifecycle_commands_are_actually_scheduled(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event) => $event->command)
            ->filter(fn ($command) => $command !== null);

        foreach ([
            'subscriptions:check-trial-expiry',
            'subscriptions:process-renewals',
            'subscriptions:send-trial-reminders',
        ] as $signature) {
            $this->assertTrue(
                $commands->contains(fn ($command) => str_contains($command, $signature)),
                "Expected \"{$signature}\" to be registered on the schedule."
            );
        }
    }

    public function test_check_trial_expiry_converts_an_expired_trial_on_a_paid_plan_to_active(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan(['price' => 29.00, 'billing_cycle' => 'monthly']);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
            'current_period_start' => now()->subDays(8),
            'current_period_end' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:check-trial-expiry')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->current_period_end->isFuture());
    }

    public function test_check_trial_expiry_expires_a_lapsed_trial_on_a_free_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan(['price' => 0]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
            'current_period_end' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:check-trial-expiry')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame('expired', $subscription->status);
    }

    public function test_process_renewals_advances_the_period_and_records_a_payment_for_a_due_subscription(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan(['price' => 29.00, 'billing_cycle' => 'monthly']);

        $originalPeriodEnd = now()->subDay();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now()->subMonth(),
            'current_period_end' => $originalPeriodEnd,
        ]);

        $this->artisan('subscriptions:process-renewals')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue(
            $subscription->current_period_end->gt($originalPeriodEnd),
            'current_period_end must advance past the original due date.'
        );
        $this->assertDatabaseHas('subscription_payments', [
            'user_subscription_id' => $subscription->id,
            'status' => 'pending',
        ]);
    }

    public function test_process_renewals_cancels_a_subscription_whose_grace_period_has_expired(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'cancel_at_period_end' => true,
            'current_period_start' => now()->subMonth(),
            'current_period_end' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:process-renewals')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame('canceled', $subscription->status);
    }

    public function test_process_renewals_does_not_touch_a_subscription_still_within_its_period(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();

        $futureEnd = now()->addDays(10);
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now()->subDays(20),
            'current_period_end' => $futureEnd,
        ]);

        $this->artisan('subscriptions:process-renewals')->assertSuccessful();

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        // current_period_end is a `timestamp` column (second precision), so
        // compare at second resolution rather than against the in-memory
        // Carbon instance's microseconds.
        $this->assertLessThan(1, $subscription->current_period_end->diffInSeconds($futureEnd));
        $this->assertDatabaseMissing('subscription_payments', [
            'user_subscription_id' => $subscription->id,
        ]);
    }

    public function test_send_trial_reminders_emails_a_user_whose_trial_ends_soon(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $plan = $this->makePlan();

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(3),
            'current_period_end' => now()->addDays(3),
        ]);

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        Mail::assertSent(SubscriptionTrialEnding::class);
    }

    private function makePlan(array $attrs = []): SubscriptionPlan
    {
        return SubscriptionPlan::create(array_merge([
            'name' => 'Plan '.uniqid(),
            'slug' => 'plan-'.uniqid(),
            'price' => 0,
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
        ], $attrs));
    }
}
