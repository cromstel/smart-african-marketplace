<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@africanmarket.com')->first();

        if ($customer) {
            Address::firstOrCreate(['user_id' => $customer->id, 'type' => 'both'], [
                'user_id' => $customer->id,
                'type' => 'both',
                'first_name' => 'Customer',
                'last_name' => 'User',
                'address_line1' => '321 Customer Road',
                'city' => 'Johannesburg',
                'state' => 'Gauteng',
                'postal_code' => '2000',
                'country' => 'South Africa',
                'phone' => '+1234567893',
                'is_default' => true,
            ]);

            Address::firstOrCreate(['user_id' => $customer->id, 'type' => 'shipping'], [
                'user_id' => $customer->id,
                'type' => 'shipping',
                'first_name' => 'Customer',
                'last_name' => 'User',
                'company' => 'Customer Company',
                'address_line1' => '456 Office Street',
                'city' => 'Cape Town',
                'state' => 'Western Cape',
                'postal_code' => '8000',
                'country' => 'South Africa',
                'phone' => '+1234567893',
                'is_default' => false,
            ]);
        }

        $customer2 = User::where('email', 'customer2@africanmarket.com')->first();

        if ($customer2) {
            Address::firstOrCreate(['user_id' => $customer2->id, 'type' => 'both'], [
                'user_id' => $customer2->id,
                'type' => 'both',
                'first_name' => 'Another',
                'last_name' => 'Customer',
                'address_line1' => '555 Market Street',
                'city' => 'Cairo',
                'state' => 'Cairo Governorate',
                'postal_code' => '11511',
                'country' => 'Egypt',
                'phone' => '+1234567894',
                'is_default' => true,
            ]);
        }
    }
}
