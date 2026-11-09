<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Tests\TestCase;

/**
 * @covers \App\Models\VendorSubscription
 * @covers \App\Models\VendorSubscription::scopeForVendor
 */
class VendorSubscriptionTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_subscription(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        VendorSubscription::create([
            'vendor_id' => $vendorA->id,
            'plan_name' => 'Starter',
            'plan_price' => 29.00,
            'status' => 'active',
        ]);

        VendorSubscription::create([
            'vendor_id' => $vendorB->id,
            'plan_name' => 'Professional',
            'plan_price' => 99.00,
            'status' => 'active',
        ]);

        $results = VendorSubscription::forVendor($vendorA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($vendorA->id, $results->first()->vendor_id);
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_subscription(): void
    {
        [$vendorA] = $this->makeVendor('vendor-empty');

        $results = VendorSubscription::forVendor($vendorA->id)->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        $this->makeVendor('vendor-nonexistent');

        $results = VendorSubscription::forVendor(999999)->get();

        $this->assertCount(0, $results);
    }

    private function makeVendor(string $slugSuffix): array
    {
        $user = User::factory()->create([
            'email' => 'vendor-' . $slugSuffix . '-' . uniqid() . '@example.com',
        ]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'business_name' => 'Test Vendor ' . $slugSuffix,
            'slug' => 'vendor-' . $slugSuffix . '-' . uniqid(),
            'business_email' => 'vendor-' . $slugSuffix . '-' . uniqid() . '@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        return [$vendor->fresh()];
    }
}
