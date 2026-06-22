<?php

namespace Database\Seeders;

use App\Models\BookingType;
use Illuminate\Database\Seeder;

class BookingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookingTypes = [
            [
                'name' => 'appointment',
                'slug' => 'appointment',
                'description' => 'Professional service appointments such as salon, spa, health, and beauty services. Time-based scheduling with optional staff assignment.',
                'icon' => 'calendar',
                'default_duration_minutes' => 60,
                'min_duration_minutes' => 15,
                'max_duration_minutes' => 240,
                'booking_window_days' => 30,
                'cancellation_policy_hours' => 24,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'reservation',
                'slug' => 'reservation',
                'description' => 'Space or table reservations for restaurants, venues, coworking spaces, and other facilities. Duration-based booking with capacity management.',
                'icon' => 'clock',
                'default_duration_minutes' => 120,
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 480,
                'booking_window_days' => 60,
                'cancellation_policy_hours' => 48,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'rental',
                'slug' => 'rental',
                'description' => 'Equipment, vehicle, and property rentals. Inventory-based booking with hourly or daily pricing options.',
                'icon' => 'cube',
                'default_duration_minutes' => 1440, // 24 hours
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 43200, // 30 days
                'booking_window_days' => 90,
                'cancellation_policy_hours' => 72,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'consultation',
                'slug' => 'consultation',
                'description' => 'Expert consultation sessions for coaching, mentoring, legal advice, and professional guidance. One-on-one time-based appointments.',
                'icon' => 'user',
                'default_duration_minutes' => 60,
                'min_duration_minutes' => 15,
                'max_duration_minutes' => 120,
                'booking_window_days' => 30,
                'cancellation_policy_hours' => 12,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'class',
                'slug' => 'class',
                'description' => 'Group learning sessions, workshops, fitness classes, and courses. Session-based booking with participant limits.',
                'icon' => 'users',
                'default_duration_minutes' => 90,
                'min_duration_minutes' => 30,
                'max_duration_minutes' => 480,
                'booking_window_days' => 14,
                'cancellation_policy_hours' => 24,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'event',
                'slug' => 'event',
                'description' => 'Private events, celebrations, corporate functions, and parties. Multi-day booking with venue capacity and resource management.',
                'icon' => 'star',
                'default_duration_minutes' => 240,
                'min_duration_minutes' => 120,
                'max_duration_minutes' => 10080, // 7 days
                'booking_window_days' => 180,
                'cancellation_policy_hours' => 168, // 1 week
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($bookingTypes as $type) {
            BookingType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
