<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorAvailability;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor availability windows.
 *
 * VendorAvailabilityController::update()/destroy() check
 * `$availability->vendor_id !== $vendor->id` and abort with 403 on an
 * implicitly-bound VendorAvailability. There is no dedicated edit route --
 * the index view lists and edits availability inline -- so only the
 * mutating actions are exercised here.
 */
class VendorAvailabilityIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_update_another_vendors_availability(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirAvailability] = $this->makeVendorWithAvailability();
        $originalStart = $theirAvailability->fresh()->start_time;

        $this->actingAs($mine)
            ->put(route('vendor.settings.availability.update', $theirAvailability), [
                'day_of_week' => 3,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'slot_interval_minutes' => 30,
                'min_lead_time_minutes' => 60,
            ])
            ->assertForbidden();

        $this->assertSame(
            $originalStart,
            $theirAvailability->fresh()->start_time,
            "A vendor updated another vendor's availability window. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_availability(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirAvailability] = $this->makeVendorWithAvailability();

        $this->actingAs($mine)
            ->delete(route('vendor.settings.availability.destroy', $theirAvailability))
            ->assertForbidden();

        $this->assertDatabaseHas('vendor_availabilities', ['id' => $theirAvailability->id]);
    }

    public function test_a_vendor_can_still_update_their_own_availability(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myAvailability] = $this->makeVendorWithAvailability();

        $this->actingAs($mine)
            ->put(route('vendor.settings.availability.update', $myAvailability), [
                'day_of_week' => 3,
                'start_time' => '10:00',
                'end_time' => '18:00',
                'slot_interval_minutes' => 30,
                'min_lead_time_minutes' => 60,
            ])
            ->assertRedirect(route('vendor.settings.availability.index'));

        $this->assertSame('10:00:00', $myAvailability->fresh()->start_time);
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: VendorAvailability}
     */
    private function makeVendorWithAvailability(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $availability = VendorAvailability::create([
            'vendor_id' => $vendor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'slot_interval_minutes' => 30,
            'min_lead_time_minutes' => 60,
            'buffer_minutes' => 0,
            'is_active' => true,
        ]);

        return [$user, $vendor, $availability];
    }
}
