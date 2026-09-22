<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\ProductVariation;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Extends the Phase 4 N+1 audit (see AdminIndexPagesSmokeTest) from admin
 * index pages to admin show/edit pages, covering the product catalog family:
 * products (highest N+1 risk in the whole admin -- category/vendor/brand/
 * images/variants all loaded on one record), attributes, brands, tags,
 * variants, coupons, and subscription plans.
 *
 * admin.products.edit surfaced a real bug alongside this file: the edit view
 * renders $product->images directly but the controller never eager-loaded
 * it, so every product with at least one image threw a
 * LazyLoadingViolationException. Fixed in ProductController::edit() by
 * loading 'images' before rendering (see the fix's commit for detail); the
 * fixture below deliberately gives the product an image so this file would
 * catch a regression.
 */
class AdminCommerceShowEditPagesSmokeTest extends TestCase
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
            'admin.products.edit' => 'productFixture',
            'admin.products.show' => 'productFixture',
            'admin.products.attributes.edit' => 'productAttributeFixture',
            'admin.products.brands.edit' => 'brandFixture',
            'admin.products.tags.edit' => 'productTagFixture',
            'admin.products.variants.edit' => 'productVariantFixture',
            'admin.coupons.edit' => 'couponFixture',
            'admin.subscriptions.plans.edit' => 'subscriptionPlanFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function productFixture(): array
    {
        [$product] = $this->makeProductWithRelations();

        return ['product' => $product];
    }

    private function productAttributeFixture(): array
    {
        $attribute = ProductAttribute::create([
            'name' => 'Attribute '.uniqid(),
            'slug' => 'attribute-'.uniqid(),
            'type' => 'text',
            'is_filterable' => true,
            'is_visible' => true,
            'enable_archives' => false,
            'default_sort_order' => 'custom_ordering',
            'sort_order' => 0,
        ]);

        return ['attribute' => $attribute];
    }

    private function brandFixture(): array
    {
        $brand = Brand::create([
            'name' => 'Brand '.uniqid(),
            'slug' => 'brand-'.uniqid(),
            'description' => 'Fixture brand.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return ['brand' => $brand];
    }

    private function productTagFixture(): array
    {
        $tag = ProductTag::create([
            'name' => 'Tag '.uniqid(),
            'slug' => 'tag-'.uniqid(),
        ]);

        return ['tag' => $tag];
    }

    private function productVariantFixture(): array
    {
        [$product] = $this->makeProductWithRelations();

        $variant = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.strtoupper(uniqid()),
            'attributes' => ['Color' => 'Red'],
            'price' => 25.00,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        return ['product' => $product, 'variant' => $variant];
    }

    private function couponFixture(): array
    {
        $user = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => 'CODE'.strtoupper(uniqid()),
            'type' => 'fixed',
            'value' => 10.00,
            'usage_limit' => 100,
            'usage_count' => 0,
            'is_active' => true,
        ]);

        return ['coupon' => $coupon];
    }

    private function subscriptionPlanFixture(): array
    {
        $plan = SubscriptionPlan::create([
            'name' => 'Plan '.uniqid(),
            'slug' => 'plan-'.uniqid(),
            'description' => 'Fixture plan.',
            'price' => 29.99,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'trial_days' => 14,
            'feature_keys' => ['multiple_product_category'],
            'max_products' => 100,
            'max_orders' => 100,
            'commission_rate' => 10,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return ['plan' => $plan];
    }

    /**
     * @return array{0: Product}
     */
    private function makeProductWithRelations(): array
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        $brand = Brand::create([
            'name' => 'Brand '.uniqid(),
            'slug' => 'brand-'.uniqid(),
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
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

        ProductVariation::create([
            'product_id' => $product->id,
            'sku' => 'VAR-'.strtoupper(uniqid()),
            'attributes' => ['Size' => 'M'],
            'price' => 22.00,
            'stock_quantity' => 2,
            'is_active' => true,
        ]);

        return [$product->fresh()];
    }
}
