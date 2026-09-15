<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * RefundRequestController::store() already carries a correct
 * $order->user_id !== auth()->id() check with no route-level policy backing
 * it up (unlike the booking routes fixed alongside this test). This proves
 * that check actually holds, and that it isn't a bare abort(403) that
 * happens to be unreachable for some other reason (wrong route, wrong
 * model binding, etc).
 */
class RefundRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_customer_can_request_a_refund_for_their_own_order(): void
    {
        $customer = User::factory()->create();
        $order = $this->makeOrderFor($customer);

        $this->actingAs($customer)
            ->post(route('orders.refunds.store', $order), ['reason' => 'Item arrived damaged.'])
            ->assertRedirect();

        $this->assertDatabaseHas('refund_requests', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);
    }

    public function test_a_customer_cannot_request_a_refund_for_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->makeOrderFor($owner);

        $this->actingAs($stranger)
            ->post(route('orders.refunds.store', $order), ['reason' => 'Not mine, but trying anyway.'])
            ->assertForbidden();

        $this->assertDatabaseCount('refund_requests', 0);
    }

    public function test_a_second_refund_request_is_blocked_while_one_is_pending(): void
    {
        $customer = User::factory()->create();
        $order = $this->makeOrderFor($customer);

        RefundRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'reason' => 'First request.',
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->post(route('orders.refunds.store', $order), ['reason' => 'Second attempt.'])
            ->assertRedirect();

        $this->assertSame(1, RefundRequest::where('order_id', $order->id)->count());
    }

    public function test_guest_cannot_request_a_refund(): void
    {
        $customer = User::factory()->create();
        $order = $this->makeOrderFor($customer);

        $this->post(route('orders.refunds.store', $order), ['reason' => 'Guest attempt.'])
            ->assertRedirect(route('login'));
    }

    private function makeOrderFor(User $customer): Order
    {
        $vendorOwner = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorOwner->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Product '.uniqid(),
            'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()),
            'description' => 'Fixture.',
            'price' => 40,
            'stock_quantity' => 10,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'RF-'.uniqid(),
            'user_id' => $customer->id,
            'subtotal' => 40, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => 40,
            'payment_status' => 'paid',
            'status' => 'delivered',
            'payment_method' => 'card',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => 40,
            'subtotal' => 40,
            'total' => 40,
        ]);

        return $order->fresh();
    }
}
