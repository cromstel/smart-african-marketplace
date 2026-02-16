<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalonServiceSeeder extends Seeder
{
    public function run(): void
    {
        $salonOwner = User::where('role', 'salon_owner')->first();

        if (! $salonOwner) {
            return;
        }

        $salonCategories = Category::where('type', 'salon')->whereNotNull('parent_id')->get();

        if ($salonCategories->isEmpty()) {
            return;
        }

        $services = [
            [
                'name' => 'Box Braids (Medium)',
                'description' => 'Classic box braids with medium-sized sections. Includes hair washing, conditioning, and styling. Estimated time: 4-5 hours.',
                'price' => 120.00,
                'duration_minutes' => 300,
            ],
            [
                'name' => 'Cornrows Styling',
                'description' => 'Traditional cornrows in various patterns. Hair preparation and styling included. Estimated time: 2-3 hours.',
                'price' => 80.00,
                'duration_minutes' => 180,
            ],
            [
                'name' => 'Loc Maintenance',
                'description' => 'Complete loc maintenance including retwisting, washing, and conditioning. Estimated time: 2-3 hours.',
                'price' => 90.00,
                'duration_minutes' => 180,
            ],
            [
                'name' => 'Sew-in Weave Installation',
                'description' => 'Professional sew-in weave installation with cornrow base. Includes hair preparation. Estimated time: 3-4 hours.',
                'price' => 150.00,
                'duration_minutes' => 240,
            ],
            [
                'name' => 'Ghana Weaving',
                'description' => 'Intricate Ghana weaving styles with extensions. Includes hair preparation and styling. Estimated time: 3-4 hours.',
                'price' => 100.00,
                'duration_minutes' => 240,
            ],
            [
                'name' => 'Natural Hair Treatment',
                'description' => 'Deep conditioning treatment for natural hair including hot oil treatment and steam. Estimated time: 1-2 hours.',
                'price' => 60.00,
                'duration_minutes' => 120,
            ],
            [
                'name' => 'Deluxe Manicure & Pedicure',
                'description' => 'Complete nail care service including filing, buffing, cuticle care, massage, and polish. Estimated time: 1.5 hours.',
                'price' => 70.00,
                'duration_minutes' => 90,
            ],
            [
                'name' => 'Senegalese Twists',
                'description' => 'Beautiful Senegalese twists using quality hair extensions. Includes hair preparation. Estimated time: 4-5 hours.',
                'price' => 130.00,
                'duration_minutes' => 300,
            ],
        ];

        foreach ($services as $serviceData) {
            $category = $salonCategories->random();

            SalonService::create([
                'user_id' => $salonOwner->id,
                'category_id' => $category->id,
                'name' => $serviceData['name'],
                'slug' => Str::slug($serviceData['name']),
                'description' => $serviceData['description'],
                'price' => $serviceData['price'],
                'duration_minutes' => $serviceData['duration_minutes'],
                'is_active' => true,
            ]);
        }
    }
}
