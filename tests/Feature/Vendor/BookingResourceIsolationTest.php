<?php

namespace Tests\Feature\Vendor;

use App\Models\BookingResource;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor booking resources (rental
 * equipment/vehicles/venues).
 *
 * Same bug class as OrderController: `auth()->id()` (a `users.id`) was
 * used both to stamp new resources' `vendor_id` on create and to check
 * ownership in `ensureOwnership()`, against a column that actually
 * references `vendors.id`. A vendor's newly created resources could be
 * silently attributed to a different vendor's account, and any vendor whose
 * user id happened to match another vendor's vendor id could edit, update,
 * or delete that vendor's resources.
 *
 * These routes additionally sit behind `can:bookings.manage_resources`,
 * which was never defined anywhere (see AuthServiceProvider) and therefore
 * denied every request outright -- fixed alongside this isolation bug,
 * otherwise every assertion here would pass for the wrong reason.
 */
class BookingResourceIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_can_create_a_resource(): void
    {
        // BookingResourceService::createResource() used to write a field
        // set ('type', 'slug', 'weekly_rate', 'min_rental_hours', ...) that
        // did not match BookingResource's actual columns ('resource_type',
        // 'quantity_total', 'minimum_rental_hours', no slug/weekly_rate/
        // category/deposit at all). The mismatched fields were silently
        // dropped by mass assignment, and the required, defaultless
        // `resource_type` column was never among them, so every store()
        // threw a SQL error -- this feature could never actually create a
        // resource, regardless of vendor_id correctness.
        [$vendor] = $this->makeVendor(['bookings']);

        $this->actingAs($vendor)
            ->post(route('vendor.booking-resources.store'), [
                'name' => 'Delivery Van',
                'description' => 'A cargo van for rent.',
                'type' => 'vehicle',
                'hourly_rate' => 25,
                'daily_rate' => 150,
                'quantity_available' => 2,
                'min_rental_hours' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('vendor.booking-resources.index'));

        $this->assertDatabaseHas('booking_resources', [
            'vendor_id' => $vendor->vendor->id,
            'resource_type' => 'vehicle',
            'name' => 'Delivery Van',
            'quantity_total' => 2,
            'quantity_available' => 2,
            'minimum_rental_hours' => 2,
            'hourly_rate' => 25,
            'daily_rate' => 150,
        ]);
    }

    public function test_a_vendor_cannot_open_another_vendors_resource_for_editing(): void
    {
        [$mine] = $this->makeVendor(['bookings']);
        [, , $theirResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.edit', $theirResource))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_view_another_vendors_resource(): void
    {
        // `show` used to be excluded from the resource route entirely
        // (its view didn't exist), so this was previously untestable.
        [$mine] = $this->makeVendor(['bookings']);
        [, , $theirResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.show', $theirResource))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_resource(): void
    {
        [$mine] = $this->makeVendor(['bookings']);
        [, , $theirResource] = $this->makeVendorWithResource();
        $originalName = $theirResource->name;

        $this->actingAs($mine)
            ->put(route('vendor.booking-resources.update', $theirResource), [
                'name' => 'Hijacked',
                'type' => $theirResource->resource_type,
                'hourly_rate' => 1,
                'quantity_available' => 1,
            ])
            ->assertForbidden();

        $this->assertSame($originalName, $theirResource->fresh()->name);
    }

    public function test_a_vendor_cannot_delete_another_vendors_resource(): void
    {
        [$mine] = $this->makeVendor(['bookings']);
        [, , $theirResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->delete(route('vendor.booking-resources.destroy', $theirResource))
            ->assertForbidden();

        $this->assertDatabaseHas('booking_resources', ['id' => $theirResource->id]);
    }

    public function test_a_vendor_can_still_update_their_own_resource(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->put(route('vendor.booking-resources.update', $myResource), [
                'name' => 'Updated Name',
                'type' => $myResource->resource_type,
                'hourly_rate' => 30,
                'quantity_available' => 2,
            ])
            ->assertRedirect(route('vendor.booking-resources.index'));

        $this->assertSame('Updated Name', $myResource->fresh()->name);
    }

    /**
     * Now that resources/views/vendor/bookings/resources/*.blade.php exist
     * (they didn't before -- see the other tests' former comments in this
     * file, and the class docblock), exercise every vendor-facing route
     * this feature exposes end to end.
     */
    public function test_a_vendor_can_view_the_resource_index_page(): void
    {
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.index'))
            ->assertOk()
            ->assertSee($myResource->name);
    }

    public function test_a_vendor_can_view_the_create_form(): void
    {
        [$vendor] = $this->makeVendor(['bookings']);

        $this->actingAs($vendor)
            ->get(route('vendor.booking-resources.create'))
            ->assertOk();
    }

    public function test_a_vendor_can_view_their_own_resource(): void
    {
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.show', $myResource))
            ->assertOk()
            ->assertSee($myResource->name);
    }

    public function test_a_vendor_can_view_the_edit_form_for_their_own_resource(): void
    {
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.edit', $myResource))
            ->assertOk()
            ->assertSee($myResource->name);
    }

    public function test_a_vendor_can_view_availability_for_their_own_resource(): void
    {
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.availability', $myResource))
            ->assertOk();
    }

    public function test_a_vendor_can_view_statistics_for_their_own_resource(): void
    {
        // statistics() didn't exist at all -- the route pointed at a
        // controller method that was never written, so this 500'd for
        // every vendor. Built out alongside the other resource views.
        [$mine, , $myResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.statistics', $myResource))
            ->assertOk();
    }

    public function test_a_vendor_cannot_view_statistics_for_another_vendors_resource(): void
    {
        [$mine] = $this->makeVendor(['bookings']);
        [, , $theirResource] = $this->makeVendorWithResource();

        $this->actingAs($mine)
            ->get(route('vendor.booking-resources.statistics', $theirResource))
            ->assertForbidden();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: BookingResource}
     */
    private function makeVendorWithResource(): array
    {
        [$user, $vendor] = $this->makeVendor(['bookings']);

        $resource = BookingResource::create([
            'vendor_id' => $vendor->id,
            'resource_type' => 'equipment',
            'name' => 'Resource '.uniqid(),
            'quantity_total' => 3,
            'quantity_available' => 3,
            'is_active' => true,
            'pricing_type' => 'hourly',
            'hourly_rate' => 15,
        ]);

        return [$user, $vendor, $resource];
    }
}
