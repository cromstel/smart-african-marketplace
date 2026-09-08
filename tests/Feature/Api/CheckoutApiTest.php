<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
{
    use DatabaseTransactions;

    private function setUpCartWithItem(User $user): Cart
    {
        $product = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('vendor_id')
            ->where('vendor_id', '>', 0)
            ->first() ?? $this->createSellableProduct();

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
            'quantity' => 1,
            'price' => 10.00,
            'subtotal' => 10.00,
        ]);

        $cart->calculateTotals();

        return $cart;
    }

    /**
     * Creates a vendor-owned, in-stock product.
     *
     * These tests previously called firstOrFail() against the catalogue, so
     * they only passed on a seeded database and failed in CI, which migrates
     * but does not seed. Building the fixture here makes them self-sufficient
     * while still preferring real seed data when it is present.
     */
    private function createSellableProduct(): Product
    {
        $owner = User::factory()->create();

        $vendor = Vendor::create([
            'user_id' => $owner->id,
            'business_name' => 'Test Vendor '.uniqid(),
            'slug' => 'test-vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Test Category '.uniqid(),
            'slug' => 'test-category-'.uniqid(),
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Test Product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'A product created for checkout tests.',
            'price' => 25.00,
            'stock_quantity' => 100,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
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
        Sanctum::actingAs($user);
        $this->setUpCartWithItem($user);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $response = $this->postJson('/api/v1/checkout', [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.payment_status', 'pending')
            ->assertJsonPath('data.payment.method', 'cash_on_delivery');

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'is_parent' => true]);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'is_parent' => false,
            'payment_method' => 'cash_on_delivery',
        ]);
        $this->assertNull(Cart::where('user_id', $user->id)->first());
    }

    public function test_checkout_requires_valid_addresses(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->setUpCartWithItem($user);

        $this->postJson('/api/v1/checkout', [
            'shipping_address_id' => 999999,
            'payment_method' => 'cash_on_delivery',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['billing_address_id']);
    }

    public function test_empty_cart_cannot_be_checked_out(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->postJson('/api/v1/checkout', [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'cash_on_delivery',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Your cart is empty');
    }

    public function test_paystack_returns_error_when_not_configured(): void
    {
        Setting::set('paystack_enabled', false);
        Setting::set('paystack_secret_key', '');

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->setUpCartWithItem($user);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->postJson('/api/v1/checkout', [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'paystack',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Paystack payment is not configured');
    }

    public function test_mobile_money_requires_phone_number(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->setUpCartWithItem($user);
        $billing = $this->makeAddress($user, 'billing');
        $shipping = $this->makeAddress($user, 'shipping');

        $this->postJson('/api/v1/checkout', [
            'billing_address_id' => $billing->id,
            'shipping_address_id' => $shipping->id,
            'payment_method' => 'mobile_money',
            'mobile_money_provider' => 'mtn',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_shipping_preview_returns_totals(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $this->setUpCartWithItem($user);
        $shipping = $this->makeAddress($user, 'shipping');

        $this->postJson('/api/v1/checkout/shipping-preview', ['address_id' => $shipping->id])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['subtotal', 'tax', 'shipping', 'discount', 'total', 'mode']]);
    }

    public function test_unauthenticated_checkout_is_rejected(): void
    {
        $this->postJson('/api/v1/checkout', [
            'billing_address_id' => 1,
            'shipping_address_id' => 1,
            'payment_method' => 'cash_on_delivery',
        ])->assertStatus(401);
    }
}
