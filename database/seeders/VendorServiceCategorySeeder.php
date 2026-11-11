<?php

namespace Database\Seeders;

use App\Models\VendorServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Men's Salon Services
            [
                'name' => "Men's Haircut",
                'description' => 'Professional men\'s haircut and styling',
                'category_type' => 'beauty',
                'gender_target' => 'men',
                'icon' => 'cut',
                'sort_order' => 1,
            ],
            [
                'name' => "Men's Shave & Beard Trim",
                'description' => 'Traditional shave and beard grooming',
                'category_type' => 'beauty',
                'gender_target' => 'men',
                'icon' => 'razor',
                'sort_order' => 2,
            ],
            [
                'name' => "Men's Grooming Package",
                'description' => 'Complete men\'s grooming package',
                'category_type' => 'beauty',
                'gender_target' => 'men',
                'icon' => 'sparkles',
                'sort_order' => 3,
            ],

            // Women's Salon Services
            [
                'name' => "Women's Haircut",
                'description' => 'Professional women\'s haircut and styling',
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'scissors',
                'sort_order' => 4,
            ],
            [
                'name' => "Women's Hair Coloring",
                'description' => 'Hair coloring and highlights',
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'palette',
                'sort_order' => 5,
            ],
            [
                'name' => "Women's Hair Treatment",
                'description' => 'Deep conditioning and treatment',
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'droplet',
                'sort_order' => 6,
            ],
            [
                'name' => "Women's Makeup",
                'description' => 'Professional makeup application',
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'lipstick',
                'sort_order' => 7,
            ],
            [
                'name' => "Women's Nail Services",
                'description' => 'Manicure and pedicure',
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'nail-polish',
                'sort_order' => 8,
            ],

            // Automotive - Car Washing Bay
            [
                'name' => 'Exterior Car Wash',
                'description' => 'Full exterior wash and dry',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'car',
                'sort_order' => 9,
            ],
            [
                'name' => 'Interior Car Detailing',
                'description' => 'Interior vacuum and cleaning',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'chair',
                'sort_order' => 10,
            ],
            [
                'name' => 'Car Wax & Polish',
                'description' => 'Protective wax and polish',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'sparkles',
                'sort_order' => 11,
            ],
            [
                'name' => 'Full Car Detailing',
                'description' => 'Complete interior and exterior detailing',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'sparkles',
                'sort_order' => 12,
            ],

            // Automotive - Car Garage
            [
                'name' => 'Oil Change Service',
                'description' => 'Engine oil change and filter',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'oil-can',
                'sort_order' => 13,
            ],
            [
                'name' => 'Brake Service',
                'description' => 'Brake pad replacement and service',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'stop-circle',
                'sort_order' => 14,
            ],
            [
                'name' => 'AC Service & Repair',
                'description' => 'Air conditioning service and repair',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'snowflake',
                'sort_order' => 15,
            ],
            [
                'name' => 'Engine Diagnostics',
                'description' => 'Computer engine diagnostics',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'microchip',
                'sort_order' => 16,
            ],

            // Painters
            [
                'name' => 'Interior Painting',
                'description' => 'Residential interior painting',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'paint-brush',
                'sort_order' => 17,
            ],
            [
                'name' => 'Exterior Painting',
                'description' => 'Building exterior painting',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'building',
                'sort_order' => 18,
            ],

            // Electricians
            [
                'name' => 'Home Electrical Wiring',
                'description' => 'Residential electrical wiring and repairs',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'zap',
                'sort_order' => 19,
            ],
            [
                'name' => 'Commercial Electrical',
                'description' => 'Commercial building electrical systems',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'zap',
                'sort_order' => 20,
            ],

            // Mechanics
            [
                'name' => 'General Vehicle Maintenance',
                'description' => 'Routine vehicle maintenance',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'wrench',
                'sort_order' => 21,
            ],
            [
                'name' => 'Fleet Maintenance',
                'description' => 'Commercial fleet vehicle maintenance',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'truck',
                'sort_order' => 22,
            ],

            // Facility Management
            [
                'name' => 'Building Cleaning',
                'description' => 'Commercial building cleaning services',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'cleaning-set',
                'sort_order' => 23,
            ],
            [
                'name' => 'Facility Maintenance',
                'description' => 'Ongoing facility operations and maintenance',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'wrench',
                'sort_order' => 24,
            ],

            // Construction
            [
                'name' => 'Residential Construction',
                'description' => 'Home building and renovation',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'home',
                'sort_order' => 25,
            ],
            [
                'name' => 'Commercial Construction',
                'description' => 'Commercial building development',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'building-2',
                'sort_order' => 26,
            ],

            // Home Assistance
            [
                'name' => 'Home Cleaning',
                'description' => 'Residential cleaning services',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'cleaning-set',
                'sort_order' => 27,
            ],
            [
                'name' => 'Caregiving Services',
                'description' => 'Elderly and child caregiving',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'heart',
                'sort_order' => 28,
            ],
            [
                'name' => 'Home Cooking',
                'description' => 'Meal preparation and cooking',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'utensils',
                'sort_order' => 29,
            ],
        ];

        foreach ($categories as $category) {
            VendorServiceCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'category_type' => $category['category_type'],
                    'gender_target' => $category['gender_target'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
