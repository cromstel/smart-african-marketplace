<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor shipments.
 *
 * ShipmentController scopes every lookup with
 * `Shipment::whereHas('order.items.product', fn ($q) => $q->where('vendor_id', $vendorId))->findOrFail($id)`
 * (a manual scoped query, not implicit route-model-binding), so a
 * cross-tenant request surfaces as 404, matching ProductEnquiryController's
 * pattern. Routes sit behind module.access:vendor_shipping_status, which is
 * not one of MakesIsolatedVendors' default feature keys, so it is
 * requested explicitly here.
 */
class ShipmentIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_shipment(): void
    {
        [$mine] = $this->makeVendor(['vendor_shipping_status']);
        [, , , $theirShipment] = $this->makeVendorWithShipment();

        $this->actingAs($mine)
            ->get(route('vendor.shipments.show', $theirShipment))
            ->assertNotFound();
    }

    public function test_a_vendor_cannot_add_tracking_to_another_vendors_shipment(): void
    {
        [$mine] = $this->makeVendor(['vendor_shipping_status']);
        [, , , $theirShipment] = $this->makeVendorWithShipment();
        $originalStatus = $theirShipment->status;

        $this->actingAs($mine)
            ->post(route('vendor.shipments.tracking', $theirShipment), [
                'status' => 'delivered',
                'location' => 'Hijacked warehouse',
            ])
            ->assertNotFound();

        $this->assertSame(
            $originalStatus,
            $theirShipment->fresh()->status,
            "A vendor updated tracking on another vendor's shipment. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_start_a_shipment_for_another_vendors_order(): void
    {
        [$mine] = $this->makeVendor(['vendor_shipping_status']);
        [, , $theirOrder] = $this->makeVendorWithShipment();

        $this->actingAs($mine)
            ->get(route('vendor.shipments.create', $theirOrder))
            ->assertNotFound();
    }

    public function test_a_vendor_can_still_view_their_own_shipment(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , , $myShipment] = $this->makeVendorWithShipment();

        $this->actingAs($mine)
            ->get(route('vendor.shipments.show', $myShipment))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Order, 3: Shipment}
     */
    private function makeVendorWithShipment(): array
    {
        [$user, $vendor] = $this->makeVendor(['vendor_shipping_status']);

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
            'description' => 'Isolation fixture.',
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
            'status' => 'shipped',
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

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'TRACK-'.strtoupper(uniqid()),
            'carrier' => 'Isolation Carrier',
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);

        return [$user, $vendor, $order->fresh(), $shipment];
    }
}
