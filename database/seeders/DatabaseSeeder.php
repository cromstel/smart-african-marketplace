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

            VendorSeeder::class,
            ProductSeeder::class,
            SalonServiceSeeder::class,
            DeliveryZoneSeeder::class,
            AddressSeeder::class,
            BlogSeeder::class,
            // PermissionSeeder intentionally removed 2026-09-14: it targeted
            // spatie/laravel-permission's permissions/role_has_permissions
            // tables, which nothing else in the app uses (see
            // 2026_09_14_000000_drop_unused_spatie_permission_tables.php).
            // The app's own role system is App\Models\Role + role_user,
            // seeded by UserSeeder.
            ConfigurationSettingsSeeder::class,
            // Additional required reference data
            SubscriptionPlanSeeder::class,
            SystemSettingsSeeder::class,
            BookingTypeSeeder::class,
            BrandColorSeeder::class,
            VendorSubscriptionSeeder::class,
        ]);
    }
}
