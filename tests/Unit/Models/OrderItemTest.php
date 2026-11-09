<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

/**
 * @covers \App\Models\OrderItem
 * @covers \App\Models\OrderItem::scopeForVendor
 */
class OrderItemTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_order_items(): void
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
            'name' => 'Product A',
            'slug' => 'product-a-' . uniqid(),
            'sku' => 'SKU-A-' . strtoupper(uniqid()),
            'description' => 'Product A.',
            'price' => 10.00,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'vendor_id' => $vendorB->id,
            'category_id' => $category->id,
            'name' => 'Product B',
            'slug' => 'product-b-' . uniqid(),
            'sku' => 'SKU-B-' . strtoupper(uniqid()),
            'description' => 'Product B.',
            'price' => 20.00,
            'stock_quantity' => 3,
            'is_active' => true,
        ]);

        // Orders must exist before order items (FK constraint)
        $orderA = \App\Models\Order::create([
            'order_number' => 'ORD-A-' . uniqid(),
            'user_id' => $vendorA->user_id,
            'status' => 'pending',
            'total' => 10.00,
            'subtotal' => 10.00,
        ]);

        $orderB = \App\Models\Order::create([
            'order_number' => 'ORD-B-' . uniqid(),
            'user_id' => $vendorB->user_id,
            'status' => 'pending',
            'total' => 40.00,
            'subtotal' => 40.00,
        ]);

        $orderItemA = OrderItem::create([
            'vendor_id' => $vendorA->id,
            'product_id' => $productA->id,
            'product_name' => 'Item A',
            'product_sku' => 'ITEM-A-SKU',
            'quantity' => 1,
            'price' => 10.00,
            'subtotal' => 10.00,
            'tax' => 0.00,
            'total' => 10.00,
            'order_id' => $orderA->id,
        ]);

        OrderItem::create([
            'vendor_id' => $vendorB->id,
            'product_id' => $productB->id,
            'product_name' => 'Item B',
            'product_sku' => 'ITEM-B-SKU',
            'quantity' => 2,
            'price' => 20.00,
            'subtotal' => 40.00,
            'tax' => 0.00,
            'total' => 40.00,
            'order_id' => $orderB->id,
        ]);

        // Act
        $results = OrderItem::forVendor($vendorA->id)->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($orderItemA));
        $this->assertFalse(
            $results->contains(function ($orderItem) use ($vendorB) {
                return $orderItem->vendor_id === $vendorB->id;
            }),
            'OrderItem::forVendor() leaked an order item from another vendor.'
        );
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_order_items(): void
    {
        // Arrange
        [$vendorA] = $this->makeVendor('vendor-empty');

        // Act
        $results = OrderItem::forVendor($vendorA->id)->get();

        // Assert
        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        // Arrange
        $this->makeVendor('vendor-nonexistent');

        // Act
        $results = OrderItem::forVendor(999999)->get();

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
