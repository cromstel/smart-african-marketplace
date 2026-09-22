<?php

namespace Tests\Feature\Vendor;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * CouponController::store()/update() have always validated and persisted
 * usage_limit, per_user_limit, starts_at, and expires_at (the model's
 * scopeActive() enforces all four), but the vendor-facing create/edit
 * Blade forms never rendered inputs for them -- unlike the admin coupon
 * forms, which have always had all four. A vendor had no way to give a
 * coupon an expiry date or a usage cap: anything created through
 * /vendor/coupons was silently permanent and unlimited-use until someone
 * manually deactivated it in the database. Fixed by adding the missing
 * fields to resources/views/vendor/coupons/partials/form.blade.php.
 */
class CouponFormFieldsTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_the_create_form_exposes_usage_limit_and_expiry_fields(): void
    {
        [$vendor] = $this->makeVendor();

        $response = $this->actingAs($vendor)->get(route('vendor.coupons.create'));

        $response->assertOk();
        $response->assertSee('name="usage_limit"', false);
        $response->assertSee('name="per_user_limit"', false);
        $response->assertSee('name="starts_at"', false);
        $response->assertSee('name="expires_at"', false);
    }

    public function test_a_vendor_can_set_a_usage_limit_and_expiry_date_when_creating_a_coupon(): void
    {
        [$vendor] = $this->makeVendor();

        $response = $this->actingAs($vendor)->post(route('vendor.coupons.store'), [
            'code' => 'LIMITED2026',
            'type' => 'fixed',
            'value' => 10,
            'usage_limit' => 100,
            'per_user_limit' => 1,
            'expires_at' => '2026-12-31 23:59:00',
        ]);

        $response->assertRedirect(route('vendor.coupons.index'));

        $coupon = Coupon::where('code', 'LIMITED2026')->firstOrFail();
        $this->assertSame(100, $coupon->usage_limit);
        $this->assertSame(1, $coupon->per_user_limit);
        $this->assertNotNull($coupon->expires_at);
        $this->assertSame('2026-12-31 23:59:00', $coupon->expires_at->format('Y-m-d H:i:s'));
    }

    public function test_the_edit_form_pre_fills_the_saved_usage_limit_and_expiry_values(): void
    {
        [$vendor, $vendorModel] = $this->makeVendor();

        $coupon = Coupon::create([
            'vendor_id' => $vendorModel->id,
            'code' => 'PREFILL'.uniqid(),
            'type' => 'fixed',
            'value' => 5,
            'usage_limit' => 50,
            'per_user_limit' => 2,
            'expires_at' => '2026-12-31 23:59:00',
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)->get(route('vendor.coupons.edit', $coupon));

        $response->assertOk();
        $response->assertSee('value="50"', false);
        $response->assertSee('value="2"', false);
        $response->assertSee('value="2026-12-31T23:59"', false);
    }
}
