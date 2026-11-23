<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor refund requests.
 *
 * RefundRequest has no `vendor_id` column of its own -- ownership is
 * derived from `order.items.product.vendor_id`. show()/approve()/reject()
 * all recompute that via authorizeVendorRefund()/an inline equivalent and
 * abort with 403 on a mismatch.
 */
class RefundRequestIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_refund_request(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirRefund] = $this->makeVendorWithRefundRequest();

        $this->actingAs($mine)
            ->get(route('vendor.refunds.show', $theirRefund))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_approve_another_vendors_refund_request(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirRefund] = $this->makeVendorWithRefundRequest();

        $this->actingAs($mine)
            ->post(route('vendor.refunds.approve', $theirRefund))
            ->assertForbidden();

        $this->assertSame(
            'pending',
            $theirRefund->fresh()->vendor_status,
            "A vendor approved another vendor's refund request. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_reject_another_vendors_refund_request(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirRefund] = $this->makeVendorWithRefundRequest();

        $this->actingAs($mine)
            ->post(route('vendor.refunds.reject', $theirRefund))
            ->assertForbidden();

        $this->assertSame(
            'pending',
            $theirRefund->fresh()->vendor_status,
            "A vendor rejected another vendor's refund request. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_can_still_view_their_own_refund_request(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , , $myRefund] = $this->makeVendorWithRefundRequest();

        $this->actingAs($mine)
            ->get(route('vendor.refunds.show', $myRefund))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Order, 3: RefundRequest}
     */
    private function makeVendorWithRefundRequest(): array
    {
        [$user, $vendor] = $this->makeVendor();

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
            'status' => 'delivered',
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

        $refund = RefundRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => 'pending',
            'vendor_status' => 'pending',
            'reason' => 'Isolation fixture.',
        ]);

        return [$user, $vendor, $order->fresh(), $refund];
    }
}
