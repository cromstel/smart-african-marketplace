<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Regression guard for a bug where submitted variant attributes were
 * silently discarded on both create and update.
 *
 * ProductVariantController read `$request->attributes` to get the
 * submitted `attributes[{i}][name]`/`attributes[{i}][value]` pairs, but
 * `attributes` is a reserved public property on the underlying Symfony
 * Request (a ParameterBag of internal route/request attributes, not
 * form input). Iterating it therefore never saw the submitted data:
 * every variant was created with an empty `attributes` array regardless
 * of what the vendor typed, and the auto-generated SKU (which folds in
 * attribute values) came out malformed (e.g. "TES--9595", a double dash
 * where the attribute-derived segment should be). The fix reads the
 * submission via `$request->input('attributes', [])` instead.
 */
class ProductVariantCreationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_can_create_a_product_variant_with_attributes(): void
    {
        [$vendor, $product] = $this->makeVendorProduct();

        $response = $this->actingAs($vendor)
            ->post(route('vendor.products.variants.store', $product), [
                'attributes' => [
                    ['name' => 'Size', 'value' => 'Large'],
                ],
                'price' => 24.99,
                'stock_quantity' => 10,
            ]);

        $response->assertRedirect(route('vendor.products.variants.index', $product));
        $response->assertSessionHas('success');

        $variant = ProductVariation::where('product_id', $product->id)->firstOrFail();

        $this->assertSame(['Size' => 'Large'], $variant->attributes);
        $this->assertStringNotContainsString('--', $variant->sku, 'SKU should not contain a double dash from an empty attributes segment.');
    }

    public function test_a_vendor_can_update_a_product_variants_attributes(): void
    {
        [$vendor, $product] = $this->makeVendorProduct();

        $variant = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.strtoupper(uniqid()),
            'attributes' => ['Size' => 'Small'],
            'price' => 19.99,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($vendor)
            ->put(route('vendor.products.variants.update', [$product, $variant]), [
                'attributes' => [
                    ['name' => 'Size', 'value' => 'Extra Large'],
                ],
                'price' => 22.99,
                'stock_quantity' => 7,
            ]);

        $response->assertRedirect(route('vendor.products.variants.index', $product));
        $response->assertSessionHas('success');

        $this->assertSame(['Size' => 'Extra Large'], $variant->fresh()->attributes);
    }

    /**
     * @return array{0: User, 1: Product}
     */
    private function makeVendorProduct(): array
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
            'description' => 'Variant creation fixture.',
            'price' => 20.00,
            'stock_quantity' => 5,
            'stock_status' => 'in_stock',
            'is_active' => true,
        ]);

        return [$user, $product];
    }
}
