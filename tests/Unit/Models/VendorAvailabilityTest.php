<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorAvailability;
use Tests\TestCase;

/**
 * @covers \App\Models\VendorAvailability
 * @covers \App\Models\VendorAvailability::scopeForVendor
 */
class VendorAvailabilityTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_availabilities(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        VendorAvailability::create([
            'vendor_id' => $vendorA->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_interval_minutes' => 30,
            'is_active' => true,
        ]);

        VendorAvailability::create([
            'vendor_id' => $vendorB->id,
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '16:00',
            'slot_interval_minutes' => 60,
            'is_active' => true,
        ]);

        $results = VendorAvailability::forVendor($vendorA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($vendorA->id, $results->first()->vendor_id);
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_availabilities(): void
    {
        [$vendorA] = $this->makeVendor('vendor-empty');

        $results = VendorAvailability::forVendor($vendorA->id)->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        $this->makeVendor('vendor-nonexistent');

        $results = VendorAvailability::forVendor(999999)->get();

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
