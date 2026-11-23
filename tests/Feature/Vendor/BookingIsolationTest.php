<?php

namespace Tests\Feature\Vendor;

use App\Models\Booking;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Vendor\BookingController already carries a correct ensureOwnership()
 * check (salon_owner_id, a users.id FK, compared against auth()->id()), but
 * the route middleware wrapping it -- can:bookings.view_any/confirm/cancel
 * -- referenced ability strings with no Gate::define and no model argument,
 * so Laravel's Gate denied them for every vendor by default regardless of
 * ownership. This proves the routes are reachable for the owning vendor and
 * still correctly blocked for a non-owning one now that they're wired to
 * real BookingPolicy abilities (which had their own salon_owner_id vs.
 * vendor->id bug fixed in the same change).
 */
class BookingIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_can_view_their_own_booking(): void
    {
        [$vendorUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($vendorUser);

        $this->actingAs($vendorUser)
            ->get(route('vendor.bookings.show', $booking))
            ->assertOk();
    }

    public function test_a_vendor_cannot_view_another_vendors_booking(): void
    {
        [$ownerUser] = $this->makeVendor(['bookings']);
        [$strangerUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($ownerUser);

        $this->actingAs($strangerUser)
            ->get(route('vendor.bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_a_vendor_can_confirm_their_own_pending_booking(): void
    {
        [$vendorUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($vendorUser, 'pending');

        $this->actingAs($vendorUser)
            ->post(route('vendor.bookings.confirm', $booking))
            ->assertRedirect();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_a_vendor_cannot_confirm_another_vendors_booking(): void
    {
        [$ownerUser] = $this->makeVendor(['bookings']);
        [$strangerUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($ownerUser, 'pending');

        $this->actingAs($strangerUser)
            ->post(route('vendor.bookings.confirm', $booking))
            ->assertForbidden();

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_a_vendor_can_cancel_their_own_booking(): void
    {
        [$vendorUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($vendorUser);

        $this->actingAs($vendorUser)
            ->post(route('vendor.bookings.cancel', $booking))
            ->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_a_vendor_cannot_cancel_another_vendors_booking(): void
    {
        [$ownerUser] = $this->makeVendor(['bookings']);
        [$strangerUser] = $this->makeVendor(['bookings']);
        $booking = $this->makeBookingFor($ownerUser);

        $this->actingAs($strangerUser)
            ->post(route('vendor.bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_a_vendor_can_still_list_their_own_bookings(): void
    {
        [$vendorUser] = $this->makeVendor(['bookings']);
        $this->makeBookingFor($vendorUser);

        $this->actingAs($vendorUser)
            ->get(route('vendor.bookings.index'))
            ->assertOk();
    }

    /**
     * @return Booking, salon_owner_id set to $vendorUser->id (a users.id,
     * per the bookings table's foreign key -- NOT the Vendor record's id).
     */
    private function makeBookingFor(User $vendorUser, string $status = 'confirmed'): Booking
    {
        $customer = User::factory()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        $service = SalonService::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 50,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);

        return Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $vendorUser->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => $status,
        ]);
    }
}
