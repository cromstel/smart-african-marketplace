<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@africanmarket.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'address' => '123 Admin Street',
            'city' => 'Lagos',
            'state' => 'Lagos State',
            'country' => 'Nigeria',
            'postal_code' => '100001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@africanmarket.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+1234567891',
            'address' => '456 Vendor Avenue',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'country' => 'Ghana',
            'postal_code' => '00233',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Salon Owner',
            'email' => 'salon@africanmarket.com',
            'password' => Hash::make('password'),
            'role' => 'salon_owner',
            'phone' => '+1234567892',
            'address' => '789 Beauty Lane',
            'city' => 'Nairobi',
            'state' => 'Nairobi County',
            'country' => 'Kenya',
            'postal_code' => '00100',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Customer User',
            'email' => 'customer@africanmarket.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1234567893',
            'address' => '321 Customer Road',
            'city' => 'Johannesburg',
            'state' => 'Gauteng',
            'country' => 'South Africa',
            'postal_code' => '2000',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Another Customer',
            'email' => 'customer2@africanmarket.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1234567894',
            'address' => '555 Market Street',
            'city' => 'Cairo',
            'state' => 'Cairo Governorate',
            'country' => 'Egypt',
            'postal_code' => '11511',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
