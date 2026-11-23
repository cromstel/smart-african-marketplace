<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorLedger;
use Tests\TestCase;

/**
 * @covers \App\Models\VendorLedger
 * @covers \App\Models\VendorLedger::scopeForVendor
 */
class VendorLedgerTest extends TestCase
{
    /** @test */
    public function for_vendor_scope_returns_only_that_vendor_ledger_entries(): void
    {
        [$vendorA] = $this->makeVendor('vendor-a');
        [$vendorB] = $this->makeVendor('vendor-b');

        $orderA = Order::create([
            'order_number' => 'ORD-A-' . uniqid(),
            'user_id' => $vendorA->user_id,
            'status' => 'pending',
            'total' => 10.00,
            'subtotal' => 10.00,
        ]);

        $orderB = Order::create([
            'order_number' => 'ORD-B-' . uniqid(),
            'user_id' => $vendorB->user_id,
            'status' => 'pending',
            'total' => 40.00,
            'subtotal' => 40.00,
        ]);

        VendorLedger::create([
            'vendor_id' => $vendorA->id,
            'order_id' => $orderA->id,
            'type' => 'credit_pending',
            'amount' => 10.00,
            'balance_after' => 10.00,
        ]);

        VendorLedger::create([
            'vendor_id' => $vendorB->id,
            'order_id' => $orderB->id,
            'type' => 'credit_available',
            'amount' => 40.00,
            'balance_after' => 40.00,
        ]);

        $results = VendorLedger::forVendor($vendorA->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($vendorA->id, $results->first()->vendor_id);
    }

    /** @test */
    public function for_vendor_scope_returns_empty_when_vendor_has_no_ledger_entries(): void
    {
        [$vendorA] = $this->makeVendor('vendor-empty');

        $results = VendorLedger::forVendor($vendorA->id)->get();

        $this->assertCount(0, $results);
    }

    /** @test */
    public function for_vendor_scope_with_invalid_vendor_id_returns_empty(): void
    {
        $this->makeVendor('vendor-nonexistent');

        $results = VendorLedger::forVendor(999999)->get();

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
