<?php

namespace Tests\Feature;

use App\Mail\SubscriptionCreated;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_user_can_subscribe_to_a_plan_and_starts_a_trial(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $plan = $this->makePlan();

        $response = $this->actingAs($user)->post(route('subscriptions.subscribe'), [
            'plan_id' => $plan->id,
        ]);

        $response->assertRedirect(route('subscriptions.success'));
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trialing',
        ]);
        Mail::assertSent(SubscriptionCreated::class);
    }

    public function test_a_user_cannot_subscribe_twice_while_already_active(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $planA = $this->makePlan();
        $planB = $this->makePlan();

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $planA->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('subscriptions.subscribe'), [
            'plan_id' => $planB->id,
        ]);

        $response->assertRedirect(route('subscriptions.my'));
        $response->assertSessionHas('error');
        $this->assertSame(1, UserSubscription::where('user_id', $user->id)->count());
        Mail::assertNothingSent();
    }

    public function test_a_user_can_schedule_cancellation_at_period_end(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)->post(route('subscriptions.cancel'));

        $response->assertRedirect(route('subscriptions.my'));
        $subscription->refresh();
        $this->assertTrue((bool) $subscription->cancel_at_period_end);
        $this->assertSame('active', $subscription->status, 'A scheduled cancellation must not immediately cut off access.');
    }

    public function test_a_user_can_cancel_immediately(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.cancel'), ['immediately' => true])
            ->assertRedirect(route('subscriptions.my'));

        $subscription->refresh();
        $this->assertSame('canceled', $subscription->status);
    }

    public function test_a_user_can_resume_a_subscription_on_its_grace_period(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();
        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'cancel_at_period_end' => true,
            'current_period_end' => now()->addDays(5),
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.resume'))
            ->assertRedirect(route('subscriptions.my'));

        $subscription->refresh();
        $this->assertFalse((bool) $subscription->cancel_at_period_end);
    }

    public function test_resuming_a_subscription_not_on_its_grace_period_fails_gracefully(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();
        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'cancel_at_period_end' => false,
        ]);

        $this->actingAs($user)
            ->post(route('subscriptions.resume'))
            ->assertSessionHas('error');
    }

    public function test_subscribing_requires_authentication(): void
    {
        $plan = $this->makePlan();

        $this->post(route('subscriptions.subscribe'), ['plan_id' => $plan->id])
            ->assertRedirect(route('login'));
    }

    private function makePlan(): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => 'Plan '.uniqid(),
            'slug' => 'plan-'.uniqid(),
        ]);
    }
}
