<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// Seeders are referenced via ::class; no imports needed.

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VendorCategorySeeder::class,
            CategorySeeder::class,
            MediaFolderSeeder::class,
            SalonServiceCategorySeeder::class,
            VendorSampleSeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
            SalonServiceSeeder::class,
            DeliveryZoneSeeder::class,
            AddressSeeder::class,
            BlogSeeder::class,
            PermissionSeeder::class,
            ConfigurationSettingsSeeder::class,
        ]);
    }
}
