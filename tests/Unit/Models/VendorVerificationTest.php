<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorVerification;
use Tests\TestCase;

/**
 * @covers \App\Models\VendorVerification
 * @covers \App\Models\VendorVerification::scopeForVendor
 */
class VendorVerificationTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_verification(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        VendorVerification::create([
            'vendor_id' => $vendorA->id,
            'status' => 'pending',
            'documents' => ['id_card' => 'path/to/id.pdf'],
        ]);

        VendorVerification::create([
            'vendor_id' => $vendorB->id,
            'status' => 'approved',
            'documents' => ['id_card' => 'path/to/id2.pdf'],
        ]);

        $results = VendorVerification::forVendor($vendorA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($vendorA->id, $results->first()->vendor_id);
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_verification(): void
    {
        [$vendorA] = $this->makeVendor('vendor-empty');

        $results = VendorVerification::forVendor($vendorA->id)->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        $this->makeVendor('vendor-nonexistent');

        $results = VendorVerification::forVendor(999999)->get();

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
