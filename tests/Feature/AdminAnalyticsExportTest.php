<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Admin\AnalyticsController::exportOrders()/exportRevenue() referenced
 * $order->total_amount / SUM(total_amount) -- the column name Booking uses
 * for the same concept, not Order's actual `total` column. exportOrders
 * silently exported a blank "Total" for every order (total_amount doesn't
 * exist on Order, so the attribute access just returned null); exportRevenue
 * used total_amount inside a raw SQL SUM(), which doesn't silently return
 * null -- it throws "SQLSTATE[42S22]: Column not found: 1054 Unknown column
 * 'total_amount'", so the whole "Export Revenue (CSV)" button 500'd for
 * every admin, every time. Reproduced live via the actual button before
 * fixing. These tests lock in the fix and assert the real total value
 * makes it into the CSV, not just that the response is 200.
 */
class AdminAnalyticsExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_export_orders_csv_includes_the_correct_order_total(): void
    {
        $admin = User::factory()->admin()->create();
        $this->makePaidOrderFor(User::factory()->create(), 40);

        $response = $this->actingAs($admin)
            ->get(route('admin.analytics.export', ['type' => 'orders']))
            ->assertOk();

        $csv = $response->streamedContent();
        $this->assertStringContainsString('40', $csv);
        $this->assertStringNotContainsString("\n,", $csv, 'Total column rendered blank -- total_amount does not exist on Order.');
    }

    public function test_export_revenue_csv_no_longer_500s_and_sums_the_real_total(): void
    {
        $admin = User::factory()->admin()->create();
        $this->makePaidOrderFor(User::factory()->create(), 75);

        $response = $this->actingAs($admin)
            ->get(route('admin.analytics.export', ['type' => 'revenue']))
            ->assertOk();

        $this->assertStringContainsString('75', $response->streamedContent());
    }

    public function test_export_products_csv_works(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.analytics.export', ['type' => 'products']))
            ->assertOk();
    }

    private function makePaidOrderFor(User $customer, float $total): Order
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
            'price' => $total,
            'stock_quantity' => 10,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'AN-'.uniqid(),
            'user_id' => $customer->id,
            'subtotal' => $total, 'tax' => 0, 'shipping_cost' => 0, 'discount' => 0, 'total' => $total,
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
            'price' => $total,
            'subtotal' => $total,
            'total' => $total,
        ]);

        return $order->fresh();
    }
}
