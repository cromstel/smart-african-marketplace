<?php

namespace Tests\Feature\Vendor;

use App\Models\Salon;
use App\Models\SalonStylist;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor salon stylists.
 *
 * SalonStylist is owned indirectly through its parent Salon
 * (`salon_stylists.salon_id` -> `salons.user_id`), so
 * SalonStylistController::edit()/update()/destroy() check
 * `$salonStylist->salon->user_id !== auth()->id()` and abort with 403 on
 * an implicitly-bound SalonStylist.
 */
class SalonStylistIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_salon_stylist(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirStylist] = $this->makeVendorWithStylist();

        $this->actingAs($mine)
            ->get(route('vendor.salon-stylists.edit', $theirStylist))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_salon_stylist(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirStylist] = $this->makeVendorWithStylist();
        $originalName = $theirStylist->name;

        $this->actingAs($mine)
            ->put(route('vendor.salon-stylists.update', $theirStylist), [
                'salon_id' => $theirStylist->salon_id,
                'name' => 'Hijacked Stylist',
            ])
            ->assertForbidden();

        $this->assertSame(
            $originalName,
            $theirStylist->fresh()->name,
            "A vendor updated another vendor's salon stylist. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_salon_stylist(): void
    {
        [$mine] = $this->makeVendor();
        [, , , $theirStylist] = $this->makeVendorWithStylist();

        $this->actingAs($mine)
            ->delete(route('vendor.salon-stylists.destroy', $theirStylist))
            ->assertForbidden();

        $this->assertDatabaseHas('salon_stylists', ['id' => $theirStylist->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_salon_stylist(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , , $myStylist] = $this->makeVendorWithStylist();

        $this->actingAs($mine)
            ->get(route('vendor.salon-stylists.edit', $myStylist))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Salon, 3: SalonStylist}
     */
    private function makeVendorWithStylist(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $salonName = 'Salon '.uniqid();
        $salon = Salon::create([
            'user_id' => $user->id,
            'name' => $salonName,
            'slug' => Str::slug($salonName).'-'.uniqid(),
            'is_active' => true,
        ]);

        $stylist = SalonStylist::create([
            'salon_id' => $salon->id,
            'name' => 'Stylist '.uniqid(),
            'is_active' => true,
        ]);

        return [$user, $vendor, $salon, $stylist];
    }
}
