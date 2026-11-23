<?php

namespace Tests\Feature\Vendor;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor coupons.
 *
 * CouponController::authorizeCoupon() checks
 * `$coupon->vendor_id !== auth()->user()->vendor?->id` and aborts with 403
 * on an implicitly-bound Coupon, rather than a manual scoped query -- so a
 * mismatch surfaces as 403, not 404 (contrast with the findOrFail-based
 * controllers such as ProductEnquiryController).
 */
class CouponIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_coupon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirCoupon] = $this->makeVendorWithCoupon();

        $this->actingAs($mine)
            ->get(route('vendor.coupons.edit', $theirCoupon))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_coupon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirCoupon] = $this->makeVendorWithCoupon();
        $originalValue = $theirCoupon->value;

        $this->actingAs($mine)
            ->put(route('vendor.coupons.update', $theirCoupon), [
                'code' => 'HIJACKED',
                'type' => 'fixed',
                'value' => 999,
            ])
            ->assertForbidden();

        $this->assertSame(
            (string) $originalValue,
            (string) $theirCoupon->fresh()->value,
            "A vendor updated another vendor's coupon. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_coupon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirCoupon] = $this->makeVendorWithCoupon();

        $this->actingAs($mine)
            ->delete(route('vendor.coupons.destroy', $theirCoupon))
            ->assertForbidden();

        $this->assertDatabaseHas('coupons', ['id' => $theirCoupon->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_coupon(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myCoupon] = $this->makeVendorWithCoupon();

        $this->actingAs($mine)
            ->get(route('vendor.coupons.edit', $myCoupon))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Coupon}
     */
    private function makeVendorWithCoupon(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => 'CODE'.strtoupper(uniqid()),
            'type' => 'fixed',
            'value' => 10.00,
            'usage_limit' => 100,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return [$user, $vendor, $coupon];
    }
}
