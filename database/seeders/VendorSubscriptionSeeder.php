<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Database\Seeder;

class VendorSubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::whereDoesntHave('subscription')->get();

        foreach ($vendors as $vendor) {
            VendorSubscription::create([
                'vendor_id' => $vendor->id,
                'plan_name' => 'Trial',
                'plan_price' => 0.00,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays(30),
                'current_period_start' => now(),
                'current_period_end' => now()->addDays(30),
            ]);
        }

        $this->command->info("✅ Created trial subscriptions for {$vendors->count()} vendors");
    }
}
