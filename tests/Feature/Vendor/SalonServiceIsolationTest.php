<?php

namespace Tests\Feature\Vendor;

use App\Models\Category;
use App\Models\SalonService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Cross-tenant isolation for vendor salon services.
 *
 * SalonService, like Salon, has no `vendor_id` column -- it is owned
 * directly by `user_id`. SalonServiceController::edit()/update()/destroy()
 * check `$salonService->user_id !== auth()->id()` and abort with 403 on an
 * implicitly-bound SalonService.
 */
class SalonServiceIsolationTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    public function test_a_vendor_cannot_edit_another_vendors_salon_service(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirService] = $this->makeVendorWithSalonService();

        $this->actingAs($mine)
            ->get(route('vendor.salon-services.edit', $theirService))
            ->assertForbidden();
    }

    public function test_a_vendor_cannot_update_another_vendors_salon_service(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirService] = $this->makeVendorWithSalonService();
        $originalName = $theirService->name;

        $this->actingAs($mine)
            ->put(route('vendor.salon-services.update', $theirService), [
                'name' => 'Hijacked Service',
                'description' => 'Hijacked',
                'category_id' => $theirService->category_id,
                'price' => 999,
                'duration_minutes' => 30,
            ])
            ->assertForbidden();

        $this->assertSame(
            $originalName,
            $theirService->fresh()->name,
            "A vendor updated another vendor's salon service. This is a cross-tenant write."
        );
    }

    public function test_a_vendor_cannot_destroy_another_vendors_salon_service(): void
    {
        [$mine] = $this->makeVendor();
        [, , $theirService] = $this->makeVendorWithSalonService();

        $this->actingAs($mine)
            ->delete(route('vendor.salon-services.destroy', $theirService))
            ->assertForbidden();

        $this->assertDatabaseHas('salon_services', ['id' => $theirService->id]);
    }

    public function test_a_vendor_can_still_edit_their_own_salon_service(): void
    {
        // Guards against "fixing" isolation by refusing everything.
        [$mine, , $myService] = $this->makeVendorWithSalonService();

        $this->actingAs($mine)
            ->get(route('vendor.salon-services.edit', $myService))
            ->assertOk();
    }

    // --------------------------------------------------------------- Helpers

    /**
     * @return array{0: User, 1: Vendor, 2: SalonService}
     */
    private function makeVendorWithSalonService(): array
    {
        [$user, $vendor] = $this->makeVendor();

        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        $name = 'Service '.uniqid();
        $service = SalonService::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'description' => 'Isolation fixture.',
            'price' => 40.00,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);

        return [$user, $vendor, $service];
    }
}
