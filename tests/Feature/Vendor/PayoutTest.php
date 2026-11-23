<?php

namespace Tests\Feature\Vendor;

use App\Models\Payout;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\VendorVerification;
use App\Services\PayoutService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Payout requests move real money out of the platform. Unlike most vendor
 * resources, the danger here is not cross-tenant access -- it is a vendor
 * (or two concurrent requests from the same vendor) draining more than their
 * available balance because the balance check at request time and the
 * balance deduction at completion time are two separate, non-atomic steps.
 */
class PayoutTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_vendor_cannot_request_a_payout_larger_than_their_available_balance(): void
    {
        [$user] = $this->makeVerifiedVendorWithBalance(available: 100);

        $this->actingAs($user)
            ->post(route('vendor.payouts.store'), [
                'amount' => 150,
                'method' => 'bank',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_a_vendor_without_approved_verification_cannot_request_a_payout(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100, verificationStatus: 'pending');
        $this->giveBankDetails($vendor);

        $this->actingAs($user)
            ->post(route('vendor.payouts.store'), [
                'amount' => 50,
                'method' => 'bank',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('payouts', 0);
    }

    public function test_requesting_a_payout_reserves_the_amount_immediately(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100);
        $this->giveBankDetails($vendor);

        $this->actingAs($user)
            ->post(route('vendor.payouts.store'), [
                'amount' => 60,
                'method' => 'bank',
            ])
            ->assertRedirect(route('vendor.payouts.index'));

        $this->assertSame('40.00', VendorBalance::where('vendor_id', $vendor->id)->first()->available);
    }

    /**
     * This is the bug: the store() balance check and the eventual completion
     * deduction are two different moments in time. Without a lock held across
     * "check available, then reserve it," a vendor can fire two requests that
     * each individually fit the balance, and both succeed -- because neither
     * one has reduced `available` when the other one checks it.
     *
     * Reverting the fix (removing the lockForUpdate + immediate reservation in
     * PayoutController::store) makes this test fail: both requests succeed and
     * $150 gets reserved against a $100 balance.
     */
    public function test_two_payout_requests_together_cannot_exceed_the_available_balance(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100);
        $this->giveBankDetails($vendor);

        $this->actingAs($user)->post(route('vendor.payouts.store'), [
            'amount' => 70,
            'method' => 'bank',
        ]);

        $this->actingAs($user)->post(route('vendor.payouts.store'), [
            'amount' => 70,
            'method' => 'bank',
        ]);

        $totalReserved = Payout::where('vendor_id', $vendor->id)->sum('amount');

        $this->assertLessThanOrEqual(
            100,
            (float) $totalReserved,
            'Two payout requests together reserved more than the vendor had available.'
        );
        $this->assertSame(1, Payout::where('vendor_id', $vendor->id)->count());
    }

    public function test_completing_a_manual_payout_does_not_deduct_the_balance_a_second_time(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100);
        $this->giveBankDetails($vendor);

        $this->actingAs($user)->post(route('vendor.payouts.store'), [
            'amount' => 60,
            'method' => 'bank',
        ]);

        $payout = Payout::where('vendor_id', $vendor->id)->firstOrFail();

        app(PayoutService::class)->process($payout);

        $payout->refresh();
        $this->assertSame('completed', $payout->status);
        $this->assertSame('40.00', VendorBalance::where('vendor_id', $vendor->id)->first()->available);

        $this->assertDatabaseHas('vendor_ledgers', [
            'vendor_id' => $vendor->id,
            'type' => 'debit_payout',
            'amount' => '60.00',
        ]);
    }

    public function test_a_failed_payout_refunds_the_reserved_amount_back_to_available(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100);
        $this->giveStripeAccount($vendor);

        $this->actingAs($user)->post(route('vendor.payouts.store'), [
            'amount' => 60,
            'method' => 'stripe',
        ]);

        $payout = Payout::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('40.00', VendorBalance::where('vendor_id', $vendor->id)->first()->available);

        // Stripe payouts are disabled in this test environment, so processing fails.
        app(PayoutService::class)->process($payout);

        $payout->refresh();
        $this->assertSame('failed', $payout->status);
        $this->assertSame(
            '100.00',
            VendorBalance::where('vendor_id', $vendor->id)->first()->available,
            'A failed payout must return the reserved amount to the vendor.'
        );
    }

    public function test_a_failed_payout_can_only_be_refunded_once(): void
    {
        [$user, $vendor] = $this->makeVerifiedVendorWithBalance(available: 100);
        $this->giveStripeAccount($vendor);

        $this->actingAs($user)->post(route('vendor.payouts.store'), [
            'amount' => 60,
            'method' => 'stripe',
        ]);

        $payout = Payout::where('vendor_id', $vendor->id)->firstOrFail();

        app(PayoutService::class)->process($payout);
        app(PayoutService::class)->process($payout); // re-triggered a second time

        $this->assertSame(
            '100.00',
            VendorBalance::where('vendor_id', $vendor->id)->first()->available,
            'Retrying a failed payout refunded the reservation twice.'
        );
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function makeVerifiedVendorWithBalance(float $available, string $verificationStatus = 'approved'): array
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

        VendorVerification::create([
            'vendor_id' => $vendor->id,
            'status' => $verificationStatus,
        ]);

        VendorBalance::create([
            'vendor_id' => $vendor->id,
            'available' => $available,
            'pending' => 0,
            'lifetime_earnings' => $available,
        ]);

        return [$user->fresh(), $vendor->fresh()];
    }

    private function giveBankDetails(Vendor $vendor): void
    {
        $vendor->update([
            'bank_account_number' => '0123456789',
            'bank_name' => 'Test Bank',
        ]);
    }

    private function giveStripeAccount(Vendor $vendor): void
    {
        $vendor->update(['stripe_account_id' => 'acct_test123']);
    }
}
