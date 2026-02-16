<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
            SalonServiceSeeder::class,
            DeliveryZoneSeeder::class,
            AddressSeeder::class,
        ]);
    }
}
