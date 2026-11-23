<?php

namespace Tests\Feature\Vendor;

use App\Models\Salon;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor salons.
 *
 * Salon has no `vendor_id` column -- it is owned directly by `user_id`
 * (a real `users.id`), which is the correct id to compare here, unlike the
 * `vendor_id`-column controllers elsewhere in this suite.
 * SalonController::edit()/update()/destroy() check
 * `$salon->user_id !== auth()->id()` and abort with 403 on an
 * implicitly-bound Salon.
 */
class SalonIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_salon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirSalon] = $this->makeVendorWithSalon();

        $this->actingAs($mine)
            ->get(route('vendor.salon.edit', $theirSalon))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_salon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirSalon] = $this->makeVendorWithSalon();
        $originalName = $theirSalon->name;

        $this->actingAs($mine)
            ->put(route('vendor.salon.update', $theirSalon), ['name' => 'Hijacked Salon'])
            ->assertForbidden();

        $this->assertSame(
            $originalName,
            $theirSalon->fresh()->name,
            "A vendor updated another vendor's salon. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_salon(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirSalon] = $this->makeVendorWithSalon();

        $this->actingAs($mine)
            ->delete(route('vendor.salon.destroy', $theirSalon))
            ->assertForbidden();

        $this->assertDatabaseHas('salons', ['id' => $theirSalon->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_salon(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $mySalon] = $this->makeVendorWithSalon();

        $this->actingAs($mine)
            ->get(route('vendor.salon.edit', $mySalon))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: Salon}
     */
    private function makeVendorWithSalon(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $name = 'Salon '.uniqid();
        $salon = Salon::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'is_active' => true,
        ]);

        return [$user, $vendor, $salon];
    }
}
