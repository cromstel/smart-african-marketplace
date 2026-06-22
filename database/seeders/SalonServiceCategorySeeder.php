<?php

namespace Database\Seeders;

use App\Models\SalonServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalonServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Braiding', 'icon' => '?????', 'description' => 'Protective braiding styles and maintenance', 'sort_order' => 1],
            ['name' => 'Natural Hair Care', 'icon' => '??', 'description' => 'Natural hair treatments and styling', 'sort_order' => 2],
            ['name' => 'Weaves & Extensions', 'icon' => '?', 'description' => 'Weave, wig, and extension services', 'sort_order' => 3],
            ['name' => 'Locs & Dreadlocks', 'icon' => '??', 'description' => 'Loc installation and maintenance', 'sort_order' => 4],
            ['name' => 'Nail Services', 'icon' => '??', 'description' => 'Manicure, pedicure, and nail art', 'sort_order' => 5],
            ['name' => 'Makeup', 'icon' => '??', 'description' => 'Makeup application and tutorials', 'sort_order' => 6],
            ['name' => 'Barbering', 'icon' => '??', 'description' => 'Haircuts, trims, and grooming', 'sort_order' => 7],
            ['name' => 'Spa & Skincare', 'icon' => '?????', 'description' => 'Facials, skincare, and spa treatments', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            SalonServiceCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
