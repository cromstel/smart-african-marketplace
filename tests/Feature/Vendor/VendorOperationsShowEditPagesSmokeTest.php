<?php

namespace Tests\Feature\Vendor;

use App\Models\Announcement;
use App\Models\Booking;
use App\Models\BookingResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductEnquiry;
use App\Models\RefundRequest;
use App\Models\Review;
use App\Models\RmaRequest;
use App\Models\Salon;
use App\Models\SalonService;
use App\Models\SalonStylist;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Extends the Phase 4 N+1 audit (see VendorIndexPagesSmokeTest) from vendor
 * index pages to vendor show/edit pages, covering the remaining "operations"
 * controllers: announcements, booking resources, bookings, enquiries,
 * orders, refunds, reviews, RMA requests, the salon family, shipments, and
 * withdrawals. Most of these models already have "can still view/edit their
 * own" positive-path assertions in this suite's cross-tenant isolation
 * tests (AuctionIsolationTest, OrderIsolationTest, etc.) -- this file's
 * distinct value is exercising them together, deliberately, under
 * Model::preventLazyLoading(), as a dedicated smoke-test pass rather than a
 * side effect of an isolation assertion.
 */
class VendorOperationsShowEditPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    /**
     * @dataProvider showEditRoutes
     */
    public function test_vendor_show_edit_page_loads_without_a_server_error(string $routeName, string $fixtureMethod): void
    {
        [$user, $routeParams] = $this->$fixtureMethod();

        $response = $this->actingAs($user)->get(route($routeName, $routeParams));

        $response->assertOk();
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'vendor.announcements.show' => 'announcementFixture',
            'vendor.booking-resources.edit' => 'bookingResourceFixture',
            'vendor.booking-resources.show' => 'bookingResourceFixture',
            'vendor.bookings.show' => 'bookingFixture',
            'vendor.enquiries.show' => 'enquiryFixture',
            'vendor.orders.show' => 'orderFixture',
            'vendor.refunds.show' => 'refundFixture',
            'vendor.reviews.show' => 'reviewFixture',
            'vendor.rma.show' => 'rmaFixture',
            'vendor.salon-services.edit' => 'salonServiceFixture',
            'vendor.salon-stylists.edit' => 'salonStylistFixture',
            'vendor.salon.edit' => 'salonFixture',
            'vendor.shipments.show' => 'shipmentFixture',
            'vendor.withdrawals.show' => 'withdrawalFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function announcementFixture(): array
    {
        [$user] = $this->makeVendor();

        $announcement = Announcement::create([
            'title' => 'Announcement '.uniqid(),
            'content' => 'Fixture announcement body.',
            'type' => 'info',
            'target' => 'all_vendors',
            'is_active' => true,
            'is_pinned' => false,
            'published_at' => null,
            'expires_at' => null,
        ]);

        return [$user, ['announcement' => $announcement]];
    }

    private function bookingResourceFixture(): array
    {
        [$user, $vendor] = $this->makeVendor(['bookings']);

        $resource = BookingResource::create([
            'vendor_id' => $vendor->id,
            'resource_type' => 'equipment',
            'name' => 'Resource '.uniqid(),
            'quantity_total' => 3,
            'quantity_available' => 3,
            'is_active' => true,
            'pricing_type' => 'hourly',
            'hourly_rate' => 15,
        ]);

        return [$user, ['booking_resource' => $resource]];
    }

    private function bookingFixture(): array
    {
        [$user] = $this->makeVendor(['bookings']);
        $customer = User::factory()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        $service = SalonService::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 50,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $user->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);

        return [$user, ['booking' => $booking]];
    }

    private function enquiryFixture(): array
    {
        [$user, , $product] = $this->makeProductFor(['product_enquiry']);

        $enquiry = ProductEnquiry::create([
            'product_id' => $product->id,
            'name' => 'Curious Customer',
            'email' => 'customer'.uniqid().'@example.com',
            'subject' => 'Stock availability',
            'message' => 'Is this still in stock?',
            'status' => 'pending',
        ]);

        return [$user, ['enquiry' => $enquiry]];
    }

    private function orderFixture(): array
    {
        [$user, , , $order] = $this->makeOrderWithItem(['order_management']);

        return [$user, ['order' => $order->id]];
    }

    private function refundFixture(): array
    {
        [$user, , , $order, $customer] = $this->makeOrderWithItem();

        $refund = RefundRequest::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => 'pending',
            'vendor_status' => 'pending',
            'reason' => 'Fixture refund.',
        ]);

        return [$user, ['refund' => $refund]];
    }

    private function reviewFixture(): array
    {
        [$user, , $product] = $this->makeProductFor();
        $reviewer = User::factory()->create(['role' => 'customer']);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'title' => 'Great product',
            'comment' => 'Fixture review body '.uniqid(),
            'is_verified_purchase' => true,
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return [$user, ['review' => $review]];
    }

    private function rmaFixture(): array
    {
        [$user, , , $order, $customer] = $this->makeOrderWithItem(['vendor_rma_request']);
        $orderItem = $order->items()->first();

        $rma = RmaRequest::create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'user_id' => $customer->id,
            'type' => 'return',
            'reason' => 'wrong_item',
            'description' => 'Fixture RMA description.',
            'status' => 'pending',
        ]);

        return [$user, ['rma' => $rma]];
    }

    private function salonServiceFixture(): array
    {
        [$user] = $this->makeVendor();

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        $name = 'Service '.uniqid();
        $service = SalonService::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 40.00,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        return [$user, ['salon_service' => $service]];
    }

    private function salonStylistFixture(): array
    {
        [$user, $salon] = $this->makeSalonFor();

        $stylist = SalonStylist::create([
            'salon_id' => $salon->id,
            'name' => 'Stylist '.uniqid(),
            'is_active' => true,
        ]);

        return [$user, ['salon_stylist' => $stylist]];
    }

    private function salonFixture(): array
    {
        [$user, $salon] = $this->makeSalonFor();

        return [$user, ['salon' => $salon]];
    }

    private function shipmentFixture(): array
    {
        [$user, , , $order] = $this->makeOrderWithItem(['vendor_shipping_status'], orderStatus: 'shipped');

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => 'TRACK-'.strtoupper(uniqid()),
            'carrier' => 'Fixture Carrier',
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);

        return [$user, ['shipment' => $shipment]];
    }

    private function withdrawalFixture(): array
    {
        [$user] = $this->makeVendor();

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

        return [$user, ['withdrawal' => $withdrawal]];
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Product}
     */
    private function makeProductFor(array $featureKeys = []): array
    {
        [$user, $vendor] = $this->makeVendor($featureKeys);

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
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

        return [$user, $vendor, $product];
    }

    /**
     * @return array{0: User, 1: Vendor, 2: Product, 3: Order, 4: User}
     */
    private function makeOrderWithItem(array $featureKeys = [], string $orderStatus = 'delivered'): array
    {
        [$user, $vendor, $product] = $this->makeProductFor($featureKeys);
        $customer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'is_parent' => false,
            'subtotal' => 20.00,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 20.00,
            'payment_status' => 'paid',
            'status' => $orderStatus,
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

        return [$user, $vendor, $product, $order->fresh(), $customer];
    }

    /**
     * @return array{0: User, 1: Salon}
     */
    private function makeSalonFor(): array
    {
        [$user] = $this->makeVendor();

        $name = 'Salon '.uniqid();
        $salon = Salon::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'is_active' => true,
        ]);

        return [$user, $salon];
    }
}
