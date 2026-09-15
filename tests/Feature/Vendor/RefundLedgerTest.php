<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Services\VendorLedgerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * VendorLedgerService::debitRefund() used to only ever debit
 * VendorBalance.available, clamped to zero, and never touched
 * lifetime_earnings. That meant refunding an order before it reached
 * "delivered" -- while its earnings still sat in .pending, not .available --
 * silently did nothing: the vendor kept phantom pending earnings from an
 * order that was refunded, forever, and lifetime_earnings was never
 * reversed by any refund regardless of timing.
 */
class RefundLedgerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_refunding_an_order_before_delivery_reverses_the_pending_balance(): void
    {
        [$vendor, $order] = $this->vendorWithPaidOrder(100.00);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->first();
        $this->assertSame('90.00', $balance->pending, 'Fixture sanity check: 10% commission on 100 should credit 90 to pending.');
        $this->assertSame('0.00', $balance->available);

        app(VendorLedgerService::class)->debitRefund($order, 100.00);

        $balance->refresh();
        $this->assertSame('0.00', $balance->pending, 'A pre-delivery refund must reverse the pending balance, not silently no-op.');
        $this->assertSame('0.00', $balance->available);
        $this->assertSame('0.00', $balance->lifetime_earnings, 'lifetime_earnings must be reversed by a refund.');
    }

    public function test_refunding_an_order_after_delivery_reverses_the_available_balance(): void
    {
        [$vendor, $order] = $this->vendorWithPaidOrder(100.00);
        $order->update(['status' => 'delivered']);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->first()->fresh();
        $this->assertSame('0.00', $balance->pending);
        $this->assertSame('90.00', $balance->available);

        app(VendorLedgerService::class)->debitRefund($order, 100.00);

        $balance->refresh();
        $this->assertSame('0.00', $balance->available);
        $this->assertSame('0.00', $balance->lifetime_earnings);
    }

    public function test_refunding_an_order_that_was_never_paid_is_a_no_op(): void
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'v'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
            'commission_rate' => 10,
        ]);
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => 'Product '.uniqid(), 'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()), 'description' => 'Fixture.',
            'price' => 100, 'stock_quantity' => 10, 'stock_status' => 'in_stock', 'is_active' => true,
        ]);
        $order = Order::create([
            'order_number' => 'RF-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => 100, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => 100,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'card',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'vendor_id' => $vendor->id,
            'product_name' => $product->name, 'product_sku' => $product->sku,
            'quantity' => 1, 'price' => 100, 'subtotal' => 100, 'total' => 100,
        ]);

        app(VendorLedgerService::class)->debitRefund($order, 100.00);

        $this->assertNull(VendorBalance::where('vendor_id', $vendor->id)->first());
        $this->assertDatabaseCount('vendor_ledgers', 0);
    }

    /**
     * @return array{0: Vendor, 1: Order}
     */
    private function vendorWithPaidOrder(float $total): array
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'v'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
            'commission_rate' => 10,
        ]);
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $category->id,
            'name' => 'Product '.uniqid(), 'slug' => 'product-'.uniqid(),
            'sku' => 'SKU-'.strtoupper(uniqid()), 'description' => 'Fixture.',
            'price' => $total, 'stock_quantity' => 10, 'stock_status' => 'in_stock', 'is_active' => true,
        ]);
        $order = Order::create([
            'order_number' => 'RF-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => $total, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => $total,
            'payment_status' => 'pending',
            'status' => 'processing',
            'payment_method' => 'card',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'vendor_id' => $vendor->id,
            'product_name' => $product->name, 'product_sku' => $product->sku,
            'quantity' => 1, 'price' => $total, 'subtotal' => $total, 'total' => $total,
        ]);

        $order->update(['payment_status' => 'paid']);

        return [$vendor->fresh(), $order->fresh()];
    }
}
