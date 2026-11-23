<?php

namespace Tests\Feature\Vendor;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Phase 4 (roadmap): "Audit N+1 queries across the 49 admin and 32 vendor
 * controllers." AdminIndexPagesSmokeTest covered the admin side; this
 * extends the same Model::preventLazyLoading() coverage to the 31 vendor
 * index routes, most of which have no dedicated feature test at all.
 */
class VendorIndexPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('indexRoutes')]
    public function test_vendor_index_page_loads_without_a_server_error(string $routeName): void
    {
        [$user] = $this->makeVendorWithFullAccess();

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertStatus($this->expectedStatusFor($routeName));
    }

    public static function indexRoutes(): array
    {
        return collect([
            'vendor.analytics.index',
            'vendor.announcements.index',
            'vendor.attributes.index',
            'vendor.auctions.index',
            'vendor.booking-resources.index',
            'vendor.bookings.index',
            'vendor.brands.index',
            'vendor.categories.index',
            'vendor.coupons.index',
            'vendor.enquiries.index',
            'vendor.followers.index',
            'vendor.inventory.index',
            'vendor.media.index',
            'vendor.orders.index',
            'vendor.payouts.index',
            'vendor.products.index',
            'vendor.refunds.index',
            'vendor.reviews.index',
            'vendor.rma.index',
            'vendor.salon-services.index',
            'vendor.salon-stylists.index',
            'vendor.salon.index',
            'vendor.settings.availability.index',
            'vendor.store-domain.index',
            'vendor.subscription.index',
            'vendor.theme.index',
            'vendor.variations.index',
            'vendor.verification.index',
            'vendor.wallet.index',
            'vendor.withdrawals.index',
        ])->mapWithKeys(fn ($name) => [$name => [$name]])->all();
    }

    private function expectedStatusFor(string $routeName): int
    {
        // Custom domain mapping is gated to the Enterprise plan specifically
        // (by slug, not just module access) -- the fixture plan here isn't
        // Enterprise, so this route correctly redirects rather than 500ing.
        return match ($routeName) {
            'vendor.store-domain.index' => 302,
            default => 200,
        };
    }

    private function makeVendorWithFullAccess(): array
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

        $plan = SubscriptionPlan::create([
            'name' => 'Smoke Test Plan '.uniqid(),
            'slug' => 'smoke-test-plan-'.uniqid(),
            'max_products' => -1,
            'max_orders' => -1,
            'feature_keys' => [
                'auctions',
                'automate_workload_with_ai',
                'bookings',
                'follow_store',
                'manual_order_creation',
                'multiple_product_category',
                'order_management',
                'product_brands_sync',
                'product_enquiry',
                'vendor_dashboard_announcements',
                'vendor_independent_store',
                'vendor_media_library',
                'vendor_rma_request',
                'vendor_shipping_status',
            ],
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        return [$user->fresh(), $vendor];
    }
}
