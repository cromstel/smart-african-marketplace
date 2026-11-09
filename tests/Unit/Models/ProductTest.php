<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

/**
 * @covers \App\Models\Product
 * @covers \App\Models\Product::scopeForVendor
 */
class ProductTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_products(): void
    {
        // Arrange
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        $category = Category::create([
            'name' => 'Test Category ' . uniqid(),
            'slug' => 'test-category-' . uniqid(),
        ]);

        $productA = Product::create([
            'vendor_id' => $vendorA->id,
            'category_id' => $category->id,
            'name' => 'Vendor A Product',
            'slug' => 'vendor-a-product-' . uniqid(),
            'sku' => 'SKU-A-' . strtoupper(uniqid()),
            'description' => 'Isolation test product A.',
            'price' => 10.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'vendor_id' => $vendorB->id,
            'category_id' => $category->id,
            'name' => 'Vendor B Product',
            'slug' => 'vendor-b-product-' . uniqid(),
            'sku' => 'SKU-B-' . strtoupper(uniqid()),
            'description' => 'Isolation test product B.',
            'price' => 20.00,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        // Act
        $results = Product::forVendor($vendorA->id)->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($productA));
        $this->assertFalse(
            $results->contains(function ($product) use ($vendorB) {
                return $product->vendor_id === $vendorB->id;
            }),
            'Product::forVendor() leaked a product from another vendor.'
        );
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_products(): void
    {
        // Arrange
        [$vendorA] = $this->makeVendor('vendor-empty');

        // Act
        $results = Product::forVendor($vendorA->id)->get();

        // Assert
        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        // Arrange
        $this->makeVendor('vendor-nonexistent');

        // Act
        $results = Product::forVendor(999999)->get();

        // Assert
        $this->assertCount(0, $results);
    }

    /**
     * @param string $slugSuffix
     * @return array{0: Vendor}
     */
    private function makeVendor(string $slugSuffix): array
    {
        $user = User::factory()->create();

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Test Vendor ' . $slugSuffix,
            'slug' => 'vendor-' . $slugSuffix . '-' . uniqid(),
            'business_email' => 'vendor-' . $slugSuffix . '-' . uniqid() . '@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        return [$vendor->fresh()];
    }
}
