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
 * Regression coverage for POST /vendor/orders (vendor.orders.store).
 *
 * order_items.product_name and order_items.product_sku are NOT NULL
 * columns with no default (see the order_items migration). The manual
 * order creation path built OrderItem rows without either field -- unlike
 * CheckoutController's normal checkout flow, which populates them from the
 * product -- so every manual order submission failed with a raw SQL error
 * ("Field 'product_name' doesn't have a default value") that was then
 * leaked straight to the vendor via `$e->getMessage()`.
 */
class ManualOrderCreationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_can_create_a_manual_order_for_a_customer(): void
    {
        [$user, $vendor] = $this->makeVendor(['order_management', 'manual_order_creation']);
        $product = $this->makeProductFor($vendor);
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post(route('vendor.orders.store'), [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
            'shipping_cost' => 5,
            'discount' => 0,
            'notes' => 'Phone order',
        ]);

        $order = Order::where('user_id', $customer->id)->latest('id')->first();

        $this->assertNotNull($order, 'The manual order was not created.');
        $response->assertRedirect(route('vendor.orders.show', $order->id));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        $item = OrderItem::where('order_id', $order->id)->first();

        $this->assertNotNull($item, 'No order_items row was created for the manual order.');
        $this->assertSame($product->name, $item->product_name);
        $this->assertSame($product->sku, $item->product_sku);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('40.00', (string) $item->subtotal);
        $this->assertSame('40.00', (string) $item->total);
        $this->assertSame('0.00', (string) $item->tax);
        $this->assertSame($vendor->id, $item->vendor_id);

        $this->assertSame(3, $product->fresh()->stock_quantity, 'Stock was not decremented correctly.');
    }

    public function test_a_manual_order_failure_does_not_leak_the_raw_exception_to_the_vendor(): void
    {
        [$user, $vendor] = $this->makeVendor(['order_management', 'manual_order_creation']);
        $product = $this->makeProductFor($vendor, stock: 1);
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->post(route('vendor.orders.store'), [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Insufficient stock', session('error'));
        $this->assertStringNotContainsString('SQLSTATE', session('error'));
        $this->assertStringNotContainsString('smart_africanmarket', session('error'));
    }

    // --------------------------------------------------------------- Helpers

    private function makeProductFor(Vendor $vendor, int $stock = 5): Product
    {
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Manual order fixture.',
            'price' => 20.00,
            'stock_quantity' => $stock,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }
}
