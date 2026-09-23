<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * routes/web.php used to gate every customer booking route behind
 * can:bookings.view_any / bookings.update / bookings.cancel / bookings.create
 * -- ability strings with no matching Gate::define and no model argument to
 * resolve a Policy against. Laravel's Gate denies an undefined ability by
 * default, so these routes 403'd for every customer regardless of
 * ownership, even though BookingController itself already carried correct
 * $booking->user_id === auth()->id() checks. This test proves the routes
 * are reachable for an owning customer and still correctly blocked for a
 * non-owning one, now that they're wired to real BookingPolicy abilities.
 *
 * A second, separate bug lived on top of that one: the whole route group
 * was ALSO wrapped in module.access:bookings, a vendor-subscription-plan
 * gate meant for the vendor-side booking-management routes. A plain
 * customer account never holds a subscription, so that gate 403'd every
 * customer before BookingPolicy even ran -- makeCustomer() below used to
 * be called makeEntitledCustomer() and papered over this in the test
 * fixture by giving the "customer" a fake subscription just to make these
 * tests pass, which masked the real-world bug instead of catching it. The
 * gate has been removed from the customer route group (it stays on the
 * vendor.bookings.* group, where it belongs), and the fixture now reflects
 * an ordinary customer: no subscription at all.
 */
class BookingIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_customer_can_view_their_own_booking(): void
    {
        $customer = $this->makeCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->get(route('bookings.show', $booking))
            ->assertOk();
    }

    public function test_a_customer_cannot_view_another_customers_booking(): void
    {
        $owner = $this->makeCustomer();
        $stranger = $this->makeCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->get(route('bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_a_customer_can_reschedule_their_own_booking(): void
    {
        $customer = $this->makeCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->get(route('bookings.reschedule', $booking))
            ->assertOk();
    }

    public function test_a_customer_cannot_reschedule_another_customers_booking(): void
    {
        $owner = $this->makeCustomer();
        $stranger = $this->makeCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->get(route('bookings.reschedule', $booking))
            ->assertForbidden();
    }

    public function test_a_customer_can_cancel_their_own_booking(): void
    {
        $customer = $this->makeCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->put(route('bookings.cancel', $booking))
            ->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_a_customer_cannot_cancel_another_customers_booking(): void
    {
        $owner = $this->makeCustomer();
        $stranger = $this->makeCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->put(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_a_customer_can_list_and_start_a_new_booking(): void
    {
        $customer = $this->makeCustomer();
        $service = $this->makeSalonService();

        $this->actingAs($customer)->get(route('bookings.index'))->assertOk();
        $this->actingAs($customer)->get(route('bookings.create', $service))->assertOk();
    }

    public function test_guest_is_redirected_to_login_for_booking_routes(): void
    {
        $customer = $this->makeCustomer();
        $booking = $this->makeBooking($customer);

        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        $this->get(route('bookings.show', $booking))->assertRedirect(route('login'));
    }

    public function test_a_customer_with_no_subscription_can_create_a_booking(): void
    {
        // Regression test for the module.access:bookings bug described in
        // the class docblock: this customer has no subscription of any
        // kind, which used to 403/redirect before BookingController::store
        // ever ran.
        $customer = $this->makeCustomer();
        $service = $this->makeSalonService();
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($customer)->post(route('bookings.store', $service), [
            'booking_date' => $date,
            'booking_time' => '10:00',
            // customer_notes intentionally omitted: BookingController::store
            // used to read $validated['customer_notes'] without a null
            // fallback, and $request->validate() omits the key entirely
            // for an absent nullable field, throwing "Undefined array key".
        ]);

        $booking = Booking::where('user_id', $customer->id)
            ->where('salon_service_id', $service->id)
            ->first();

        $this->assertNotNull($booking, 'Expected a booking to be created.');
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertNull($booking->customer_notes);
    }

    public function test_a_customer_cannot_book_a_service_the_vendor_deactivated(): void
    {
        // BookingController::store() used to have no is_active check at all,
        // unlike CartController's equivalent guard on the commit-to-purchase
        // step for products. A customer with a stale page open (or a direct
        // POST) could still create a real booking against a service the
        // vendor had turned off.
        $customer = $this->makeCustomer();
        $service = $this->makeSalonService(isActive: false);
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->actingAs($customer)->post(route('bookings.store', $service), [
            'booking_date' => $date,
            'booking_time' => '10:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'salon_service_id' => $service->id,
            'user_id' => $customer->id,
        ]);
    }

    public function test_a_vendor_cannot_create_a_booking_as_a_customer(): void
    {
        // BookingPolicy::create() only grants admins and customers -- a
        // vendor account booking a service (as opposed to managing bookings
        // made on their own services, via vendor.bookings.*) is correctly
        // forbidden.
        $vendor = User::factory()->create(['role' => 'vendor']);
        $service = $this->makeSalonService();

        $this->actingAs($vendor)
            ->get(route('bookings.create', $service))
            ->assertForbidden();
    }

    private function makeCustomer(): User
    {
        // Deliberately a bare factory customer with no subscription of any
        // kind -- see the class docblock. If the module.access:bookings
        // regression ever comes back, every test in this file using this
        // fixture will fail with a 302 to the upgrade-subscription page.
        return User::factory()->create(['role' => 'customer']);
    }

    private function makeSalonService(bool $isActive = true): SalonService
    {
        $owner = User::factory()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        return SalonService::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Service '.uniqid(),
            'slug' => 'service-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 50,
            'duration_minutes' => 30,
            'is_active' => $isActive,
        ]);
    }

    private function makeBooking(User $customer, string $status = 'confirmed'): Booking
    {
        $service = $this->makeSalonService();

        return Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $service->user_id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => $status,
        ]);
    }
}
