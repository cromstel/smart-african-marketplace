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
 * Admin CRUD for booking types. The views didn't exist until this test's
 * commit, and the "has bookings" delete guard queried a `booking_type_id`
 * column that was never migrated -- bookings reference their type through
 * the `booking_type` enum matching `booking_types.slug`.
 */
class BookingTypeManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Workshop '.uniqid(),
            'description' => 'Hands-on group sessions.',
            'icon' => 'calendar',
            'default_duration_minutes' => 90,
            'min_duration_minutes' => 60,
            'max_duration_minutes' => 180,
            'booking_window_days' => 45,
            'cancellation_policy_hours' => 12,
            'is_active' => '1',
            'sort_order' => 3,
        ], $overrides);
    }

    private function makeType(string $slug, array $attributes = []): BookingType
    {
        return BookingType::create(array_merge([
            'name' => $slug,
            'slug' => $slug,
            'default_duration_minutes' => 60,
            'booking_window_days' => 30,
            'cancellation_policy_hours' => 24,
        ], $attributes));
    }

    public function test_index_create_and_edit_pages_render(): void
    {
        $admin = $this->admin();
        $type = $this->makeType('rental');

        $this->actingAs($admin)->get(route('admin.booking-types.index'))
            ->assertOk()
            ->assertSee('Rental')
            ->assertSee(route('admin.booking-types.edit', $type), false);

        $this->actingAs($admin)->get(route('admin.booking-types.create'))->assertOk();

        $this->actingAs($admin)->get(route('admin.booking-types.edit', $type))
            ->assertOk()
            ->assertSee('rental');
    }

    public function test_non_admins_are_forbidden(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);

        $this->actingAs($vendor)->get(route('admin.booking-types.index'))->assertForbidden();
    }

    public function test_admin_can_create_a_booking_type(): void
    {
        $payload = $this->validPayload(['name' => 'Tasting Session']);

        $this->actingAs($this->admin())
            ->post(route('admin.booking-types.store'), $payload)
            ->assertRedirect(route('admin.booking-types.index'));

        $this->assertDatabaseHas('booking_types', [
            'name' => 'Tasting Session',
            'slug' => 'tasting-session',
            'default_duration_minutes' => 90,
            'is_active' => true,
        ]);
    }

    public function test_blank_sort_order_defaults_to_zero_instead_of_failing(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.booking-types.store'), $this->validPayload(['name' => 'Blank Order', 'sort_order' => '']))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, BookingType::where('slug', 'blank-order')->value('sort_order'));
    }

    public function test_a_name_whose_slug_collides_is_rejected(): void
    {
        $this->makeType('class');

        $this->actingAs($this->admin())
            ->post(route('admin.booking-types.store'), $this->validPayload(['name' => 'Class!']))
            ->assertSessionHasErrors('name');
    }

    public function test_default_duration_must_fall_within_min_and_max(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.booking-types.store'), $this->validPayload([
                'min_duration_minutes' => 60,
                'max_duration_minutes' => 120,
                'default_duration_minutes' => 240,
            ]))
            ->assertSessionHasErrors('default_duration_minutes');
    }

    public function test_unchecking_active_on_edit_deactivates_the_type(): void
    {
        $type = $this->makeType('event', ['is_active' => true]);

        // An unchecked checkbox posts nothing, and the controller defaults an
        // absent is_active to true -- so the form must carry a hidden 0
        // fallback, and that fallback must be what update() receives.
        $this->actingAs($this->admin())
            ->get(route('admin.booking-types.edit', $type))
            ->assertSee('<input type="hidden" name="is_active" value="0">', false);

        $this->actingAs($this->admin())
            ->put(route('admin.booking-types.update', $type), $this->validPayload(['name' => 'event', 'is_active' => '0']))
            ->assertRedirect(route('admin.booking-types.index'));

        $this->assertFalse($type->fresh()->is_active);
    }

    public function test_renaming_keeps_the_slug_that_bookings_reference(): void
    {
        $type = $this->makeType('appointment');

        $this->actingAs($this->admin())
            ->put(route('admin.booking-types.update', $type), $this->validPayload(['name' => 'Salon Appointment']))
            ->assertSessionHasNoErrors();

        $type->refresh();
        $this->assertSame('Salon Appointment', $type->name);
        $this->assertSame('appointment', $type->slug);
    }

    public function test_toggle_flips_status(): void
    {
        $type = $this->makeType('consultation', ['is_active' => true]);

        $this->actingAs($this->admin())
            ->post(route('admin.booking-types.toggle', $type))
            ->assertRedirect();

        $this->assertFalse($type->fresh()->is_active);
    }

    public function test_a_type_without_bookings_can_be_deleted(): void
    {
        $type = $this->makeType('reservation');

        $this->actingAs($this->admin())
            ->delete(route('admin.booking-types.destroy', $type))
            ->assertRedirect(route('admin.booking-types.index'));

        $this->assertModelMissing($type);
    }

    public function test_a_type_with_bookings_cannot_be_deleted(): void
    {
        $type = $this->makeType('rental');
        $booking = $this->makeBooking('rental');

        $this->assertTrue($booking->bookingType->is($type));

        $this->actingAs($this->admin())
            ->from(route('admin.booking-types.index'))
            ->delete(route('admin.booking-types.destroy', $type))
            ->assertRedirect(route('admin.booking-types.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($type);
    }

    private function makeBooking(string $bookingType): Booking
    {
        $owner = User::factory()->create();
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid()]);
        $service = SalonService::create([
            'user_id' => $owner->id,
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
            'booking_type' => $bookingType,
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $owner->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes(30),
            'duration_minutes' => 30,
            'price' => 50,
            'status' => 'confirmed',
        ]);
    }
}
