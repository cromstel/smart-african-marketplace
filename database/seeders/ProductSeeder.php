<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::where('status', 'approved')->get();
        $foodCategories = Category::where('type', 'food')->whereNotNull('parent_id')->get();
        $clothingCategories = Category::where('type', 'clothing')->whereNotNull('parent_id')->get();

        if ($vendors->isEmpty() || $foodCategories->isEmpty()) {
            return;
        }

        $foodProducts = [
            [
                'name' => 'Premium Fufu Flour',
                'description' => 'High-quality fufu flour made from cassava. Perfect for making authentic African fufu. Simply mix with hot water and enjoy with your favorite soup.',
                'short_description' => 'Authentic cassava-based fufu flour',
                'price' => 12.99,
                'compare_price' => 15.99,
                'stock_quantity' => 100,
                'origin_country' => 'Nigeria',
                'weight' => 2.0,
            ],
            [
                'name' => 'Suya Spice Blend',
                'description' => 'Traditional Nigerian suya spice blend with groundnuts, ginger, and peppers. Perfect for grilling meats and vegetables.',
                'short_description' => 'Authentic Nigerian suya spice',
                'price' => 8.99,
                'compare_price' => 10.99,
                'stock_quantity' => 150,
                'origin_country' => 'Nigeria',
                'weight' => 0.5,
            ],
            [
                'name' => 'Red Palm Oil',
                'description' => 'Pure red palm oil extracted from fresh palm fruits. Rich in vitamins and perfect for cooking authentic African dishes.',
                'short_description' => 'Pure red palm oil',
                'price' => 14.99,
                'compare_price' => 18.99,
                'stock_quantity' => 80,
                'origin_country' => 'Ghana',
                'weight' => 1.5,
            ],
            [
                'name' => 'Hibiscus Tea (Bissap)',
                'description' => 'Dried hibiscus flowers for making refreshing bissap drink. Rich in antioxidants and vitamin C.',
                'short_description' => 'Dried hibiscus flowers for tea',
                'price' => 6.99,
                'stock_quantity' => 200,
                'origin_country' => 'Senegal',
                'weight' => 0.3,
            ],
            [
                'name' => 'Plantain Flour',
                'description' => 'Ground plantain flour perfect for making swallow, baking, or smoothies. Gluten-free and nutritious.',
                'short_description' => 'Gluten-free plantain flour',
                'price' => 11.99,
                'stock_quantity' => 120,
                'origin_country' => 'Uganda',
                'weight' => 1.8,
            ],
        ];

        foreach ($foodProducts as $index => $productData) {
            $category = $foodCategories->random();
            $vendor = $vendors->random();

            Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'sku' => 'FOOD-'.str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'] ?? null,
                'cost_price' => $productData['price'] * 0.6,
                'stock_quantity' => $productData['stock_quantity'],
                'low_stock_threshold' => 10,
                'stock_status' => 'in_stock',
                'weight' => $productData['weight'],
                'origin_country' => $productData['origin_country'],
                'is_featured' => $index < 3,
                'is_active' => true,
                'published_at' => now(),
            ]);
        }

        if ($clothingCategories->isEmpty()) {
            return;
        }

        $clothingProducts = [
            [
                'name' => 'Ankara Dashiki Shirt',
                'description' => 'Handmade dashiki shirt with vibrant Ankara print. Comfortable cotton fabric perfect for casual or formal occasions.',
                'short_description' => 'Vibrant Ankara dashiki shirt',
                'price' => 49.99,
                'compare_price' => 64.99,
                'stock_quantity' => 50,
                'origin_country' => 'Ghana',
            ],
            [
                'name' => 'Kente Dress',
                'description' => 'Beautiful kente cloth dress with traditional patterns. Made from high-quality woven fabric.',
                'short_description' => 'Traditional kente cloth dress',
                'price' => 89.99,
                'compare_price' => 119.99,
                'stock_quantity' => 30,
                'origin_country' => 'Ghana',
            ],
            [
                'name' => 'African Print Head Wrap',
                'description' => 'Colorful African print head wrap. Versatile and stylish accessory for any outfit.',
                'short_description' => 'Colorful African head wrap',
                'price' => 15.99,
                'stock_quantity' => 100,
                'origin_country' => 'Nigeria',
            ],
        ];

        foreach ($clothingProducts as $index => $productData) {
            $category = $clothingCategories->random();
            $vendor = $vendors->random();

            Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => Str::slug($productData['name']),
                'sku' => 'CLOTH-'.str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'] ?? null,
                'cost_price' => $productData['price'] * 0.5,
                'stock_quantity' => $productData['stock_quantity'],
                'low_stock_threshold' => 5,
                'stock_status' => 'in_stock',
                'origin_country' => $productData['origin_country'],
                'is_featured' => $index < 2,
                'is_active' => true,
                'published_at' => now(),
            ]);
        }
    }
}
