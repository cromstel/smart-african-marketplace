<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor product reviews.
 *
 * ReviewController::show() checks
 * `$review->product->vendor_id !== auth()->user()->vendor->id` and aborts
 * with 403 on an implicitly-bound Review. The route sits behind
 * module.access:multiple_product_category, which MakesIsolatedVendors
 * grants by default.
 */
class ReviewIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_view_another_vendors_review(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirReview] = $this->makeVendorWithReview();

        $this->actingAs($mine)
            ->get(route('vendor.reviews.show', $theirReview))
            ->assertForbidden();
    }

    public function test_a_vendor_can_still_view_their_own_review(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , , $myReview] = $this->makeVendorWithReview();

        $this->actingAs($mine)
            ->get(route('vendor.reviews.show', $myReview))
            ->assertOk()
            ->assertSee($myReview->comment);
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Product, 3: Review}
     */
    private function makeVendorWithReview(): array
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

        $reviewer = User::factory()->create(['role' => 'customer']);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $reviewer->id,
            'rating' => 5,
            'title' => 'Great product',
            'comment' => 'Isolation fixture review body '.uniqid(),
            'is_verified_purchase' => true,
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return [$user, $vendor, $product, $review];
    }
}
