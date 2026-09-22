<?php

namespace Tests\Feature\Vendor;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\MediaLibrary;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Extends the Phase 4 N+1 audit (see VendorIndexPagesSmokeTest) from vendor
 * index pages to vendor show/edit pages, covering the product catalog family:
 * products, attributes, auctions, brands, coupons, and media.
 *
 * vendor.products.edit surfaced a real bug alongside this file: the edit
 * view renders both $product->images (existing-images gallery) and
 * $product->tags (pre-selected tag checkboxes) directly, but the controller
 * never eager-loaded either, so any product with an image or a tag threw a
 * LazyLoadingViolationException. Fixed in Vendor\ProductController::edit()
 * by loading ['images', 'tags'] before rendering; the fixture below
 * deliberately gives the product both so this file would catch a
 * regression.
 *
 * vendor.brands.edit always redirects (brand management is admin-only; see
 * Vendor\BrandController::edit()) rather than rendering a page, so it's
 * asserted as a redirect, not a 200 -- this is existing, deliberate
 * behaviour, not a bug this file found.
 */
class VendorCatalogShowEditPagesSmokeTest extends TestCase
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

        $response->assertStatus($this->expectedStatusFor($routeName));
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'vendor.attributes.edit' => 'attributeFixture',
            'vendor.auctions.edit' => 'auctionEditFixture',
            'vendor.auctions.show' => 'auctionShowFixture',
            'vendor.brands.edit' => 'brandFixture',
            'vendor.coupons.edit' => 'couponFixture',
            'vendor.media.edit' => 'mediaFixture',
            'vendor.media.show' => 'mediaFixture',
            'vendor.products.edit' => 'productFixture',
            'vendor.products.variants.edit' => 'productVariantFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    private function expectedStatusFor(string $routeName): int
    {
        // Brands are created by vendors but can only be edited by admins --
        // Vendor\BrandController::edit() always redirects back with an
        // error, regardless of ownership. See the class docblock.
        return match ($routeName) {
            'vendor.brands.edit' => 302,
            default => 200,
        };
    }

    // --------------------------------------------------------------- Fixtures

    private function attributeFixture(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $attribute = ProductAttribute::create([
            'vendor_id' => $vendor->id,
            'name' => 'Attribute '.uniqid(),
            'slug' => 'attribute-'.uniqid(),
            'type' => 'text',
            'is_filterable' => true,
            'is_visible' => true,
            'enable_archives' => false,
            'default_sort_order' => 'custom_ordering',
            'sort_order' => 0,
        ]);

        return [$user, ['attribute' => $attribute]];
    }

    /**
     * AuctionPolicy::update() only allows a vendor to edit their own
     * draft/scheduled auctions -- not active/ended ones, since the terms
     * can't change once bidding has started. A separate fixture from
     * auctionShowFixture() so this route is exercised under a status the
     * policy actually permits, rather than getting a 403 for the wrong
     * reason.
     */
    private function auctionEditFixture(): array
    {
        [$user, $vendor] = $this->makeVendor(['auctions']);

        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 100,
            'bid_increment' => 10,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(3),
            'status' => 'scheduled',
            'type' => 'english',
        ]);

        return [$user, ['auction' => $auction]];
    }

    private function auctionShowFixture(): array
    {
        [$user, $vendor] = $this->makeVendor(['auctions']);
        $bidder = User::factory()->create();

        $auction = Auction::create([
            'vendor_id' => $vendor->id,
            'title' => 'Auction '.uniqid(),
            'starting_price' => 100,
            'current_price' => 120,
            'bid_increment' => 10,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(3),
            'status' => 'active',
            'type' => 'english',
            // Set so show()'s `winner` eager load actually has a row to
            // resolve -- see the identical note in
            // AdminOperationsShowEditPagesSmokeTest::auctionFixture().
            'winner_id' => $bidder->id,
        ]);

        AuctionBid::create([
            'auction_id' => $auction->id,
            'user_id' => $bidder->id,
            'amount' => 120,
            'is_winning' => true,
        ]);

        return [$user, ['auction' => $auction]];
    }

    private function brandFixture(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $brand = Brand::create([
            'vendor_id' => $vendor->id,
            'name' => 'Brand '.uniqid(),
            'slug' => 'brand-'.uniqid(),
            'is_active' => true,
        ]);

        return [$user, ['brand' => $brand]];
    }

    private function couponFixture(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => 'CODE'.strtoupper(uniqid()),
            'type' => 'fixed',
            'value' => 10.00,
            'usage_limit' => 100,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return [$user, ['coupon' => $coupon]];
    }

    private function mediaFixture(): array
    {
        [$user, $vendor] = $this->makeVendor(['vendor_media_library']);

        $media = MediaLibrary::create([
            'title' => 'Fixture image',
            'file_name' => 'fixture-'.uniqid().'.jpg',
            'original_name' => 'fixture.jpg',
            'file_path' => 'media/vendors/'.$vendor->id.'/fixture-'.uniqid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 12345,
            'type' => 'image',
            'uploaded_by_type' => 'App\\Models\\User',
            'uploaded_by_id' => $user->id,
            'vendor_id' => $vendor->id,
        ]);

        return [$user, ['media' => $media]];
    }

    private function productFixture(): array
    {
        [$user, $product] = $this->makeProductWithImageAndTag();

        return [$user, ['product' => $product]];
    }

    private function productVariantFixture(): array
    {
        [$user, $product] = $this->makeProductWithImageAndTag();

        $variant = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.strtoupper(uniqid()),
            'attributes' => ['Color' => 'Red'],
            'price' => 25.00,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        return [$user, ['product' => $product, 'variant' => $variant]];
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Product}
     */
    private function makeProductWithImageAndTag(): array
    {
        [$user, $vendor] = $this->makeVendor();

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

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/'.$product->id.'/fixture.jpg',
            'alt_text' => $product->name,
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $tag = ProductTag::create([
            'name' => 'Tag '.uniqid(),
            'slug' => 'tag-'.uniqid(),
        ]);
        $product->tags()->attach($tag);

        return [$user, $product->fresh()];
    }
}
