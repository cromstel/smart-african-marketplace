<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        DeliveryZone::create([
            'name' => 'Lagos Metropolitan',
            'cities' => ['Lagos', 'Ikeja', 'Victoria Island', 'Lekki', 'Ajah'],
            'postal_codes' => ['100001', '100211', '101245', '105102'],
            'base_fee' => 5.00,
            'per_km_rate' => 0.50,
            'max_distance_km' => 50.00,
            'is_active' => true,
        ]);

        DeliveryZone::create([
            'name' => 'Accra Central',
            'cities' => ['Accra', 'Tema', 'Osu', 'Labone', 'East Legon'],
            'postal_codes' => ['00233', 'GA-001', 'GA-002'],
            'base_fee' => 4.00,
            'per_km_rate' => 0.40,
            'max_distance_km' => 40.00,
            'is_active' => true,
        ]);

        DeliveryZone::create([
            'name' => 'Nairobi Region',
            'cities' => ['Nairobi', 'Westlands', 'Kilimani', 'Karen', 'Lavington'],
            'postal_codes' => ['00100', '00200', '00300'],
            'base_fee' => 6.00,
            'per_km_rate' => 0.60,
            'max_distance_km' => 45.00,
            'is_active' => true,
        ]);

        DeliveryZone::create([
            'name' => 'Johannesburg Area',
            'cities' => ['Johannesburg', 'Sandton', 'Rosebank', 'Midrand', 'Randburg'],
            'postal_codes' => ['2000', '2001', '2196', '2090'],
            'base_fee' => 7.00,
            'per_km_rate' => 0.70,
            'max_distance_km' => 60.00,
            'is_active' => true,
        ]);

        DeliveryZone::create([
            'name' => 'Cairo District',
            'cities' => ['Cairo', 'Giza', 'Heliopolis', 'Maadi', 'Nasr City'],
            'postal_codes' => ['11511', '11512', '11513'],
            'base_fee' => 3.00,
            'per_km_rate' => 0.30,
            'max_distance_km' => 35.00,
            'is_active' => true,
        ]);
    }
}
