<?php

namespace Tests\Unit\Services;

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
 * VendorLedgerService is the source of truth for what a vendor is owed. An
 * arithmetic mistake here is a financial one -- it either overstates what a
 * vendor can withdraw, or quietly shorts them. These tests exercise the
 * commission math and the pending -> available -> refunded lifecycle
 * directly against the ledger and balance tables, independent of whatever
 * UI or controller happens to call the service.
 */
class VendorLedgerServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_credit_pending_applies_the_vendors_commission_rate(): void
    {
        $vendor = $this->makeVendor(commissionRate: 20);
        $order = $this->makeOrderWithItem($vendor, total: 100);

        app(VendorLedgerService::class)->creditPending($order);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('80.00', $balance->pending);
        $this->assertSame('0.00', $balance->available);
        $this->assertSame('80.00', $balance->lifetime_earnings);

        $this->assertDatabaseHas('vendor_ledgers', [
            'vendor_id' => $vendor->id,
            'order_id' => $order->id,
            'type' => 'credit_pending',
            'amount' => '80.00',
        ]);
    }

    public function test_credit_pending_uses_the_vendors_table_default_commission_rate_when_unset(): void
    {
        // vendors.commission_rate is NOT NULL with a schema default of 15.00 --
        // a vendor created without specifying one still gets a real rate applied,
        // it just comes from the column default rather than being truly absent.
        $vendor = $this->makeVendor(commissionRate: null);

        $this->assertSame('15.00', $vendor->fresh()->commission_rate);

        $order = $this->makeOrderWithItem($vendor, total: 100);

        app(VendorLedgerService::class)->creditPending($order);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('85.00', $balance->pending);
    }

    public function test_release_to_available_moves_the_exact_pending_amount(): void
    {
        $vendor = $this->makeVendor(commissionRate: 10);
        $order = $this->makeOrderWithItem($vendor, total: 100);
        $service = app(VendorLedgerService::class);

        $service->creditPending($order);
        $service->releaseToAvailable($order);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('0.00', $balance->pending);
        $this->assertSame('90.00', $balance->available);
        // Lifetime earnings reflect what was ever earned, not what is
        // currently withdrawable -- releasing pending to available must not
        // count the same sale twice.
        $this->assertSame('90.00', $balance->lifetime_earnings);
    }

    public function test_releasing_more_than_once_does_not_drive_pending_negative(): void
    {
        $vendor = $this->makeVendor(commissionRate: 0);
        $order = $this->makeOrderWithItem($vendor, total: 50);
        $service = app(VendorLedgerService::class);

        $service->creditPending($order);
        $service->releaseToAvailable($order);
        $service->releaseToAvailable($order); // simulates a duplicated event/webhook

        $balance = VendorBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('0.00', $balance->pending, 'Pending balance went negative on a duplicated release.');
        $this->assertSame('100.00', $balance->available, 'A duplicated release paid the vendor for the same order twice.');
    }

    public function test_a_multi_vendor_order_credits_each_vendor_only_for_their_own_items(): void
    {
        $vendorA = $this->makeVendor(commissionRate: 0);
        $vendorB = $this->makeVendor(commissionRate: 0);

        $order = Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => 150,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 150,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->makeItem($order, $vendorA, total: 100);
        $this->makeItem($order, $vendorB, total: 50);
        $order->load('items');

        app(VendorLedgerService::class)->creditPending($order);

        $this->assertSame('100.00', VendorBalance::where('vendor_id', $vendorA->id)->firstOrFail()->pending);
        $this->assertSame('50.00', VendorBalance::where('vendor_id', $vendorB->id)->firstOrFail()->pending);
    }

    public function test_debit_refund_splits_proportionally_across_vendors_on_a_shared_order(): void
    {
        $vendorA = $this->makeVendor(commissionRate: 0);
        $vendorB = $this->makeVendor(commissionRate: 0);

        $order = Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => 150,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 150,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->makeItem($order, $vendorA, total: 100); // 2/3 of the order
        $this->makeItem($order, $vendorB, total: 50);  // 1/3 of the order
        $order->load('items');

        $service = app(VendorLedgerService::class);
        $service->creditPending($order);
        $service->releaseToAvailable($order);

        // A $30 refund should be split 2:1 in proportion to each vendor's share.
        $service->debitRefund($order, 30);

        $this->assertSame('80.00', VendorBalance::where('vendor_id', $vendorA->id)->firstOrFail()->available); // 100 - 20
        $this->assertSame('40.00', VendorBalance::where('vendor_id', $vendorB->id)->firstOrFail()->available); // 50 - 10
    }

    public function test_debit_refund_never_drives_available_balance_negative(): void
    {
        $vendor = $this->makeVendor(commissionRate: 0);
        $order = $this->makeOrderWithItem($vendor, total: 50);
        $service = app(VendorLedgerService::class);

        $service->creditPending($order);
        $service->releaseToAvailable($order);

        // Refund more than the vendor's share of this order (e.g. the vendor
        // already withdrew part of it elsewhere) -- the balance must floor
        // at zero rather than go negative.
        $service->debitRefund($order, 500);

        $balance = VendorBalance::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertSame('0.00', $balance->available);
    }

    // --------------------------------------------------------------- Helpers

    private function makeVendor(?float $commissionRate): Vendor
    {
        $user = User::factory()->vendor()->create();

        $attributes = [
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ];

        if ($commissionRate !== null) {
            $attributes['commission_rate'] = $commissionRate;
        }

        return Vendor::create($attributes);
    }

    private function makeOrderWithItem(Vendor $vendor, float $total): Order
    {
        $order = Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => User::factory()->create()->id,
            'subtotal' => $total,
            'tax' => 0,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->makeItem($order, $vendor, $total);

        return $order->fresh(['items']);
    }

    private function makeItem(Order $order, Vendor $vendor, float $total): OrderItem
    {
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
            'description' => 'Ledger test fixture.',
            'price' => $total,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => $total,
            'subtotal' => $total,
            'tax' => 0,
            'total' => $total,
        ]);
    }
}
