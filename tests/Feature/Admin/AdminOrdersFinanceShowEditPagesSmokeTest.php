<?php

namespace Tests\Feature\Admin;

use App\Models\Address;
use App\Models\Category;
use App\Models\DataExportRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Payout;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Extends the Phase 4 N+1 audit (see AdminIndexPagesSmokeTest) from admin
 * index pages to admin show/edit pages, covering money-movement records:
 * orders, payouts, refunds, withdrawals, and data export requests. These are
 * all single-record pages that eager-load several relationships at once
 * (Order::show() alone loads user/items/shippingAddress/billingAddress/
 * notes.user/group.orders.items), making them the highest-value targets for
 * this audit outside the product catalog.
 */
class AdminOrdersFinanceShowEditPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider showEditRoutes
     */
    public function test_admin_show_edit_page_loads_without_a_server_error(string $routeName, string $fixtureMethod): void
    {
        $admin = User::factory()->admin()->create();
        $routeParams = $this->$fixtureMethod();

        $response = $this->actingAs($admin)->get(route($routeName, $routeParams));

        $response->assertOk();
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'admin.orders.edit' => 'orderFixture',
            'admin.orders.show' => 'orderFixture',
            'admin.payouts.show' => 'payoutFixture',
            'admin.refunds.show' => 'refundFixture',
            'admin.withdrawals.show' => 'withdrawalFixture',
            'admin.data-exports.show' => 'dataExportRequestFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function orderFixture(): array
    {
        [, $order] = $this->makeOrderWithItems();

        return ['order' => $order];
    }

    private function payoutFixture(): array
    {
        $vendor = $this->makeVendor();

        $payout = Payout::create([
            'vendor_id' => $vendor->id,
            'amount' => 100.00,
            'method' => 'bank',
            'status' => 'pending',
        ]);

        return ['payout' => $payout];
    }

    private function refundFixture(): array
    {
        [, $order, $customer] = $this->makeOrderWithItems();

        $refund = RefundRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => 'pending',
            'vendor_status' => 'pending',
            'reason' => 'Fixture refund.',
        ]);

        return ['refund' => $refund];
    }

    private function withdrawalFixture(): array
    {
        $user = User::factory()->vendor()->create();

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'amount' => 100.00,
            'fee' => 2.00,
            'net_amount' => 98.00,
            'method' => 'bank_transfer',
            'account_details' => [
                'account_name' => 'Fixture Account',
                'account_number' => '00000000',
                'bank_name' => 'Fixture Bank',
            ],
            'status' => 'pending',
        ]);

        return ['withdrawal' => $withdrawal];
    }

    private function dataExportRequestFixture(): array
    {
        $vendor = $this->makeVendor();

        $request = DataExportRequest::create([
            'vendor_id' => $vendor->id,
            'requested_by' => $vendor->user_id,
            'reason' => 'Fixture export request.',
            'status' => DataExportRequest::STATUS_PENDING,
        ]);

        return ['dataExportRequest' => $request];
    }

    // --------------------------------------------------------------- Helpers

    private function makeVendor(): Vendor
    {
        $user = User::factory()->vendor()->create();

        return Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0: Vendor, 1: Order, 2: User}
     */
    private function makeOrderWithItems(): array
    {
        $vendor = $this->makeVendor();

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
            'description' => 'Fixture product.',
            'price' => 20.00,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $customer = User::factory()->create(['role' => 'customer']);

        $shippingAddress = Address::create([
            'user_id' => $customer->id,
            'type' => 'shipping',
            'first_name' => 'Fixture',
            'last_name' => 'Customer',
            'address_line1' => '1 Fixture Street',
            'city' => 'Fixture City',
            'state' => 'Fixture State',
            'postal_code' => '00000',
            'country' => 'Nigeria',
            'phone' => '+15550000001',
        ]);

        $billingAddress = Address::create([
            'user_id' => $customer->id,
            'type' => 'billing',
            'first_name' => 'Fixture',
            'last_name' => 'Customer',
            'address_line1' => '1 Fixture Street',
            'city' => 'Fixture City',
            'state' => 'Fixture State',
            'postal_code' => '00000',
            'country' => 'Nigeria',
            'phone' => '+15550000001',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
            'subtotal' => 20.00,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 20.00,
            'payment_status' => 'paid',
            'status' => 'processing',
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

        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'note' => 'Fixture order note.',
            'note_type' => 'system',
            'is_customer_visible' => true,
        ]);

        return [$vendor, $order->fresh(), $customer];
    }
}
