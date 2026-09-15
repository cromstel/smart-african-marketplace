<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductEnquiry;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor product enquiries.
 *
 * Same bug class as OrderController: `$vendorId = auth()->id();` was
 * compared against `products.vendor_id` (a `vendors.id`), so a vendor could
 * read, respond to, and close another vendor's customer enquiries.
 */
class ProductEnquiryIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_enquiry(): void
    {
        [$mine] = $this->makeVendor(['product_enquiry']);
        [, , $theirEnquiry] = $this->makeVendorWithEnquiry();

        $this->actingAs($mine)
            ->get(route('vendor.enquiries.show', $theirEnquiry))
            ->assertNotFound();
    }

    public function test_a_vendor_cannot_respond_to_another_vendors_enquiry(): void
    {
        [$mine] = $this->makeVendor(['product_enquiry']);
        [, , $theirEnquiry] = $this->makeVendorWithEnquiry();

        $this->actingAs($mine)
            ->post(route('vendor.enquiries.respond', $theirEnquiry), ['response' => 'Hijacked response'])
            ->assertNotFound();

        $this->assertNull($theirEnquiry->fresh()->response);
    }

    public function test_a_vendor_cannot_close_another_vendors_enquiry(): void
    {
        [$mine] = $this->makeVendor(['product_enquiry']);
        [, , $theirEnquiry] = $this->makeVendorWithEnquiry();
        $originalStatus = $theirEnquiry->status;

        $this->actingAs($mine)
            ->post(route('vendor.enquiries.close', $theirEnquiry))
            ->assertNotFound();

        $this->assertSame($originalStatus, $theirEnquiry->fresh()->status);
    }

    public function test_a_vendor_can_still_respond_to_their_own_enquiry(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myEnquiry] = $this->makeVendorWithEnquiry();

        $this->actingAs($mine)
            ->post(route('vendor.enquiries.respond', $myEnquiry), ['response' => 'Yes, in stock!'])
            ->assertRedirect();

        $this->assertSame('Yes, in stock!', $myEnquiry->fresh()->response);
    }

    public function test_a_vendor_can_view_their_own_enquiry(): void
    {
        // The vendor enquiry detail view
        // (resources/views/vendor/enquiries/show.blade.php) used to not
        // exist, so this 500'd for every vendor clicking "Respond"/"View"
        // from their enquiries list. Now that it's built, exercise it
        // directly rather than only through respond()/close().
        [$mine, , $myEnquiry] = $this->makeVendorWithEnquiry();

        $this->actingAs($mine)
            ->get(route('vendor.enquiries.show', $myEnquiry))
            ->assertOk()
            ->assertSee($myEnquiry->subject)
            ->assertSee($myEnquiry->message);
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: ProductEnquiry}
     */
    private function makeVendorWithEnquiry(): array
    {
        [$user, $vendor] = $this->makeVendor(['product_enquiry']);

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

        $enquiry = ProductEnquiry::create([
            'product_id' => $product->id,
            'name' => 'Curious Customer',
            'email' => 'customer'.uniqid().'@example.com',
            'subject' => 'Stock availability',
            'message' => 'Is this still in stock?',
            'status' => 'pending',
        ]);

        return [$user, $vendor, $enquiry];
    }
}
