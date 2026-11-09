<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBalance;
use Tests\TestCase;

/**
 * @covers \App\Models\VendorBalance
 * @covers \App\Models\VendorBalance::scopeForVendor
 */
class VendorBalanceTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_balance(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        VendorBalance::create([
            'vendor_id' => $vendorA->id,
            'available' => 100.00,
            'pending' => 0.00,
            'lifetime_earnings' => 100.00,
        ]);

        VendorBalance::create([
            'vendor_id' => $vendorB->id,
            'available' => 200.00,
            'pending' => 50.00,
            'lifetime_earnings' => 250.00,
        ]);

        $results = VendorBalance::forVendor($vendorA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($vendorA->id, $results->first()->vendor_id);
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_balance(): void
    {
        [$vendorA] = $this->makeVendor('vendor-empty');

        $results = VendorBalance::forVendor($vendorA->id)->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        $this->makeVendor('vendor-nonexistent');

        $results = VendorBalance::forVendor(999999)->get();

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
