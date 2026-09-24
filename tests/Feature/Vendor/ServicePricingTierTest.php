<?php

namespace Tests\Feature\Vendor;

use App\Models\Booking;
use App\Models\Category;
use App\Models\SalonService;
use App\Models\ServicePricingTier;
use App\Models\User;
use App\Services\BookingPricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Vendor\Concerns\MakesIsolatedVendors;
use Tests\TestCase;

/**
 * Pricing tiers for salon services: vendor management (scoped to the owning
 * vendor), which tier the pricing service picks, and that a booking is
 * charged the tiered price.
 *
 * Before this test's commit there was no UI to create tiers, and
 * BookingPricingService::getApplicablePricingTier() queried columns that
 * service_pricing_tiers never had (vendor_id, days_of_week, start_hour, ...),
 * so POST /api/bookings/price threw on every call.
 */
class ServicePricingTierTest extends TestCase
{
    use DatabaseTransactions;
    use MakesIsolatedVendors;

    private function makeService(User $owner, float $price = 100, int $duration = 60): SalonService
    {
        $category = Category::create(['name' => 'Cat '.uniqid(), 'slug' => 'cat-'.uniqid(), 'is_active' => true]);
        $name = 'Service '.uniqid();

        return SalonService::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'description' => 'Pricing tier fixture.',
            'price' => $price,
            'duration_minutes' => $duration,
            'is_active' => true,
        ]);
    }

    private function tier(SalonService $service, array $attributes): ServicePricingTier
    {
        return $service->pricingTiers()->create(array_merge([
            'tier_name' => 'Tier '.uniqid(),
            'price_modifier' => 10,
            'price_modifier_type' => 'percentage',
            'is_active' => true,
        ], $attributes));
    }

    // --- Vendor management --------------------------------------------------

    public function test_vendor_can_list_add_edit_and_delete_tiers_on_their_service(): void
    {
        [$vendor] = $this->makeVendor();
        $service = $this->makeService($vendor);

        $this->actingAs($vendor)
            ->get(route('vendor.salon-services.pricing-tiers.index', $service))
            ->assertOk()
            ->assertSee('No pricing tiers yet');

        $this->actingAs($vendor)
            ->post(route('vendor.salon-services.pricing-tiers.store', $service), [
                'tier_name' => 'Saturday peak',
                'day_of_week' => 6,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'price_modifier_type' => 'percentage',
                'price_modifier' => 20,
                'is_active' => '1',
            ])
            ->assertRedirect(route('vendor.salon-services.pricing-tiers.index', $service))
            ->assertSessionHasNoErrors();

        $tier = $service->pricingTiers()->sole();
        $this->assertSame(6, $tier->day_of_week);

        $this->actingAs($vendor)
            ->get(route('vendor.salon-services.pricing-tiers.index', $service))
            ->assertSee('Saturday peak')
            ->assertSee('$120.00');

        $this->actingAs($vendor)->get(route('vendor.salon-services.pricing-tiers.edit', [$service, $tier]))->assertOk();

        $this->actingAs($vendor)
            ->put(route('vendor.salon-services.pricing-tiers.update', [$service, $tier]), [
                'tier_name' => 'Saturday peak',
                'day_of_week' => 6,
                'price_modifier_type' => 'fixed',
                'price_modifier' => -15,
                'is_active' => '0',
            ])
            ->assertSessionHasNoErrors();

        $tier->refresh();
        $this->assertSame('fixed', $tier->price_modifier_type);
        $this->assertFalse($tier->is_active);
        $this->assertNull($tier->start_time);

        $this->actingAs($vendor)
            ->delete(route('vendor.salon-services.pricing-tiers.destroy', [$service, $tier]))
            ->assertRedirect();
        $this->assertModelMissing($tier);
    }

    public function test_time_window_must_be_complete_and_ordered(): void
    {
        [$vendor] = $this->makeVendor();
        $service = $this->makeService($vendor);
        $url = route('vendor.salon-services.pricing-tiers.store', $service);
        $base = ['tier_name' => 'x', 'price_modifier_type' => 'percentage', 'price_modifier' => 10];

        $this->actingAs($vendor)->post($url, $base + ['start_time' => '10:00'])->assertSessionHasErrors('end_time');
        $this->actingAs($vendor)->post($url, $base + ['start_time' => '10:00', 'end_time' => '09:00'])->assertSessionHasErrors('end_time');
        $this->actingAs($vendor)->post($url, array_merge($base, ['price_modifier' => -150]))->assertSessionHasErrors('price_modifier');

        $this->assertSame(0, $service->pricingTiers()->count());
    }

    public function test_a_vendor_cannot_manage_tiers_on_another_vendors_service(): void
    {
        [$mine] = $this->makeVendor();
        [$them] = $this->makeVendor();
        $theirService = $this->makeService($them);
        $theirTier = $this->tier($theirService, ['tier_name' => 'Theirs']);

        $this->actingAs($mine)->get(route('vendor.salon-services.pricing-tiers.index', $theirService))->assertForbidden();
        $this->actingAs($mine)->get(route('vendor.salon-services.pricing-tiers.edit', [$theirService, $theirTier]))->assertForbidden();

        $this->actingAs($mine)
            ->post(route('vendor.salon-services.pricing-tiers.store', $theirService), [
                'tier_name' => 'Injected', 'price_modifier_type' => 'percentage', 'price_modifier' => -100,
            ])
            ->assertForbidden();

        $this->actingAs($mine)
            ->put(route('vendor.salon-services.pricing-tiers.update', [$theirService, $theirTier]), [
                'tier_name' => 'Hijacked', 'price_modifier_type' => 'percentage', 'price_modifier' => -100,
            ])
            ->assertForbidden();

        $this->actingAs($mine)
            ->delete(route('vendor.salon-services.pricing-tiers.destroy', [$theirService, $theirTier]))
            ->assertForbidden();

        $this->assertSame('Theirs', $theirTier->fresh()->tier_name);
        $this->assertSame(1, $theirService->pricingTiers()->count());
    }

    public function test_another_services_tier_cannot_be_reached_through_my_own_service(): void
    {
        // The route is ->scoped(): a tier id from someone else's service in
        // my service's URL must not resolve at all.
        [$mine] = $this->makeVendor();
        [$them] = $this->makeVendor();
        $myService = $this->makeService($mine);
        $theirTier = $this->tier($this->makeService($them), ['tier_name' => 'Theirs']);

        $this->actingAs($mine)
            ->put(route('vendor.salon-services.pricing-tiers.update', [$myService, $theirTier]), [
                'tier_name' => 'Hijacked', 'price_modifier_type' => 'percentage', 'price_modifier' => -100,
            ])
            ->assertNotFound();

        $this->actingAs($mine)
            ->delete(route('vendor.salon-services.pricing-tiers.destroy', [$myService, $theirTier]))
            ->assertNotFound();

        $this->assertModelExists($theirTier);
        $this->assertSame('Theirs', $theirTier->fresh()->tier_name);
    }

    // --- Tier selection -----------------------------------------------------

    public function test_tier_selection_matches_day_and_time_on_future_dates(): void
    {
        [$vendor] = $this->makeVendor();
        $service = $this->makeService($vendor);
        $pricing = app(BookingPricingService::class);

        $saturday = $this->tier($service, ['day_of_week' => Carbon::SATURDAY]);
        $evenings = $this->tier($service, ['start_time' => '17:00', 'end_time' => '21:00']);
        $saturdayEvening = $this->tier($service, ['day_of_week' => Carbon::SATURDAY, 'start_time' => '17:00', 'end_time' => '21:00']);
        $this->tier($service, ['is_active' => false, 'day_of_week' => Carbon::MONDAY]);

        // Weeks out, so a comparison against "today at 17:00" (the old
        // appliesTo() bug) would give the wrong answer.
        $sat = now()->addWeeks(3)->next(Carbon::SATURDAY);
        $mon = now()->addWeeks(3)->next(Carbon::MONDAY);

        $this->assertTrue($pricing->getApplicablePricingTier($service->id, $sat, $sat->copy()->setTime(18, 0))->is($saturdayEvening));
        $this->assertTrue($pricing->getApplicablePricingTier($service->id, $sat, $sat->copy()->setTime(10, 0))->is($saturday));
        $this->assertTrue($pricing->getApplicablePricingTier($service->id, $mon, $mon->copy()->setTime(18, 0))->is($evenings));
        $this->assertNull($pricing->getApplicablePricingTier($service->id, $mon, $mon->copy()->setTime(21, 0)), 'end time is exclusive');
        $this->assertNull($pricing->getApplicablePricingTier($service->id, $mon, $mon->copy()->setTime(10, 0)), 'inactive tier ignored');
    }

    public function test_minimum_duration_and_zero_floor(): void
    {
        [$vendor] = $this->makeVendor();
        $short = $this->makeService($vendor, price: 30, duration: 30);
        $this->tier($short, ['minimum_duration' => 60, 'price_modifier' => 50]);
        $this->tier($short, ['day_of_week' => Carbon::SUNDAY, 'price_modifier_type' => 'fixed', 'price_modifier' => -50]);
        $pricing = app(BookingPricingService::class);

        $weekday = now()->addWeek()->next(Carbon::TUESDAY)->setTime(10, 0);
        $sunday = now()->addWeek()->next(Carbon::SUNDAY)->setTime(10, 0);

        $this->assertSame(['price' => 30.0, 'tier' => null], $pricing->priceForService($short, $weekday), 'minimum duration not met');
        $this->assertSame(0.0, $pricing->priceForService($short, $sunday)['price'], 'never below zero');
    }

    public function test_price_endpoint_uses_the_service_price_and_tier(): void
    {
        [$vendor] = $this->makeVendor();
        $service = $this->makeService($vendor, price: 80);
        $this->tier($service, ['price_modifier' => 25]);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->postJson(route('api.bookings.price'), [
                'booking_type' => 'appointment',
                'vendor_id' => $vendor->id,
                'service_id' => $service->id,
                'booking_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00',
            ])
            ->assertOk()
            ->assertJsonPath('unit_price', 80)
            ->assertJsonPath('subtotal', 100);
    }

    // --- Booking ------------------------------------------------------------

    public function test_a_booking_is_charged_the_tiered_price_and_customers_see_the_tiers(): void
    {
        [$vendor] = $this->makeVendor();
        $service = $this->makeService($vendor, price: 50, duration: 30);
        $tomorrow = now()->addDay();
        $this->tier($service, ['tier_name' => 'Morning rush', 'day_of_week' => $tomorrow->dayOfWeek, 'start_time' => '09:00', 'end_time' => '12:00', 'price_modifier' => 20]);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('bookings.create', $service))
            ->assertOk()
            ->assertSee('Morning rush')
            ->assertSee('$60.00');

        $this->actingAs($customer)->post(route('bookings.store', $service), [
            'booking_date' => $tomorrow->format('Y-m-d'),
            'booking_time' => '10:00',
        ]);

        $booking = Booking::where('user_id', $customer->id)->where('salon_service_id', $service->id)->sole();
        $this->assertSame('60.00', (string) $booking->price);
    }
}
