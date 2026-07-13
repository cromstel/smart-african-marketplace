<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    // Isolated test DB: phpunit.xml points DB_DATABASE at a dedicated
    // `african_market_test` clone. Writes are wrapped in a transaction per
    // test and rolled back, so the dev DB is never touched.
    use DatabaseTransactions;

    public function test_guests_are_redirected_to_login_for_protected_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('cart.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('vendor.dashboard'))->assertRedirect(route('login'));
    }

    public function test_guests_hit_login_before_verification_on_sensitive_actions(): void
    {
        $this->post(route('subscriptions.subscribe'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'))->assertRedirect(route('login'));
    }

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }

    public function test_unverified_vendor_is_redirected_to_verification_notice(): void
    {
        $vendor = User::factory()->vendor()->unverified()->create();

        $this->actingAs($vendor)
            ->get(route('vendor.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_vendor_passes_verification_gate(): void
    {
        // Vendor middleware is role-based; a verified vendor (no Vendor record
        // yet) passes the `verified` gate and is only redirected to home by the
        // dashboard controller because no vendor profile exists yet.
        $vendor = User::factory()->vendor()->create();

        $response = $this->actingAs($vendor)->get(route('vendor.dashboard'));

        $response->assertRedirect();
        $this->assertNotEquals(route('verification.notice'), $response->headers->get('Location'));
    }
}
