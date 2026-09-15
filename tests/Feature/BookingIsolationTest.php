<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
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
 */
class BookingIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_customer_can_view_their_own_booking(): void
    {
        $customer = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->get(route('bookings.show', $booking))
            ->assertOk();
    }

    public function test_a_customer_cannot_view_another_customers_booking(): void
    {
        $owner = $this->makeEntitledCustomer();
        $stranger = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->get(route('bookings.show', $booking))
            ->assertForbidden();
    }

    public function test_a_customer_can_reschedule_their_own_booking(): void
    {
        $customer = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->get(route('bookings.reschedule', $booking))
            ->assertOk();
    }

    public function test_a_customer_cannot_reschedule_another_customers_booking(): void
    {
        $owner = $this->makeEntitledCustomer();
        $stranger = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->get(route('bookings.reschedule', $booking))
            ->assertForbidden();
    }

    public function test_a_customer_can_cancel_their_own_booking(): void
    {
        $customer = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($customer);

        $this->actingAs($customer)
            ->put(route('bookings.cancel', $booking))
            ->assertRedirect();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_a_customer_cannot_cancel_another_customers_booking(): void
    {
        $owner = $this->makeEntitledCustomer();
        $stranger = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($owner);

        $this->actingAs($stranger)
            ->put(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_a_customer_can_list_and_start_a_new_booking(): void
    {
        $customer = $this->makeEntitledCustomer();
        $service = $this->makeSalonService();

        $this->actingAs($customer)->get(route('bookings.index'))->assertOk();
        $this->actingAs($customer)->get(route('bookings.create', $service))->assertOk();
    }

    public function test_guest_is_redirected_to_login_for_booking_routes(): void
    {
        $customer = $this->makeEntitledCustomer();
        $booking = $this->makeBooking($customer);

        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        $this->get(route('bookings.show', $booking))->assertRedirect(route('login'));
    }

    private function makeEntitledCustomer(): User
    {
        $user = User::factory()->create();

        $plan = SubscriptionPlan::create([
            'name' => 'Booking Test Plan '.uniqid(),
            'slug' => 'booking-test-plan-'.uniqid(),
            'feature_keys' => ['bookings'],
        ]);

        UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);

        return $user->fresh();
    }

    private function makeSalonService(): SalonService
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
            'is_active' => true,
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
