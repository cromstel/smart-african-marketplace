<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $foodCategory = Category::create([
            'name' => 'African Food Products',
            'slug' => 'african-food-products',
            'description' => 'Authentic African food products from across the continent',
            'type' => 'food',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $foodSubcategories = [
            'Grains & Flours' => ['Fufu flour', 'Cassava flour', 'Yam flour', 'Plantain flour'],
            'Spices & Seasonings' => ['Suya spice', 'Berbere', 'Ras el hanout', 'Peri peri'],
            'Sauces & Condiments' => ['Palm oil', 'Groundnut paste', 'Shito sauce', 'Piri piri sauce'],
            'Snacks & Beverages' => ['Chin chin', 'Puff puff mix', 'Hibiscus tea', 'Baobab powder'],
            'Fresh & Frozen' => ['Plantains', 'Cassava', 'Yams', 'African vegetables'],
        ];

        foreach ($foodSubcategories as $name => $keywords) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Various '.$name.' products',
                'parent_id' => $foodCategory->id,
                'type' => 'food',
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }

        $clothingCategory = Category::create([
            'name' => 'African Clothing',
            'slug' => 'african-clothing',
            'description' => 'Traditional and modern African fashion',
            'type' => 'clothing',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $clothingSubcategories = [
            "Men's Wear" => ['Dashiki', 'Agbada', 'Kaftan', 'African shirts'],
            "Women's Wear" => ['Ankara dresses', 'Kente dresses', 'African skirts', 'Wraps'],
            'Accessories' => ['Head wraps', 'African jewelry', 'Bags', 'Shoes'],
            'Fabrics' => ['Ankara fabric', 'Kente cloth', 'Mudcloth', 'Aso oke'],
        ];

        foreach ($clothingSubcategories as $name => $keywords) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Various '.$name.' products',
                'parent_id' => $clothingCategory->id,
                'type' => 'clothing',
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }

        $salonCategory = Category::create([
            'name' => 'Salon Services',
            'slug' => 'salon-services',
            'description' => 'Professional African hair and beauty services',
            'type' => 'salon',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $salonSubcategories = [
            'Braiding' => ['Box braids', 'Cornrows', 'Twist braids', 'Ghana weaving'],
            'Natural Hair Care' => ['Loc maintenance', 'Twist outs', 'Wash and go', 'Hot oil treatment'],
            'Weaves & Extensions' => ['Sew-in weave', 'Closure installation', 'Wig installation'],
            'Nail Services' => ['Manicure', 'Pedicure', 'Nail art', 'Acrylic nails'],
        ];

        foreach ($salonSubcategories as $name => $keywords) {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => 'Various '.$name.' services',
                'parent_id' => $salonCategory->id,
                'type' => 'salon',
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }
    }
}
