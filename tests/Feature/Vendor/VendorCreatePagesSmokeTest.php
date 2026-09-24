<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Completes the Phase 4 N+1 audit's route-shape coverage on the vendor side
 * (see VendorIndexPagesSmokeTest for index, VendorCatalogShowEditPagesSmokeTest
 * and VendorOperationsShowEditPagesSmokeTest for show/edit) with the last
 * uncovered shape: create pages. `vendor.products.variants.create` needs a
 * parent {product}; `vendor.shipments.create` needs a parent {order} with no
 * shipment yet (ShipmentController::create() redirects away if one already
 * exists, so the fixture must not create one).
 *
 * This file found a real bug: `vendor.media.create` had a route
 * (routes/web.php, hand-written to mirror the admin equivalent's shape) but
 * MediaController::create() was never implemented, no view existed, and
 * nothing in the vendor UI even linked to it (media management there is
 * entirely modal-based, via the same upload() endpoint). Unlike a route
 * nothing reaches, hitting this URL directly threw a fatal
 * BadMethodCallException rather than a 404 or a graceful redirect. Built
 * the missing create()/view pair, mirroring Admin\MediaLibraryController's
 * working equivalent.
 *
 * `vendor.withdrawals.create` is asserted as a 302, not a bug: a freshly
 * created fixture vendor has a $0 available balance, below the minimum
 * withdrawal amount, and WithdrawalController::create() deliberately
 * redirects rather than rendering in that state -- a realistic vendor
 * state to exercise, not a fixture gap to paper over.
 */
class VendorCreatePagesSmokeTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    /**
     * @dataProvider createRoutes
     */
    public function test_vendor_create_page_loads_without_a_server_error(string $routeName): void
    {
        [$user, $routeParams] = $this->fixtureFor($routeName);

        $response = $this->actingAs($user)->get(route($routeName, $routeParams));

        $response->assertStatus($this->expectedStatusFor($routeName));
    }

    public static function createRoutes(): array
    {
        return collect([
            'vendor.attributes.create',
            'vendor.auctions.create',
            'vendor.booking-resources.create',
            'vendor.brands.create',
            'vendor.coupons.create',
            'vendor.media.create',
            'vendor.orders.create',
            'vendor.shipments.create',
            'vendor.payouts.create',
            'vendor.products.create',
            'vendor.products.variants.create',
            'vendor.salon-services.create',
            'vendor.salon-stylists.create',
            'vendor.salon.create',
            'vendor.withdrawals.create',
        ])->mapWithKeys(fn ($name) => [$name => [$name]])->all();
    }

    /**
     * @return array{0: User, 1: array}
     */
    private function fixtureFor(string $routeName): array
    {
        return match ($routeName) {
            'vendor.products.variants.create' => $this->productVariantFixture(),
            'vendor.shipments.create' => $this->shipmentFixture(),
            default => [$this->makeFullyEntitledVendor()[0], []],
        };
    }

    private function expectedStatusFor(string $routeName): int
    {
        // A freshly-created fixture vendor has a $0 available balance, below
        // the minimum withdrawal amount -- WithdrawalController::create()
        // deliberately redirects rather than rendering the page in that
        // state (see the class docblock: this is a realistic vendor state
        // to exercise, not a fixture gap to paper over).
        return match ($routeName) {
            'vendor.withdrawals.create' => 302,
            default => 200,
        };
    }

    private function productVariantFixture(): array
    {
        [$user, , $product] = $this->makeProduct();

        return [$user, ['product' => $product]];
    }

    private function shipmentFixture(): array
    {
        [$user, , , $order] = $this->makeOrderWithItem();

        return [$user, ['order' => $order->id]];
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function makeFullyEntitledVendor(): array
    {
        return $this->makeVendor([
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
        ]);
    }

    /**
     * @return array{0: User, 1: Vendor, 2: Product}
     */
    private function makeProduct(): array
    {
        [$user, $vendor] = $this->makeFullyEntitledVendor();

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
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

        return [$user, $vendor, $product];
    }

    /**
     * @return array{0: User, 1: Vendor, 2: Product, 3: Order}
     */
    private function makeOrderWithItem(): array
    {
        [$user, $vendor, $product] = $this->makeProduct();
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'is_parent' => false,
            'subtotal' => 20.00,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 20.00,
            'payment_status' => 'paid',
            'status' => 'processing',
            'payment_method' => 'manual',
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

        return [$user, $vendor, $product, $order->fresh()];
    }
}
