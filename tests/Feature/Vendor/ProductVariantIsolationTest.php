<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor product variants.
 *
 * ProductVariantController authorizes every action with
 * `$this->authorize('update', $product)`, which resolves to
 * ProductPolicy::update() (`$product->vendor_id === $user->vendor->id`
 * for a vendor). A denied Gate check throws an AuthorizationException,
 * which Laravel's exception handler renders as 403 -- a third mechanism
 * for the same isolation guarantee, alongside the manual abort(403) and
 * findOrFail(404) patterns covered elsewhere in this suite. Routes sit
 * behind module.access:multiple_product_category, which
 * MakesIsolatedVendors grants by default.
 */
class ProductVariantIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_product_variant(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct, $theirVariant] = $this->makeVendorWithVariant();

        $this->actingAs($mine)
            ->get(route('vendor.products.variants.edit', [$theirProduct, $theirVariant]))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_product_variant(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct, $theirVariant] = $this->makeVendorWithVariant();
        $originalPrice = $theirVariant->price;

        $this->actingAs($mine)
            ->put(route('vendor.products.variants.update', [$theirProduct, $theirVariant]), [
                'attributes' => [['name' => 'Color', 'value' => 'Hijacked']],
                'price' => 999,
                'stock_quantity' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(
            (string) $originalPrice,
            (string) $theirVariant->fresh()->price,
            "A vendor updated another vendor's product variant. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_product_variant(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct, $theirVariant] = $this->makeVendorWithVariant();

        $this->actingAs($mine)
            ->delete(route('vendor.products.variants.destroy', [$theirProduct, $theirVariant]))
            ->assertForbidden();

        $this->assertDatabaseHas('product_variations', ['id' => $theirVariant->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_product_variant(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myProduct, $myVariant] = $this->makeVendorWithVariant();

        $this->actingAs($mine)
            ->get(route('vendor.products.variants.edit', [$myProduct, $myVariant]))
            ->assertOk();
    }

    /**
     * Regression guard for a `Call to undefined method
     * App\Models\Product::variations()` fatal that broke this route for
     * every vendor: ProductVariantController::index() called the
     * relation by the wrong name (`variations()` instead of the model's
     * actual `variants()` HasMany), so `/vendor/products/{id}/variants`
     * 500'd unconditionally.
     */
    public function test_a_vendor_can_view_their_product_variants_index(): void
    {
        [$mine, , $myProduct] = $this->makeVendorWithVariant();

        $response = $this->actingAs($mine)
            ->get(route('vendor.products.variants.index', $myProduct));

        $response->assertOk();
        $response->assertDontSee('BadMethodCallException');
        $response->assertDontSee('Call to undefined method');
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Product, 3: ProductVariation}
     */
    private function makeVendorWithVariant(): array
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

        $variant = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.strtoupper(uniqid()),
            'attributes' => ['Color' => 'Red'],
            'price' => 25.00,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        return [$user, $vendor, $product, $variant];
    }
}
