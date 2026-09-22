<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use App\Models\VendorVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Admin\VendorVerificationController::approve()/reject() previously had no
 * guard on the verification's current status: an admin could click
 * "Approve Verification" or "Reject Verification" on a record that had
 * already been decided (e.g. a stale page left open in a second tab, or a
 * double click), silently re-stamping reviewed_at/reviewed_by and -- worse
 * -- flipping an already-approved vendor's live account status back to
 * rejected (deactivating it) or vice versa. These tests lock in the fix:
 * both actions now only apply to a still-pending verification and bounce
 * back with an error otherwise, leaving the vendor's status untouched.
 */
class VendorVerificationApprovalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_approve_a_pending_verification(): void
    {
        $admin = User::factory()->admin()->create();
        [, $vendor, $verification] = $this->makePendingVendor();

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.approve', $verification))
            ->assertRedirect(route('admin.vendor-verifications.show', $verification));

        $this->assertSame('approved', $verification->fresh()->status);
        $this->assertSame('approved', $vendor->fresh()->status);
        $this->assertTrue((bool) $vendor->fresh()->is_active);
    }

    public function test_approving_a_vendor_auto_assigns_a_free_subscription_plan(): void
    {
        $freePlan = SubscriptionPlan::create([
            'name' => 'Free Trial',
            'slug' => 'free-trial-'.uniqid(),
            'price' => 0,
            'is_active' => true,
        ]);

        $admin = User::factory()->admin()->create();
        [$user, $vendor, $verification] = $this->makePendingVendor();

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.approve', $verification))
            ->assertRedirect(route('admin.vendor-verifications.show', $verification));

        $subscription = UserSubscription::where('user_id', $user->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame($freePlan->id, $subscription->subscription_plan_id);
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->isActive());
    }

    public function test_approving_a_vendor_does_not_touch_an_existing_active_subscription(): void
    {
        SubscriptionPlan::create([
            'name' => 'Free Trial',
            'slug' => 'free-trial-'.uniqid(),
            'price' => 0,
            'is_active' => true,
        ]);

        $paidPlan = SubscriptionPlan::create([
            'name' => 'Professional',
            'slug' => 'professional-'.uniqid(),
            'price' => 79,
            'is_active' => true,
        ]);

        $admin = User::factory()->admin()->create();
        [$user, $vendor, $verification] = $this->makePendingVendor();

        $existing = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $paidPlan->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.approve', $verification))
            ->assertRedirect(route('admin.vendor-verifications.show', $verification));

        $this->assertSame(1, UserSubscription::where('user_id', $user->id)->count());
        $this->assertSame($paidPlan->id, $existing->fresh()->subscription_plan_id);
    }

    public function test_admin_can_reject_a_pending_verification(): void
    {
        $admin = User::factory()->admin()->create();
        [, $vendor, $verification] = $this->makePendingVendor();

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.reject', $verification), [
                'notes' => 'Documents unreadable.',
            ])
            ->assertRedirect(route('admin.vendor-verifications.show', $verification));

        $this->assertSame('rejected', $verification->fresh()->status);
        $this->assertSame('rejected', $vendor->fresh()->status);
        $this->assertFalse((bool) $vendor->fresh()->is_active);
    }

    public function test_approving_an_already_approved_verification_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        [, $vendor, $verification] = $this->makePendingVendor();
        $verification->update(['status' => 'approved']);
        $vendor->update(['status' => 'approved', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.approve', $verification))
            ->assertRedirect(route('admin.vendor-verifications.show', $verification))
            ->assertSessionHas('error');

        // No-op: still approved, no reviewer stamped by this call.
        $this->assertSame('approved', $verification->fresh()->status);
        $this->assertNull($verification->fresh()->reviewed_by);
    }

    public function test_rejecting_an_already_approved_verification_does_not_deactivate_the_vendor(): void
    {
        $admin = User::factory()->admin()->create();
        [, $vendor, $verification] = $this->makePendingVendor();
        $verification->update(['status' => 'approved']);
        $vendor->update(['status' => 'approved', 'is_active' => true]);

        $this->actingAs($admin)
            ->post(route('admin.vendor-verifications.reject', $verification), ['notes' => 'oops'])
            ->assertRedirect(route('admin.vendor-verifications.show', $verification))
            ->assertSessionHas('error');

        // The whole point of the guard: a stray/duplicate reject click must
        // not flip an already-approved, live vendor back to rejected.
        $this->assertSame('approved', $verification->fresh()->status);
        $this->assertSame('approved', $vendor->fresh()->status);
        $this->assertTrue((bool) $vendor->fresh()->is_active);
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: VendorVerification}
     */
    private function makePendingVendor(): array
    {
        $user = User::factory()->vendor()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'pending',
            'is_active' => false,
        ]);

        $verification = VendorVerification::create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);

        return [$user->fresh(), $vendor->fresh(), $verification->fresh()];
    }
}
