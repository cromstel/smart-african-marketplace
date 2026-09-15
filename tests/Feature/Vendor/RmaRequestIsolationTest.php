<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RmaRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor RMA (return/refund) handling.
 *
 * Same bug class as OrderController: `$vendorId = auth()->id();` was
 * compared against `order_items.product.vendor_id` (a `vendors.id`), so a
 * vendor could view and decide the outcome of another vendor's return
 * requests -- including approving refunds against sales they never made.
 */
class RmaRequestIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_rma_request(): void
    {
        [$mine] = $this->makeVendor(['vendor_rma_request']);
        [, , $theirRma] = $this->makeVendorWithRmaRequest();

        $this->actingAs($mine)
            ->get(route('vendor.rma.show', $theirRma))
            ->assertNotFound();
    }

    public function test_a_vendor_cannot_decide_another_vendors_rma_request(): void
    {
        [$mine] = $this->makeVendor(['vendor_rma_request']);
        [, , $theirRma] = $this->makeVendorWithRmaRequest();
        $originalStatus = $theirRma->status;

        $this->actingAs($mine)
            ->post(route('vendor.rma.updateStatus', $theirRma), [
                'status' => 'approved',
                'response' => 'Hijacked approval',
            ])
            ->assertNotFound();

        $this->assertSame($originalStatus, $theirRma->fresh()->status);
        $this->assertNull($theirRma->fresh()->vendor_response);
    }

    public function test_a_vendor_can_still_decide_their_own_rma_request(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myRma] = $this->makeVendorWithRmaRequest();

        $this->actingAs($mine)
            ->post(route('vendor.rma.updateStatus', $myRma), [
                'status' => 'approved',
                'response' => 'Approved, refund on the way.',
            ])
            ->assertRedirect();

        $this->assertSame('approved', $myRma->fresh()->status);
    }

    public function test_a_vendor_can_view_their_own_rma_request(): void
    {
        // The vendor RMA detail view (resources/views/vendor/rma/show.blade.php)
        // used to not exist, so this 500'd for every vendor opening an RMA
        // request from their list. Now that it's built, exercise it
        // directly rather than only through updateStatus().
        [$mine, , $myRma] = $this->makeVendorWithRmaRequest();

        $this->actingAs($mine)
            ->get(route('vendor.rma.show', $myRma))
            ->assertOk()
            ->assertSee($myRma->rma_number)
            ->assertSee($myRma->description);
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: RmaRequest}
     */
    private function makeVendorWithRmaRequest(): array
    {
        [$user, $vendor] = $this->makeVendor(['vendor_rma_request']);

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

        $orderItem = OrderItem::create([
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

        $rma = RmaRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $customer->id,
            'type' => 'return',
            'reason' => 'wrong_item',
            'description' => 'The item that arrived does not match what was ordered.',
            'status' => 'pending',
        ]);

        return [$user, $vendor, $rma];
    }
}
