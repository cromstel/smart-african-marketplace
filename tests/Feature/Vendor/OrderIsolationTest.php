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
 * Cross-tenant isolation for vendor order management.
 *
 * OrderController computed `$vendorId = auth()->id();` and then compared it
 * against `products.vendor_id` / `order_items.vendor_id` -- both of which
 * reference `vendors.id`, not `users.id`. Since a vendor's user id and its
 * own vendor id are unrelated auto-increment sequences, vendor A could view,
 * update the status of, and leave internal notes on vendor B's orders
 * whenever A's user id happened to match B's vendor id (or, more subtly,
 * simply got a "not found" for their own orders when the ids diverged the
 * other way). See VendorIsolationTest / WithdrawalTest for the same bug
 * class elsewhere in this controller family.
 */
class OrderIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_order(): void
    {
        [$mine] = $this->makeVendor(['order_management']);
        [, , $theirOrder] = $this->makeVendorWithOrder();

        $this->actingAs($mine)
            ->get(route('vendor.orders.show', $theirOrder))
            ->assertNotFound();
    }

    public function test_a_vendor_cannot_change_another_vendors_order_status(): void
    {
        [$mine] = $this->makeVendor(['order_management']);
        [, , $theirOrder] = $this->makeVendorWithOrder();
        $originalStatus = $theirOrder->status;

        $this->actingAs($mine)
            ->post(route('vendor.orders.updateStatus', $theirOrder), ['status' => 'processing'])
            ->assertNotFound();

        $this->assertSame(
            $originalStatus,
            $theirOrder->fresh()->status,
            "A vendor changed another vendor's order status. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_add_a_note_to_another_vendors_order(): void
    {
        [$mine] = $this->makeVendor(['order_management']);
        [, , $theirOrder] = $this->makeVendorWithOrder();

        $this->actingAs($mine)
            ->post(route('vendor.orders.addNote', $theirOrder), ['note' => 'Hijacked note'])
            ->assertNotFound();

        $this->assertDatabaseMissing('order_notes', [
            'order_id' => $theirOrder->id,
            'note' => 'Hijacked note',
        ]);
    }

    public function test_a_vendor_cannot_see_another_vendors_order_in_their_index(): void
    {
        [$mine] = $this->makeVendor(['order_management']);
        [, , $theirOrder] = $this->makeVendorWithOrder();

        $response = $this->actingAs($mine)->get(route('vendor.orders.index'));

        $response->assertOk();
        $response->assertDontSee($theirOrder->order_number);
    }

    public function test_a_vendor_can_still_view_their_own_order(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myOrder] = $this->makeVendorWithOrder();

        $this->actingAs($mine)
            ->get(route('vendor.orders.show', $myOrder))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Order}
     */
    private function makeVendorWithOrder(): array
    {
        [$user, $vendor] = $this->makeVendor(['order_management']);

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
            'payment_status' => 'pending',
            'status' => 'pending',
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

        return [$user, $vendor, $order->fresh()];
    }
}
