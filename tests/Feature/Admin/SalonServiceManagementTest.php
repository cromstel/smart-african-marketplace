<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Admin\SalonServiceController::store() used to force
 * `$validated['user_id'] = auth()->id()` -- every salon service created
 * through the admin panel was owned by the admin's own account, not any
 * vendor, even though this same controller's index view renders
 * `$service->user->name` as the "Vendor" column and the vendor-facing
 * controller treats `salon_services.user_id` as the ownership column.
 * Fixed to accept an explicit vendor selection instead.
 */
class SalonServiceManagementTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_admin_creating_a_salon_service_assigns_it_to_the_selected_vendor_not_the_admin(): void
    {
        $admin = User::factory()->admin()->create();
        [$vendorUser] = $this->makeVendor();
        $category = $this->makeCategory();

        $response = $this->actingAs($admin)->post(route('admin.salon-services.store'), [
            'name' => 'Admin-Created Cut',
            'description' => 'Created via the admin panel.',
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'price' => 45,
            'duration_minutes' => 30,
        ]);

        $response->assertRedirect(route('admin.salon-services.index'));

        $service = SalonService::where('name', 'Admin-Created Cut')->firstOrFail();

        $this->assertSame(
            $vendorUser->id,
            $service->user_id,
            'The salon service was assigned to the wrong owner -- expected the selected vendor, not whoever submitted the form.'
        );
        $this->assertNotSame($admin->id, $service->user_id);
    }

    public function test_admin_cannot_create_a_salon_service_without_selecting_a_vendor(): void
    {
        $admin = User::factory()->admin()->create();
        $category = $this->makeCategory();

        $this->actingAs($admin)->post(route('admin.salon-services.store'), [
            'name' => 'No Vendor Cut',
            'description' => 'Missing vendor.',
            'category_id' => $category->id,
            'price' => 45,
            'duration_minutes' => 30,
        ])->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('salon_services', ['name' => 'No Vendor Cut']);
    }

    public function test_admin_cannot_assign_a_salon_service_to_a_non_approved_vendor(): void
    {
        $admin = User::factory()->admin()->create();
        $pendingVendorUser = User::factory()->vendor()->create();
        Vendor::create([
            'user_id' => $pendingVendorUser->id,
            'business_name' => 'Pending Vendor '.uniqid(),
            'slug' => 'pending-vendor-'.uniqid(),
            'business_email' => 'pending'.uniqid().'@example.com',
            'business_phone' => '+15550000000',
            'business_address' => '1 Test Street',
            'status' => 'pending',
            'is_active' => true,
        ]);
        $category = $this->makeCategory();

        $this->actingAs($admin)->post(route('admin.salon-services.store'), [
            'name' => 'Pending Vendor Cut',
            'description' => 'Vendor not yet approved.',
            'user_id' => $pendingVendorUser->id,
            'category_id' => $category->id,
            'price' => 45,
            'duration_minutes' => 30,
        ])->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('salon_services', ['name' => 'Pending Vendor Cut']);
    }

    public function test_admin_cannot_delete_a_salon_service_with_existing_bookings(): void
    {
        [$vendorUser] = $this->makeVendor();
        $service = $this->makeSalonService($vendorUser);
        $this->makeBooking($vendorUser, $service);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.salon-services.destroy', $service))
            ->assertRedirect();

        $this->assertDatabaseHas('salon_services', ['id' => $service->id]);
    }

    public function test_admin_can_delete_a_salon_service_without_bookings(): void
    {
        [$vendorUser] = $this->makeVendor();
        $service = $this->makeSalonService($vendorUser);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.salon-services.destroy', $service))
            ->assertRedirect(route('admin.salon-services.index'));

        $this->assertDatabaseMissing('salon_services', ['id' => $service->id]);
    }

    // --------------------------------------------------------------- Helpers

    private function makeCategory(): Category
    {
        return Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeSalonService(User $vendorUser): SalonService
    {
        $category = $this->makeCategory();
        $name = 'Service '.uniqid();

        return SalonService::create([
            'user_id' => $vendorUser->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'description' => 'Fixture service.',
            'price' => 40.00,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }

    private function makeBooking(User $vendorUser, SalonService $service): Booking
    {
        $customer = User::factory()->create();

        return Booking::create([
            'booking_number' => 'BK-'.strtoupper(uniqid()),
            'user_id' => $customer->id,
            'salon_service_id' => $service->id,
            'salon_owner_id' => $vendorUser->id,
            'booking_date' => now()->addDay(),
            'booking_end_date' => now()->addDay()->addMinutes($service->duration_minutes),
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'status' => 'pending',
        ]);
    }
}
