<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * CheckSubscriptionLimits (aliased as the `subscription.limits` route
 * middleware) enforces a plan's max_products/max_orders caps. It was fully
 * implemented and aliased in Kernel.php but never actually attached to any
 * route, so these caps had never been enforced -- it was wired onto
 * products.store and orders.create/orders.store as part of this change.
 *
 * Wiring it up surfaced a second, previously-dormant bug: checkOrderLimit()
 * called User::vendorOrders(), a relation that did not exist anywhere in the
 * codebase. Attaching the middleware without adding it would have turned
 * every capped vendor's visit to the manual-order-creation screen into a
 * 500 error instead of a graceful limit message (see the vendorOrders()
 * fix on User).
 */
class SubscriptionLimitsTest extends TestCase
{
    use DatabaseTransactions;

    // ------------------------------------------------------- Product limit

    public function test_a_vendor_at_their_product_limit_cannot_create_a_new_product(): void
    {
        [$user, $vendor] = $this->makeVendor(maxProducts: 1);
        $this->makeProductFor($vendor);

        $response = $this->actingAs($user)->post(route('vendor.products.store'), [
            'name' => 'One Too Many',
            'price' => 10,
            'stock_quantity' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString("plan's limit", session('error'));
        $this->assertSame(1, Product::where('vendor_id', $vendor->id)->count());
    }

    public function test_a_vendor_under_their_product_limit_is_not_blocked_by_the_limit_check(): void
    {
        [$user, $vendor] = $this->makeVendor(maxProducts: 2);
        $this->makeProductFor($vendor);
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        $response = $this->actingAs($user)->post(route('vendor.products.store'), [
            'name' => 'Room To Grow',
            'slug' => 'room-to-grow-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'category_id' => $category->id,
            'price' => 10,
            'stock_quantity' => 5,
        ]);

        // Whatever the controller's own validation makes of this payload,
        // it must not be the subscription-limit message.
        $response->assertSessionMissing('error');
    }

    public function test_a_vendor_on_an_unlimited_plan_is_never_blocked_from_creating_products(): void
    {
        [$user, $vendor] = $this->makeVendor(maxProducts: -1);
        for ($i = 0; $i < 5; $i++) {
            $this->makeProductFor($vendor);
        }

        $response = $this->actingAs($user)->post(route('vendor.products.store'), [
            'name' => 'Still Fine',
        ]);

        $response->assertSessionMissing('error');
    }

    public function test_a_vendor_at_their_product_limit_can_still_edit_an_existing_product(): void
    {
        [$user, $vendor] = $this->makeVendor(maxProducts: 1);
        $product = $this->makeProductFor($vendor);

        $this->actingAs($user)
            ->get(route('vendor.products.edit', $product))
            ->assertOk();

        $response = $this->actingAs($user)->put(route('vendor.products.update', $product), [
            'name' => 'Updated Name',
            'price' => 15,
            'stock_quantity' => 3,
        ]);

        $response->assertSessionMissing('error');
    }

    // --------------------------------------------------------- Order limit

    public function test_a_vendor_at_their_order_limit_is_redirected_from_manual_order_creation(): void
    {
        [$user, $vendor] = $this->makeVendor(maxOrders: 1, featureKeys: ['order_management', 'manual_order_creation']);
        $this->makeOrderFor($vendor);

        $response = $this->actingAs($user)->get(route('vendor.orders.create'));

        $response->assertRedirect(route('subscriptions.my'));
        $response->assertSessionHas('warning');
    }

    public function test_a_vendor_at_their_order_limit_is_blocked_from_submitting_a_manual_order(): void
    {
        [$user, $vendor] = $this->makeVendor(maxOrders: 1, featureKeys: ['order_management', 'manual_order_creation']);
        $this->makeOrderFor($vendor);
        $countBefore = Order::count();

        $response = $this->actingAs($user)->post(route('vendor.orders.store'), []);

        $response->assertRedirect(route('subscriptions.my'));
        $response->assertSessionHas('warning');
        $this->assertSame($countBefore, Order::count(), 'A blocked request must not create an order.');
    }

    public function test_a_vendor_under_their_order_limit_can_reach_manual_order_creation(): void
    {
        [$user, $vendor] = $this->makeVendor(maxOrders: 2, featureKeys: ['order_management', 'manual_order_creation']);
        $this->makeOrderFor($vendor);

        $this->actingAs($user)
            ->get(route('vendor.orders.create'))
            ->assertOk();
    }

    public function test_a_vendor_on_an_unlimited_order_plan_is_never_blocked(): void
    {
        [$user, $vendor] = $this->makeVendor(maxOrders: -1, featureKeys: ['order_management', 'manual_order_creation']);
        for ($i = 0; $i < 3; $i++) {
            $this->makeOrderFor($vendor);
        }

        $this->actingAs($user)
            ->get(route('vendor.orders.create'))
            ->assertOk();
    }

    public function test_a_vendor_at_their_order_limit_can_still_view_existing_orders(): void
    {
        [$user, $vendor] = $this->makeVendor(maxOrders: 1, featureKeys: ['order_management', 'manual_order_creation']);
        $order = $this->makeOrderFor($vendor);

        $this->actingAs($user)->get(route('vendor.orders.index'))->assertOk();
        $this->actingAs($user)->get(route('vendor.orders.show', $order))->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor}
     */
    private function makeVendor(int $maxProducts = -1, int $maxOrders = -1, array $featureKeys = ['multiple_product_category']): array
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
            'name' => 'Limits Test Plan '.uniqid(),
            'slug' => 'limits-test-plan-'.uniqid(),
            'feature_keys' => array_values(array_unique($featureKeys)),
            'max_products' => $maxProducts,
            'max_orders' => $maxOrders,
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        return [$user->fresh(), $vendor];
    }

    private function makeProductFor(Vendor $vendor): Product
    {
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Limits fixture.',
            'price' => 20.00,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);
    }

    private function makeOrderFor(Vendor $vendor): Order
    {
        $product = $this->makeProductFor($vendor);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'is_parent' => false,
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

        return $order->fresh();
    }
}
