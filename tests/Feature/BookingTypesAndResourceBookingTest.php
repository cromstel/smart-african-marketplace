<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingResource;
use App\Models\BookingType;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Live wiring for the previously-orphaned multi-type booking subsystem
 * (BookingType had a full admin CRUD UI and zero customer-facing entry
 * point -- see the BookingType::services() removal a prior session did
 * instead of guessing at this). Covers the new browse-by-type flow
 * (BookingTypeController) and the new rental booking creation flow
 * (ResourceBookingController), plus confirming the existing "my bookings"
 * pages still work once a resource-type booking exists alongside
 * salon-service ones.
 */
class BookingTypesAndResourceBookingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_booking_types_index_lists_active_types(): void
    {
        $this->makeBookingType('appointment');
        $this->makeBookingType('rental');

        $this->get(route('booking-types.index'))
            ->assertOk()
            ->assertSee('Appointment')
            ->assertSee('Rental');
    }

    public function test_appointment_type_lists_active_salon_services(): void
    {
        $bookingType = $this->makeBookingType('appointment');
        $service = $this->makeSalonService();

        $this->get(route('booking-types.show', $bookingType))
            ->assertOk()
            ->assertSee($service->name);
    }

    public function test_rental_type_lists_active_booking_resources(): void
    {
        $bookingType = $this->makeBookingType('rental');
        [, $resource] = $this->makeBookingResource();

        $this->get(route('booking-types.show', $bookingType))
            ->assertOk()
            ->assertSee($resource->name);
    }

    public function test_reservation_type_shows_coming_soon_not_a_fake_listing(): void
    {
        $bookingType = $this->makeBookingType('reservation');

        $this->get(route('booking-types.show', $bookingType))
            ->assertOk()
            ->assertSee('available yet');
    }

    public function test_resource_booking_create_page_loads(): void
    {
        $customer = $this->makeCustomer();
        [, $resource] = $this->makeBookingResource();

        $this->actingAs($customer)
            ->get(route('resource-bookings.create', $resource))
            ->assertOk()
            ->assertSee($resource->name);
    }

    public function test_customer_can_create_a_resource_booking(): void
    {
        $customer = $this->makeCustomer();
        [$vendorUser, $resource] = $this->makeBookingResource();
        $start = now()->addDays(3)->toDateString();
        $end = now()->addDays(5)->toDateString();

        $response = $this->actingAs($customer)->post(route('resource-bookings.store', $resource), [
            'start_date' => $start,
            'end_date' => $end,
            'quantity' => 1,
        ]);

        $booking = Booking::where('user_id', $customer->id)->where('resource_id', $resource->id)->first();

        $this->assertNotNull($booking, 'Expected a resource booking to be created.');
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertSame('rental', $booking->booking_type);
        $this->assertSame($resource->name, $booking->resource_name);
        $this->assertSame($vendorUser->id, $booking->salon_owner_id);
        $this->assertSame(1, $booking->quantity);
        $this->assertSame($start, $booking->booking_date->toDateString());
        $this->assertSame($end, $booking->end_date->toDateString());
        // 3 inclusive days (start, start+1, end) at the fixture's $25/day
        // daily rate -- BookingResource::calculatePrice()'s daily branch.
        $this->assertEquals(75.00, $booking->unit_price);
        $this->assertEquals(75.00, $booking->total_amount);
    }

    public function test_resource_booking_is_blocked_when_quantity_exceeds_availability(): void
    {
        $customer = $this->makeCustomer();
        [, $resource] = $this->makeBookingResource(quantityAvailable: 1);

        $response = $this->actingAs($customer)->post(route('resource-bookings.store', $resource), [
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $customer->id,
            'resource_id' => $resource->id,
        ]);
    }

    public function test_resource_booking_respects_existing_overlapping_bookings(): void
    {
        $customer = $this->makeCustomer();
        [, $resource] = $this->makeBookingResource(quantityAvailable: 1);

        Booking::create([
            'booking_number' => 'RTL-EXISTING',
            'booking_type' => 'rental',
            'user_id' => User::factory()->create()->id,
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'salon_owner_id' => $resource->vendor->user_id,
            'booking_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'duration_minutes' => 2880,
            'quantity' => 1,
            'unit_price' => 10,
            'price' => 10,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($customer)->post(route('resource-bookings.store', $resource), [
            'start_date' => now()->addDays(4)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('bookings', [
            'user_id' => $customer->id,
            'resource_id' => $resource->id,
        ]);
    }

    public function test_my_bookings_index_renders_both_salon_service_and_resource_bookings(): void
    {
        $customer = $this->makeCustomer();
        $service = $this->makeSalonService();
        [, $resource] = $this->makeBookingResource();

        Booking::create([
            'booking_number' => 'APT-1',
            'booking_type' => 'appointment',
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $service->user_id,
            'booking_date' => now()->addDay(),
            'booking_time' => '10:00',
            'duration_minutes' => 30,
            'price' => 40,
            'status' => 'pending',
        ]);

        Booking::create([
            'booking_number' => 'RTL-1',
            'booking_type' => 'rental',
            'user_id' => $customer->id,
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'salon_owner_id' => $resource->vendor->user_id,
            'booking_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'duration_minutes' => 2880,
            'quantity' => 1,
            'unit_price' => 10,
            'price' => 10,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('bookings.index'))
            ->assertOk()
            ->assertSee($service->name)
            ->assertSee($resource->name);
    }

    public function test_my_bookings_show_renders_a_resource_booking(): void
    {
        $customer = $this->makeCustomer();
        [, $resource] = $this->makeBookingResource();

        $booking = Booking::create([
            'booking_number' => 'RTL-2',
            'booking_type' => 'rental',
            'user_id' => $customer->id,
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'salon_owner_id' => $resource->vendor->user_id,
            'booking_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'duration_minutes' => 2880,
            'quantity' => 2,
            'unit_price' => 10,
            'price' => 20,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee($resource->name)
            ->assertSee($resource->vendor->business_name);
    }

    public function test_reschedule_is_not_offered_for_a_resource_booking(): void
    {
        $customer = $this->makeCustomer();
        [, $resource] = $this->makeBookingResource();

        $booking = Booking::create([
            'booking_number' => 'RTL-3',
            'booking_type' => 'rental',
            'user_id' => $customer->id,
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'salon_owner_id' => $resource->vendor->user_id,
            'booking_date' => now()->addDays(3),
            'end_date' => now()->addDays(5),
            'duration_minutes' => 2880,
            'quantity' => 1,
            'unit_price' => 10,
            'price' => 10,
            'status' => 'pending',
        ]);

        $this->actingAs($customer)
            ->get(route('bookings.reschedule', $booking))
            ->assertRedirect(route('bookings.show', $booking));
    }

    // --------------------------------------------------------------- Fixtures

    private function makeCustomer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function makeBookingType(string $name): BookingType
    {
        return BookingType::updateOrCreate(['name' => $name], [
            'slug' => $name,
            'description' => ucfirst($name).' fixture.',
            'icon' => 'calendar',
            'default_duration_minutes' => 60,
            'is_active' => true,
            'sort_order' => 1,
        ]);
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

    /**
     * @return array{0: User, 1: BookingResource}
     */
    private function makeBookingResource(int $quantityAvailable = 5): array
    {
        $vendorUser = User::factory()->vendor()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Vendor '.uniqid(),
            'slug' => 'vendor-'.uniqid(),
            'business_email' => 'vendor'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'approved',
            'is_active' => true,
        ]);

        $resource = BookingResource::create([
            'vendor_id' => $vendor->id,
            'resource_type' => 'equipment',
            'name' => 'Resource '.uniqid(),
            'description' => 'Fixture resource.',
            'quantity_total' => $quantityAvailable,
            'quantity_available' => $quantityAvailable,
            'is_active' => true,
            'minimum_rental_hours' => 1,
            'pricing_type' => 'daily',
            'daily_rate' => 25,
        ]);

        return [$vendorUser, $resource];
    }
}
