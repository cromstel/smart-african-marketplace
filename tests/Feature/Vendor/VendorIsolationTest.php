<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor-owned resources.
 *
 * This platform is single-database multi-vendor: no row-level security, no
 * schema per tenant. Separation is enforced entirely in application code, so a
 * query that forgets its ownership constraint does not produce a display bug --
 * it hands one vendor another vendor's data.
 *
 * These are therefore security tests, not CRUD tests. The shape to copy for any
 * new vendor-scoped route is:
 *
 *     vendor A acts on vendor B's resource  ->  refused, and nothing changes
 *
 * Note the module-access gate: vendor product routes sit behind
 * `module.access:multiple_product_category`, which *redirects* rather than
 * returning 403 when the plan lacks the feature. Without an entitled
 * subscription every assertion here would pass for the wrong reason -- the
 * request never reaches the policy. Both vendors are therefore given an active
 * plan that includes the feature, so what is actually under test is ownership.
 */
class VendorIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_open_another_vendors_product_for_editing(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct] = $this->makeVendorWithProduct();

        $this->actingAs($mine)
            ->get(route('vendor.products.edit', $theirProduct))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_product(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct] = $this->makeVendorWithProduct();
        $originalName = $theirProduct->name;

        $this->actingAs($mine)
            ->put(route('vendor.products.update', $theirProduct), [
                'name' => 'Hijacked',
                'price' => 1,
                'stock_quantity' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(
            $originalName,
            $theirProduct->fresh()->name,
            "A vendor changed another vendor's product. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_delete_another_vendors_product(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct] = $this->makeVendorWithProduct();

        $this->actingAs($mine)
            ->delete(route('vendor.products.destroy', $theirProduct))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $theirProduct->id]);
    }

    public function test_a_vendor_cannot_list_another_vendors_product_variants(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirProduct] = $this->makeVendorWithProduct();

        $this->actingAs($mine)
            ->get(route('vendor.products.variants.index', $theirProduct))
            ->assertForbidden();
    }

    public function test_a_vendor_can_still_edit_their_own_product(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myProduct] = $this->makeVendorWithProduct();

        $this->actingAs($mine)
            ->get(route('vendor.products.edit', $myProduct))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Product}
     */
    private function makeVendorWithProduct(): array
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

        return [$user, $vendor, $product];
    }
}
