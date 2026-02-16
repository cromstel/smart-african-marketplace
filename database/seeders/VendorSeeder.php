<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendorUser = User::where('email', 'vendor@africanmarket.com')->first();

        if ($vendorUser) {
            Vendor::create([
                'user_id' => $vendorUser->id,
                'business_name' => 'African Delights Store',
                'business_email' => 'contact@africandelights.com',
                'business_phone' => '+1234567891',
                'business_address' => '456 Vendor Avenue, Accra, Ghana',
                'business_registration_number' => 'GH-BN-2024-001',
                'tax_id' => 'GH-TAX-123456',
                'description' => 'Premium African food products supplier with over 10 years of experience',
                'commission_rate' => 15.00,
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => now(),
            ]);
        }

        $adminUser = User::where('email', 'admin@africanmarket.com')->first();

        if ($adminUser) {
            Vendor::create([
                'user_id' => $adminUser->id,
                'business_name' => 'African Market Official Store',
                'business_email' => 'official@africanmarket.com',
                'business_phone' => '+1234567890',
                'business_address' => '123 Admin Street, Lagos, Nigeria',
                'business_registration_number' => 'NG-BN-2024-001',
                'tax_id' => 'NG-TAX-987654',
                'description' => 'Official African Market store offering curated products',
                'commission_rate' => 0.00,
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => now(),
            ]);
        }
    }
}
