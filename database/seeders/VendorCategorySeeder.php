<?php

namespace Database\Seeders;

use App\Models\VendorCategory;
use Illuminate\Database\Seeder;

class VendorCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food & Beverages',
                'slug' => 'food-beverages',
                'description' => 'Restaurants, food vendors, catering services, and beverage suppliers',
                'icon' => '🍽️',
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion & Clothing',
                'slug' => 'fashion-clothing',
                'description' => 'Clothing stores, fashion designers, accessories, and footwear',
                'icon' => '👗',
                'sort_order' => 2,
            ],
            [
                'name' => 'Beauty & Wellness',
                'slug' => 'beauty-wellness',
                'description' => 'Salons, spas, beauty products, cosmetics, and wellness services',
                'icon' => '💄',
                'sort_order' => 3,
            ],
            [
                'name' => 'Arts & Crafts',
                'slug' => 'arts-crafts',
                'description' => 'Handmade items, artwork, sculptures, traditional crafts, and artisanal goods',
                'icon' => '🎨',
                'sort_order' => 4,
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Furniture, home decor, kitchenware, and household items',
                'icon' => '🏠',
                'sort_order' => 5,
            ],
            [
                'name' => 'Electronics & Technology',
                'slug' => 'electronics-technology',
                'description' => 'Mobile phones, computers, accessories, and electronic devices',
                'icon' => '📱',
                'sort_order' => 6,
            ],
            [
                'name' => 'Books & Education',
                'slug' => 'books-education',
                'description' => 'Books, educational materials, stationery, and learning resources',
                'icon' => '📚',
                'sort_order' => 7,
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Sporting goods, fitness equipment, activewear, and wellness products',
                'icon' => '⚽',
                'sort_order' => 8,
            ],
            [
                'name' => 'Health & Personal Care',
                'slug' => 'health-personal-care',
                'description' => 'Healthcare products, supplements, personal hygiene, and medical supplies',
                'icon' => '💊',
                'sort_order' => 9,
            ],
            [
                'name' => 'Jewelry & Accessories',
                'slug' => 'jewelry-accessories',
                'description' => 'Jewelry, watches, bags, belts, and fashion accessories',
                'icon' => '💎',
                'sort_order' => 10,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Professional services, consulting, repairs, and other service-based businesses',
                'icon' => '🔧',
                'sort_order' => 11,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other business categories not listed above',
                'icon' => '🏪',
                'sort_order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            VendorCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
