<?php

namespace App\Services\Shipping;

use App\Models\Address;
use App\Models\DeliveryZone;
use App\Models\Setting;

class ShippingCalculatorService
{
    /**
     * @return array{cost: float, mode: string, zone: ?DeliveryZone}
     */
    public function calculate(float $subtotal, ?Address $shippingAddress): array
    {
        $shippingEnabled = (bool) Setting::get('shipping_enabled', true);
        if (! $shippingEnabled) {
            return ['cost' => 0.0, 'mode' => 'disabled', 'zone' => null];
        }

        $freeShippingThreshold = (float) Setting::get('shipping_free_threshold', 0);
        if ($freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold) {
            return ['cost' => 0.0, 'mode' => 'free_threshold', 'zone' => null];
        }

        $flatFee = (float) Setting::get('shipping_flat_fee', 0);
        $mode = (string) Setting::get('shipping_mode', 'flat');
        if ($mode !== 'zone') {
            return ['cost' => max(0, $flatFee), 'mode' => 'flat', 'zone' => null];
        }

        $zone = $this->matchZone($shippingAddress);
        if (! $zone) {
            return ['cost' => max(0, $flatFee), 'mode' => 'zone_fallback_flat', 'zone' => null];
        }

        return ['cost' => max(0, (float) $zone->base_fee), 'mode' => 'zone', 'zone' => $zone];
    }

    private function matchZone(?Address $shippingAddress): ?DeliveryZone
    {
        if (! $shippingAddress) {
            return null;
        }

        $city = mb_strtolower(trim((string) $shippingAddress->city));
        $postalCode = mb_strtolower(trim((string) $shippingAddress->postal_code));

        $zones = DeliveryZone::query()
            ->active()
            ->orderBy('name')
            ->get();

        foreach ($zones as $zone) {
            $cities = collect($zone->cities ?? [])
                ->filter()
                ->map(fn ($value) => mb_strtolower(trim((string) $value)))
                ->values();

            $postalCodes = collect($zone->postal_codes ?? [])
                ->filter()
                ->map(fn ($value) => mb_strtolower(trim((string) $value)))
                ->values();

            if ($city !== '' && $cities->contains($city)) {
                return $zone;
            }

            if ($postalCode !== '' && $postalCodes->contains($postalCode)) {
                return $zone;
            }
        }

        return null;
    }
}
