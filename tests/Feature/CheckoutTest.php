<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use DatabaseTransactions;

    private function makeVendorProduct(int $stock = 100, ?int $vendorId = null): Product
    {
        if ($vendorId === null) {
            $owner = User::factory()->create();

            $vendor = Vendor::create([
                'user_id' => $owner->id,
                'business_name' => 'Vendor '.uniqid(),
                'slug' => 'vendor-'.uniqid(),
                'business_email' => 'vendor'.uniqid().'@example.com',
                'business_phone' => '+15550000000',
                'business_address' => '1 Test Street',
                'status' => 'approved',
                'is_active' => true,
            ]);
            $vendorId = $vendor->id;
        }

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
        ]);

        return Product::create([
            'vendor_id' => $vendorId,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'A product created for checkout tests.',
            'price' => 25.00,
            'stock_quantity' => $stock,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }

    private function cartWithProduct(User $user, Product $product, int $quantity = 1): Cart
    {
        $cart = Cart::create([
            'user_id' => $user->id,
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'discount' => 0,
            'total' => 0,
        ]);

        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $product->price * $quantity,
        ]);

        $cart->calculateTotals();

        return $cart;
    }

    private function makeAddress(User $user, string $type): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => $type,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address_line1' => '123 Market St',
            'city' => 'Lagos',
            'country' => 'NG',
            'is_default' => true,
        ]);
    }

    public function test_cash_on_delivery_creates_order_and_clears_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->makeVendorProduct(stock: 10);
        $this->cartWithProduct($user, $product);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'is_parent' => true, 'payment_method' => 'cash_on_delivery']);
        $this->assertNull(Cart::where('user_id', $user->id)->first());
        $this->assertSame(9, $product->fresh()->stock_quantity);
    }

    public function test_resubmitting_checkout_after_choosing_a_gateway_does_not_duplicate_the_order(): void
    {
        $user = User::factory()->create();
        $product = $this->makeVendorProduct(stock: 10);
        $this->cartWithProduct($user, $product, quantity: 2);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $payload = [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'stripe',
        ];

        // First submission: creates a pending order awaiting payment and
        // clears the cart immediately (the order stays reachable via its own
        // route binding, independent of the cart).
        $first = $this->actingAs($user)->post(route('checkout.store'), $payload);
        $first->assertRedirect(route('payment.stripe', Order::where('user_id', $user->id)->where('is_parent', true)->firstOrFail()));
        $this->assertNull(Cart::where('user_id', $user->id)->first());
        $this->assertSame(1, Order::where('user_id', $user->id)->where('is_parent', true)->count());
        $this->assertSame(8, $product->fresh()->stock_quantity);

        // Resubmitting (e.g. a double-click, or the browser back button) now
        // sees an empty cart instead of creating a second order and
        // decrementing stock a second time.
        $second = $this->actingAs($user)->post(route('checkout.store'), $payload);
        $second->assertRedirect(route('cart.index'));
        $this->assertSame(1, Order::where('user_id', $user->id)->where('is_parent', true)->count());
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_a_cart_that_exceeds_available_stock(): void
    {
        $user = User::factory()->create();
        // Stock dropped to 1 (another sale, a vendor correction, etc.) after
        // this item was added to the cart with quantity 2.
        $product = $this->makeVendorProduct(stock: 1);
        $this->cartWithProduct($user, $product, quantity: 2);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Not enough stock', session('error'));
        $this->assertSame(0, Order::where('user_id', $user->id)->count());
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertNotNull(Cart::where('user_id', $user->id)->first());
    }

    public function test_multi_vendor_cart_splits_into_child_orders_per_vendor(): void
    {
        $user = User::factory()->create();
        $productA = $this->makeVendorProduct(stock: 10);
        $productB = $this->makeVendorProduct(stock: 10);

        $cart = $this->cartWithProduct($user, $productA, quantity: 1);
        $cart->items()->create([
            'product_id' => $productB->id,
            'quantity' => 1,
            'price' => $productB->price,
            'subtotal' => $productB->price,
        ]);
        $cart->calculateTotals();

        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->actingAs($user)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect();

        $parent = Order::where('user_id', $user->id)->where('is_parent', true)->firstOrFail();
        $children = Order::where('order_group_id', $parent->order_group_id)->where('is_parent', false)->get();

        $this->assertCount(2, $children);
        $this->assertEqualsWithDelta(
            (float) $parent->total,
            (float) $children->sum('total'),
            0.01,
            'Child order totals should add back up to the parent order total.'
        );

        $vendorIds = $children->flatMap(fn ($child) => $child->items->pluck('vendor_id'))
            ->unique()->sort()->values()->all();
        $expectedVendorIds = collect([$productA->vendor_id, $productB->vendor_id])->sort()->values()->all();
        $this->assertSame($expectedVendorIds, $vendorIds);
    }

    public function test_a_customer_cannot_pay_or_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $product = $this->makeVendorProduct(stock: 10);
        $this->cartWithProduct($owner, $product);
        $billing = $this->makeAddress($owner, 'billing');
        $shipping = $this->makeAddress($owner, 'shipping');

        $this->actingAs($owner)->post(route('checkout.store'), [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'stripe',
        ])->assertRedirect();

        $order = Order::where('user_id', $owner->id)->where('is_parent', true)->firstOrFail();

        $this->actingAs($stranger)->get(route('payment.stripe', $order))->assertForbidden();
        $this->actingAs($stranger)->get(route('payment.success', $order))->assertForbidden();
        $this->actingAs($stranger)->get(route('payment.failed', $order))->assertForbidden();
    }

    public function test_checkout_requires_authentication(): void
    {
        $this->post(route('checkout.store'), [
            'billing_address_id' => 1,
            'shipping_address_id' => 1,
            'payment_method' => 'cash_on_delivery',
        ])->assertRedirect(route('login'));
    }
}
