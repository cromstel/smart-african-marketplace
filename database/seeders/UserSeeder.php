<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $demoPassword = (string) env('DEMO_USER_PASSWORD', 'changeme');
        $vendorPassword = (string) env('VENDOR_DEMO_PASSWORD', $demoPassword);

        $demoUsers = [
            [
                'name' => 'Admin User',
                'email' => 'admin@africanmarket.com',
                'role' => 'admin',
                'phone' => '+1234567890',
                'address' => '123 Admin Street',
                'city' => 'Lagos',
                'state' => 'Lagos State',
                'country' => 'Nigeria',
                'postal_code' => '100001',
            ],
            [
                'name' => 'Vendor User',
                'email' => 'vendor@africanmarket.com',
                'role' => 'vendor',
                'phone' => '+1234567891',
                'address' => '456 Vendor Avenue',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => '00233',
            ],
            [
                'name' => 'Salon Owner',
                'email' => 'salon@africanmarket.com',
                'role' => 'salon_owner',
                'phone' => '+1234567892',
                'address' => '789 Beauty Lane',
                'city' => 'Nairobi',
                'state' => 'Nairobi County',
                'country' => 'Kenya',
                'postal_code' => '00100',
            ],
            [
                'name' => 'Customer User',
                'email' => 'customer@africanmarket.com',
                'role' => 'customer',
                'phone' => '+1234567893',
                'address' => '321 Customer Road',
                'city' => 'Johannesburg',
                'state' => 'Gauteng',
                'country' => 'South Africa',
                'postal_code' => '2000',
            ],
            [
                'name' => 'Another Customer',
                'email' => 'customer2@africanmarket.com',
                'role' => 'customer',
                'phone' => '+1234567894',
                'address' => '555 Market Street',
                'city' => 'Cairo',
                'state' => 'Cairo Governorate',
                'country' => 'Egypt',
                'postal_code' => '11511',
            ],
        ];

        foreach ($demoUsers as $demoUser) {
            $password = $demoUser['email'] === 'vendor@africanmarket.com'
                ? $vendorPassword
                : $demoPassword;

            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                array_merge($demoUser, [
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );

            if (Schema::hasTable('roles') && Schema::hasTable('role_user')) {
                $roleId = Role::where('slug', $demoUser['role'])->value('id');
                if ($roleId) {
                    $user->roles()->syncWithoutDetaching([$roleId]);
                }
            }
        }
    }
}
