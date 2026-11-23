<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\BookingType;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * BookingTypeController had a full CRUD implementation (index/create/store/
 * edit/update/destroy/toggle) with no admin view for any of it, and no nav
 * link. Building the views surfaced two further bugs in the same area:
 *
 * - The reorder/pricing-tiers routes referenced controller methods that
 *   didn't exist at all (reorder now implemented below; the pricing-tiers
 *   routes were removed -- see routes/web.php for why, they referenced a
 *   relationship that has never existed in this schema).
 * - BookingType::bookings() used a `booking_type_id` foreign key that was
 *   never migrated onto the bookings table (also referenced, equally
 *   broken, by dead code in BookingService/BookingPricingService). Fixed
 *   to match on the `booking_type` enum column real bookings actually
 *   populate, by name.
 */
class BookingTypeManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bookings_relation_counts_by_matching_the_booking_type_enum_to_the_name(): void
    {
        // Regression: this used to be hasMany(Booking::class, 'booking_type_id'),
        // a column that doesn't exist on the bookings table. The real, always-
        // populated column is the `booking_type` enum, matched here by name.
        $appointment = BookingType::create(['name' => 'appointment']);
        $rental = BookingType::create(['name' => 'rental']);

        $this->makeBookingWithType('appointment');
        $this->makeBookingWithType('appointment');
        $this->makeBookingWithType('rental');

        $this->assertSame(2, $appointment->bookings()->count());
        $this->assertSame(1, $rental->bookings()->count());
    }

    public function test_index_page_renders_with_real_booking_type_and_booking_data_present(): void
    {
        $admin = User::factory()->admin()->create();
        $type = BookingType::create(['name' => 'appointment']);
        $this->makeBookingWithType('appointment');

        $response = $this->actingAs($admin)->get(route('admin.booking-types.index'));

        $response->assertOk();
        $response->assertSee($type->name);
        $response->assertSee($type->slug);
    }

    public function test_admin_can_create_a_booking_type(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.booking-types.store'), [
            'name' => 'Consultation '.uniqid(),
            'default_duration_minutes' => 45,
            'booking_window_days' => 14,
            'cancellation_policy_hours' => 12,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.booking-types.index'));
        $this->assertDatabaseHas('booking_types', [
            'default_duration_minutes' => 45,
            'booking_window_days' => 14,
        ]);
    }

    public function test_admin_can_update_a_booking_type(): void
    {
        $admin = User::factory()->admin()->create();
        $type = BookingType::create([
            'name' => 'Rental '.uniqid(),
            'default_duration_minutes' => 60,
            'booking_window_days' => 30,
            'cancellation_policy_hours' => 24,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.booking-types.update', $type), [
            'name' => $type->name,
            'default_duration_minutes' => 120,
            'booking_window_days' => $type->booking_window_days,
            'cancellation_policy_hours' => $type->cancellation_policy_hours,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.booking-types.index'));
        $this->assertSame(120, $type->fresh()->default_duration_minutes);
    }

    public function test_admin_can_toggle_a_booking_type_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $type = BookingType::create(['name' => 'Class '.uniqid(), 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.booking-types.toggle', $type))
            ->assertRedirect();

        $this->assertFalse($type->fresh()->is_active);
    }

    public function test_admin_can_reorder_booking_types(): void
    {
        $admin = User::factory()->admin()->create();
        $first = BookingType::create(['name' => 'First '.uniqid(), 'sort_order' => 0]);
        $second = BookingType::create(['name' => 'Second '.uniqid(), 'sort_order' => 1]);

        $this->actingAs($admin)
            ->post(route('admin.booking-types.reorder', $first), ['direction' => 'down'])
            ->assertRedirect();

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(0, $second->fresh()->sort_order);
    }

    public function test_reordering_past_the_last_item_is_a_harmless_no_op(): void
    {
        $admin = User::factory()->admin()->create();
        $only = BookingType::create(['name' => 'Only '.uniqid(), 'sort_order' => 0]);

        $this->actingAs($admin)
            ->post(route('admin.booking-types.reorder', $only), ['direction' => 'down'])
            ->assertRedirect();

        $this->assertSame(0, $only->fresh()->sort_order);
    }

    public function test_admin_cannot_delete_a_booking_type_with_existing_bookings(): void
    {
        $admin = User::factory()->admin()->create();
        $type = BookingType::create(['name' => 'appointment']);
        $this->makeBookingWithType('appointment');

        $response = $this->actingAs($admin)->delete(route('admin.booking-types.destroy', $type));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('booking_types', ['id' => $type->id]);
    }

    public function test_admin_can_delete_a_booking_type_with_no_bookings(): void
    {
        $admin = User::factory()->admin()->create();
        $type = BookingType::create(['name' => 'Unused '.uniqid()]);

        $response = $this->actingAs($admin)->delete(route('admin.booking-types.destroy', $type));

        $response->assertRedirect(route('admin.booking-types.index'));
        $this->assertDatabaseMissing('booking_types', ['id' => $type->id]);
    }

    public function test_the_admin_sidebar_links_to_booking_types(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.booking-types.index'), false);
    }

    private function makeBookingWithType(string $bookingType): Booking
    {
        $vendor = User::factory()->vendor()->create();
        $customer = User::factory()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);

        $service = SalonService::create([
            'user_id' => $vendor->id,
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
            'salon_owner_id' => $vendor->id,
            'booking_type' => $bookingType,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => 'confirmed',
        ]);
    }
}
