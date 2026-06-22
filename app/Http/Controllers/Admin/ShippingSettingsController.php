<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\DeliveryZone;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingSettingsController extends Controller
{
    public function index(): View
    {
        $supportedCarriers = (array) Setting::get('shipment_supported_carriers', ['DHL', 'FedEx', 'UPS', 'USPS', 'Aramex']);
        if ($supportedCarriers === []) {
            $supportedCarriers = ['DHL', 'FedEx', 'UPS', 'USPS', 'Aramex'];
        }

        $settings = [
            'shipping_enabled' => (bool) Setting::get('shipping_enabled', true),
            'shipping_mode' => (string) Setting::get('shipping_mode', 'flat'),
            'shipping_flat_fee' => (float) Setting::get('shipping_flat_fee', 0),
            'shipping_free_threshold' => (float) Setting::get('shipping_free_threshold', 0),
            'shipment_default_carrier' => (string) Setting::get('shipment_default_carrier', 'DHL'),
            'shipment_supported_carriers' => $supportedCarriers,
            'shipping_label_provider' => (string) Setting::get('shipping_label_provider', 'manual'),
            'shippo_api_key' => (string) Setting::get('shippo_api_key', ''),
            'easypost_api_key' => (string) Setting::get('easypost_api_key', ''),
            'label_parcel_length' => (float) Setting::get('label_parcel_length', 10),
            'label_parcel_width' => (float) Setting::get('label_parcel_width', 8),
            'label_parcel_height' => (float) Setting::get('label_parcel_height', 4),
            'label_parcel_weight' => (float) Setting::get('label_parcel_weight', 2),
            'label_parcel_distance_unit' => (string) Setting::get('label_parcel_distance_unit', 'in'),
            'label_parcel_mass_unit' => (string) Setting::get('label_parcel_mass_unit', 'lb'),
        ];

        $zones = DeliveryZone::query()->orderBy('name')->get();
        $cityOptions = $this->cityOptions($zones);
        $postalCodeOptions = $this->postalCodeOptions($zones);

        return view('admin.shipping-settings.index', compact('settings', 'zones', 'cityOptions', 'postalCodeOptions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_enabled' => 'nullable|boolean',
            'shipping_mode' => 'required|in:flat,zone',
            'shipping_flat_fee' => 'required|numeric|min:0',
            'shipping_free_threshold' => 'nullable|numeric|min:0',
            'shipment_default_carrier' => 'required|string|max:100',
            'shipment_supported_carriers' => 'nullable',
            'shipping_label_provider' => 'required|in:manual,shippo,easypost',
            'shippo_api_key' => 'nullable|string',
            'easypost_api_key' => 'nullable|string',
            'label_parcel_length' => 'required|numeric|min:0.1',
            'label_parcel_width' => 'required|numeric|min:0.1',
            'label_parcel_height' => 'required|numeric|min:0.1',
            'label_parcel_weight' => 'required|numeric|min:0.1',
            'label_parcel_distance_unit' => 'required|in:in,cm',
            'label_parcel_mass_unit' => 'required|in:lb,oz,g,kg',
        ]);

        $supportedCarriers = $this->normalizeList($validated['shipment_supported_carriers'] ?? []);

        if ($supportedCarriers === []) {
            $supportedCarriers = ['DHL', 'FedEx', 'UPS', 'USPS', 'Aramex'];
        }
        if (! in_array($validated['shipment_default_carrier'], $supportedCarriers, true)) {
            $supportedCarriers[] = $validated['shipment_default_carrier'];
        }

        Setting::set('shipping_enabled', $request->boolean('shipping_enabled'));
        Setting::set('shipping_mode', $validated['shipping_mode']);
        Setting::set('shipping_flat_fee', (float) $validated['shipping_flat_fee'], 'float');
        Setting::set('shipping_free_threshold', (float) ($validated['shipping_free_threshold'] ?? 0), 'float');
        Setting::set('shipment_default_carrier', $validated['shipment_default_carrier']);
        Setting::set('shipment_supported_carriers', $supportedCarriers);
        Setting::set('shipping_label_provider', $validated['shipping_label_provider']);
        Setting::set('shippo_api_key', $validated['shippo_api_key'] ?? '');
        Setting::set('easypost_api_key', $validated['easypost_api_key'] ?? '');
        Setting::set('label_parcel_length', (float) $validated['label_parcel_length'], 'float');
        Setting::set('label_parcel_width', (float) $validated['label_parcel_width'], 'float');
        Setting::set('label_parcel_height', (float) $validated['label_parcel_height'], 'float');
        Setting::set('label_parcel_weight', (float) $validated['label_parcel_weight'], 'float');
        Setting::set('label_parcel_distance_unit', $validated['label_parcel_distance_unit']);
        Setting::set('label_parcel_mass_unit', $validated['label_parcel_mass_unit']);

        return redirect()->route('admin.shipping-settings.index')->with('success', 'Shipping settings updated successfully.');
    }

    public function storeZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cities' => 'nullable',
            'cities.*' => 'nullable|string|max:120',
            'postal_codes' => 'nullable',
            'postal_codes.*' => 'nullable|string|max:40',
            'base_fee' => 'required|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'max_distance_km' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        DeliveryZone::create([
            'name' => $validated['name'],
            'cities' => $this->normalizeList($request->input('cities')),
            'postal_codes' => $this->normalizeList($request->input('postal_codes')),
            'base_fee' => (float) $validated['base_fee'],
            'per_km_rate' => (float) ($validated['per_km_rate'] ?? 0),
            'max_distance_km' => (float) ($validated['max_distance_km'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.shipping-settings.index')->with('success', 'Delivery zone created.');
    }

    public function updateZone(Request $request, DeliveryZone $zone): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cities' => 'nullable',
            'cities.*' => 'nullable|string|max:120',
            'postal_codes' => 'nullable',
            'postal_codes.*' => 'nullable|string|max:40',
            'base_fee' => 'required|numeric|min:0',
            'per_km_rate' => 'nullable|numeric|min:0',
            'max_distance_km' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $zone->update([
            'name' => $validated['name'],
            'cities' => $this->normalizeList($request->input('cities')),
            'postal_codes' => $this->normalizeList($request->input('postal_codes')),
            'base_fee' => (float) $validated['base_fee'],
            'per_km_rate' => (float) ($validated['per_km_rate'] ?? 0),
            'max_distance_km' => (float) ($validated['max_distance_km'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.shipping-settings.index')->with('success', 'Delivery zone updated.');
    }

    public function destroyZone(DeliveryZone $zone): RedirectResponse
    {
        $zone->delete();

        return redirect()->route('admin.shipping-settings.index')->with('success', 'Delivery zone removed.');
    }

    /**
     * @return array<int, string>
     */
    private function normalizeList(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (is_string($value)) {
            return collect(preg_split('/[\r\n,]+/', $value))
                ->map(fn ($item) => trim((string) $item))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function cityOptions($zones): array
    {
        $addressCities = Address::query()
            ->whereNotNull('city')
            ->pluck('city')
            ->map(fn ($city) => trim((string) $city))
            ->filter()
            ->all();

        $zoneCities = collect($zones)
            ->flatMap(fn (DeliveryZone $zone) => $zone->cities ?? [])
            ->map(fn ($city) => trim((string) $city))
            ->filter()
            ->all();

        return collect(array_merge($addressCities, $zoneCities))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function postalCodeOptions($zones): array
    {
        $addressPostalCodes = Address::query()
            ->whereNotNull('postal_code')
            ->pluck('postal_code')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();

        $zonePostalCodes = collect($zones)
            ->flatMap(fn (DeliveryZone $zone) => $zone->postal_codes ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->all();

        return collect(array_merge($addressPostalCodes, $zonePostalCodes))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
