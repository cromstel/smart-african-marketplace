<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Booking permissions
        $bookingPermissions = [
            'bookings.view_any',
            'bookings.create',
            'bookings.update',
            'bookings.delete',
            'bookings.confirm',
            'bookings.cancel',
            'bookings.manage_types',
            'bookings.manage_resources',
        ];

        foreach ($bookingPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            // Admin gets all booking permissions
            $adminRole->givePermissionTo($bookingPermissions);
        }

        $vendorRole = Role::where('slug', 'vendor')->first();
        if ($vendorRole) {
            // Vendor gets specific booking permissions
            $vendorRole->givePermissionTo([
                'bookings.view_any',
                'bookings.update',
                'bookings.confirm',
                'bookings.cancel',
                'bookings.manage_resources',
            ]);
        }

        $salonOwnerRole = Role::where('slug', 'salon_owner')->first();
        if ($salonOwnerRole) {
            // Salon owner gets same as vendor
            $salonOwnerRole->givePermissionTo([
                'bookings.view_any',
                'bookings.update',
                'bookings.confirm',
                'bookings.cancel',
                'bookings.manage_resources',
            ]);
        }

        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            // Customer gets limited booking permissions
            $customerRole->givePermissionTo([
                'bookings.view_any',
                'bookings.create',
                'bookings.update',
                'bookings.cancel',
            ]);
        }
    }
}
