<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $grainsCategory = Category::where('slug', 'grains-flours')
            ->orWhere('name', 'Grains & Flours')
            ->first();
        if ($grainsCategory) {
            $grainsCategory->update([
                'name' => 'Grains & Cereals',
                'slug' => Str::slug('Grains & Cereals'),
                'description' => 'Staple grains and cereals',
                'parent_id' => null,
                'type' => 'food',
                'sort_order' => 1,
                'is_active' => true,
            ]);
        } else {
            $grainsCategory = Category::updateOrCreate(
                ['slug' => 'grains-cereals'],
                [
                    'name' => 'Grains & Cereals',
                    'description' => 'Staple grains and cereals',
                    'parent_id' => null,
                    'type' => 'food',
                    'sort_order' => 1,
                    'is_active' => true,
                ]
            );
        }

        $spicesCategory = Category::where('slug', 'spices-seasonings')
            ->orWhere('name', 'Spices & Seasonings')
            ->first();
        if ($spicesCategory) {
            $spicesCategory->update([
                'name' => 'Spices & Seasonings',
                'slug' => Str::slug('Spices & Seasonings'),
                'description' => 'Authentic spice blends and seasonings',
                'parent_id' => null,
                'type' => 'food',
                'sort_order' => 2,
                'is_active' => true,
            ]);
        } else {
            $spicesCategory = Category::updateOrCreate(
                ['slug' => 'spices-seasonings'],
                [
                    'name' => 'Spices & Seasonings',
                    'description' => 'Authentic spice blends and seasonings',
                    'parent_id' => null,
                    'type' => 'food',
                    'sort_order' => 2,
                    'is_active' => true,
                ]
            );
        }

        $readyMealsCategory = Category::where('slug', 'ready-meals')
            ->orWhere('name', 'Ready Meals')
            ->first();
        if ($readyMealsCategory) {
            $readyMealsCategory->update([
                'name' => 'Ready Meals',
                'slug' => Str::slug('Ready Meals'),
                'description' => 'Ready to eat meals and quick dishes',
                'parent_id' => null,
                'type' => 'food',
                'sort_order' => 3,
                'is_active' => true,
            ]);
        } else {
            $readyMealsCategory = Category::updateOrCreate(
                ['slug' => 'ready-meals'],
                [
                    'name' => 'Ready Meals',
                    'description' => 'Ready to eat meals and quick dishes',
                    'parent_id' => null,
                    'type' => 'food',
                    'sort_order' => 3,
                    'is_active' => true,
                ]
            );
        }

        $fashionCategory = Category::where('slug', 'african-clothing')
            ->orWhere('name', 'African Clothing')
            ->first();
        if ($fashionCategory) {
            $fashionCategory->update([
                'name' => 'Fashion & Apparel',
                'slug' => Str::slug('Fashion & Apparel'),
                'description' => 'Traditional and modern African fashion',
                'parent_id' => null,
                'type' => 'clothing',
                'sort_order' => 4,
                'is_active' => true,
            ]);
        } else {
            $fashionCategory = Category::updateOrCreate(
                ['slug' => 'fashion-apparel'],
                [
                    'name' => 'Fashion & Apparel',
                    'description' => 'Traditional and modern African fashion',
                    'parent_id' => null,
                    'type' => 'clothing',
                    'sort_order' => 4,
                    'is_active' => true,
                ]
            );
        }

        $foodCategory = Category::updateOrCreate(
            ['slug' => 'african-food-products'],
            [
                'name' => 'African Food Products',
                'description' => 'Authentic African food products from across the continent',
                'type' => 'food',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        $foodSubcategories = [
            'Sauces & Condiments' => ['Palm oil', 'Groundnut paste', 'Shito sauce', 'Piri piri sauce'],
            'Fresh & Frozen' => ['Plantains', 'Cassava', 'Yams', 'African vegetables'],
        ];

        foreach ($foodSubcategories as $name => $keywords) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Various '.$name.' products',
                    'parent_id' => $foodCategory->id,
                    'type' => 'food',
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
        Category::updateOrCreate(
            ['slug' => 'snacks-beverages'],
            [
                'name' => 'Snacks & Beverages',
                'description' => 'Quick bites and refreshing drinks',
                'parent_id' => $readyMealsCategory->id,
                'type' => 'food',
                'sort_order' => 0,
                'is_active' => true,
            ]
        );

        $clothingSubcategories = [
            "Men's Wear" => ['Dashiki', 'Agbada', 'Kaftan', 'African shirts'],
            "Women's Wear" => ['Ankara dresses', 'Kente dresses', 'African skirts', 'Wraps'],
            'Accessories' => ['Head wraps', 'African jewelry', 'Bags', 'Shoes'],
            'Fabrics' => ['Ankara fabric', 'Kente cloth', 'Mudcloth', 'Aso oke'],
        ];

        foreach ($clothingSubcategories as $name => $keywords) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Various '.$name.' products',
                    'parent_id' => $fashionCategory->id,
                    'type' => 'clothing',
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }

        $salonCategory = Category::updateOrCreate(
            ['slug' => 'salon-services'],
            [
                'name' => 'Salon Services',
                'description' => 'Professional African hair and beauty services',
                'type' => 'salon',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $salonSubcategories = [
            'Braiding' => ['Box braids', 'Cornrows', 'Twist braids', 'Ghana weaving'],
            'Natural Hair Care' => ['Loc maintenance', 'Twist outs', 'Wash and go', 'Hot oil treatment'],
            'Weaves & Extensions' => ['Sew-in weave', 'Closure installation', 'Wig installation'],
            'Nail Services' => ['Manicure', 'Pedicure', 'Nail art', 'Acrylic nails'],
        ];

        foreach ($salonSubcategories as $name => $keywords) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Various '.$name.' services',
                    'parent_id' => $salonCategory->id,
                    'type' => 'salon',
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
