<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorSeederUpdate extends Seeder
{
    public function run(): void
    {
        // Update existing vendors to have vendor_type and mark as service providers
        $vendorUsers = [
            'vendor@africanmarket.com' => [
                'business_name' => 'African Delights Store',
                'vendor_type' => 'private_home_assistance',
                'is_service_provider' => true,
            ],
        ];

        foreach ($vendorUsers as $email => $data) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Vendor::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'vendor_type' => $data['vendor_type'],
                        'is_service_provider' => $data['is_service_provider'],
                    ]
                );
            }
        }

        // Create additional vendor type examples for each category
        $vendorTypes = [
            'private_painter' => ['email' => 'painter.private@africanmarket.com', 'name' => 'Precision Painters Private'],
            'corporate_painter' => ['email' => 'painter.corporate@africanmarket.com', 'name' => 'Elite Painting Corp'],
            'private_electrician' => ['email' => 'electrician.private@africanmarket.com', 'name' => 'Bright Sparks Electric'],
            'corporate_electrician' => ['email' => 'electrician.corporate@africanmarket.com', 'name' => 'PowerGrid Solutions'],
            'private_mechanic' => ['email' => 'mechanic.private@africanmarket.com', 'name' => 'Quick Fix Garage'],
            'corporate_mechanic' => ['email' => 'mechanic.corporate@africanmarket.com', 'name' => 'FleetMaster Motors'],
            'private_home_assistance' => ['email' => 'homehelp.private@africanmarket.com', 'name' => 'Home Helpers Private'],
            'corporate_home_assistance' => ['email' => 'homehelp.corporate@africanmarket.com', 'name' => 'Domestic Care Corp'],
            'private_facility_management' => ['email' => 'facility.private@africanmarket.com', 'name' => 'CleanSpace FM'],
            'corporate_facility_management' => ['email' => 'facility.corporate@africanmarket.com', 'name' => 'FacilityPro Management'],
            'private_construction' => ['email' => 'construct.private@africanmarket.com', 'name' => 'BuildRight Homes'],
            'corporate_construction' => ['email' => 'construct.corporate@africanmarket.com', 'name' => 'Skyline Developers'],
            'men_salon' => ['email' => 'salon.men@africanmarket.com', 'name' => "Gentlemen's Grooming Lounge"],
            'women_salon' => ['email' => 'salon.women@africanmarket.com', 'name' => 'Elegance Beauty Studio'],
            'car_washing_bay' => ['email' => 'carwash@africanmarket.com', 'name' => 'Sparkle Car Wash Bay'],
            'car_garage' => ['email' => 'garage@africanmarket.com', 'name' => 'AutoCare Garage'],
        ];

        foreach ($vendorTypes as $vendorType => $info) {
            $user = User::where('email', $info['email'])->first();
            if ($user) {
                Vendor::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'business_name' => $info['name'],
                        'slug' => Str::slug($info['name']),
                        'business_email' => $info['email'],
                        'business_phone' => '+233000000000',
                        'business_address' => 'Accra, Ghana',
                        'description' => 'Professional '.ucwords(str_replace('_', ' ', $vendorType)).' services',
                        'vendor_type' => $vendorType,
                        'is_service_provider' => true,
                        'status' => 'approved',
                        'is_active' => true,
                        'approved_at' => now(),
                        'commission_rate' => 10.00,
                    ]
                );
            }
        }
    }
}
