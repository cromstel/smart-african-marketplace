<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SalonService;
use App\Models\ServicePricingTier;
use Illuminate\Http\Request;

/**
 * Peak / off-peak pricing tiers for a vendor's own salon service.
 *
 * Tiers are scoped to their service by the route (->scoped()), and every
 * action first checks the service belongs to the signed-in vendor --
 * SalonService is owned by user_id, like SalonServiceController checks.
 */
class ServicePricingTierController extends Controller
{
    public function index(SalonService $salonService)
    {
        $this->authorizeService($salonService);

        $tiers = $salonService->pricingTiers()->orderBy('id')->get();

        return view('vendor.salon-services.pricing-tiers.index', [
            'salonService' => $salonService,
            'tiers' => $tiers,
        ]);
    }

    public function store(Request $request, SalonService $salonService)
    {
        $this->authorizeService($salonService);

        $salonService->pricingTiers()->create($this->validated($request));

        return redirect()->route('vendor.salon-services.pricing-tiers.index', $salonService)
            ->with('success', 'Pricing tier added.');
    }

    public function edit(SalonService $salonService, ServicePricingTier $pricingTier)
    {
        $this->authorizeService($salonService);

        return view('vendor.salon-services.pricing-tiers.edit', [
            'salonService' => $salonService,
            'tier' => $pricingTier,
        ]);
    }

    public function update(Request $request, SalonService $salonService, ServicePricingTier $pricingTier)
    {
        $this->authorizeService($salonService);

        $pricingTier->update($this->validated($request));

        return redirect()->route('vendor.salon-services.pricing-tiers.index', $salonService)
            ->with('success', 'Pricing tier updated.');
    }

    public function destroy(SalonService $salonService, ServicePricingTier $pricingTier)
    {
        $this->authorizeService($salonService);

        $pricingTier->delete();

        return redirect()->route('vendor.salon-services.pricing-tiers.index', $salonService)
            ->with('success', 'Pricing tier deleted.');
    }

    private function authorizeService(SalonService $salonService): void
    {
        if ($salonService->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function validated(Request $request): array
    {
        // price_modifier is decimal(5,2); a percentage below -100 would make
        // the price negative (the pricing service floors at zero anyway).
        $modifierRange = $request->input('price_modifier_type') === 'fixed'
            ? 'between:-999.99,999.99'
            : 'between:-100,500';

        $validated = $request->validate([
            'tier_name' => 'required|string|max:100',
            'day_of_week' => 'nullable|integer|between:0,6',
            'start_time' => 'nullable|required_with:end_time|date_format:H:i',
            'end_time' => 'nullable|required_with:start_time|date_format:H:i|after:start_time',
            'price_modifier_type' => 'required|in:percentage,fixed',
            'price_modifier' => 'required|numeric|'.$modifierRange,
            'minimum_duration' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // An omitted optional restriction means "no restriction", so clear
        // it on update rather than silently keeping the old value.
        return array_merge([
            'day_of_week' => null,
            'start_time' => null,
            'end_time' => null,
            'minimum_duration' => null,
        ], $validated);
    }
}
