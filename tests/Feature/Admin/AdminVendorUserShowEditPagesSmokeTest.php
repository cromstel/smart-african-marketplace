<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SalonService;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorVerification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Extends the Phase 4 N+1 audit (see AdminIndexPagesSmokeTest) from admin
 * index pages to admin show/edit pages, covering vendor and user management:
 * vendors, users, and vendor verifications. VendorController::show() is the
 * heaviest of the three -- it loads the vendor's products, its own recent
 * bookings (eager-loaded via salonService/user), and computes several
 * order-derived stats, so the fixture gives it a product, a booking, and an
 * order with an item actually attributed to this vendor.
 */
class AdminVendorUserShowEditPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('showEditRoutes')]
    public function test_admin_show_edit_page_loads_without_a_server_error(string $routeName, string $fixtureMethod): void
    {
        $admin = User::factory()->admin()->create();
        $routeParams = $this->$fixtureMethod();

        $response = $this->actingAs($admin)->get(route($routeName, $routeParams));

        $response->assertOk();
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'admin.vendors.edit' => 'vendorFixture',
            'admin.vendors.show' => 'vendorFixture',
            'admin.users.edit' => 'userFixture',
            'admin.vendor-verifications.show' => 'vendorVerificationFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function vendorFixture(): array
    {
        [$vendor] = $this->makeVendorWithActivity();

        return ['vendor' => $vendor];
    }

    private function userFixture(): array
    {
        $user = User::factory()->create(['role' => 'customer']);

        return ['user' => $user];
    }

    private function vendorVerificationFixture(): array
    {
        [$vendor] = $this->makeVendorWithActivity();

        $verification = VendorVerification::create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
            'documents' => [
                ['path' => 'verifications/fixture.pdf', 'original_name' => 'fixture.pdf', 'size' => 1024],
            ],
        ]);

        return ['verification' => $verification];
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: Vendor}
     */
    private function makeVendorWithActivity(): array
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Fixture product.',
            'price' => 20.00,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'subtotal' => 20.00,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 20.00,
            'payment_status' => 'paid',
            'status' => 'delivered',
            'payment_method' => 'manual',
            'delivered_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => 20.00,
            'subtotal' => 20.00,
            'total' => 20.00,
        ]);

        $service = SalonService::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 50,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $vendorUser->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);

        return [$vendor->fresh()];
    }
}
