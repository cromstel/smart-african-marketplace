@extends('admin.layout')

@section('title', 'Shipping Settings')
@section('header', 'Shipping Settings')

@section('content')
<div class="max-w-6xl space-y-6">
    <p class="text-gray-400">Configure global shipping rules, supported carriers, and delivery zones used during checkout.</p>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 text-red-400 px-4 py-3 rounded-lg">
            <p class="font-semibold mb-2">Please fix the following:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.shipping-settings.update') }}" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Global Shipping Rules</h2>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="shipping_enabled" value="1" class="sr-only peer" {{ $settings['shipping_enabled'] ? 'checked' : '' }}>
                <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                <span class="ml-3 text-sm font-medium text-gray-300">Shipping Enabled</span>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Shipping Mode</label>
                <select name="shipping_mode" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="flat" {{ $settings['shipping_mode'] === 'flat' ? 'selected' : '' }}>Flat Fee</option>
                    <option value="zone" {{ $settings['shipping_mode'] === 'zone' ? 'selected' : '' }}>Delivery Zone Based</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Flat Fee (USD)</label>
                <input type="number" name="shipping_flat_fee" min="0" step="0.01" value="{{ $settings['shipping_flat_fee'] }}"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Free Shipping Threshold (USD)</label>
                <input type="number" name="shipping_free_threshold" min="0" step="0.01" value="{{ $settings['shipping_free_threshold'] }}"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                       placeholder="0 disables free shipping">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Default Carrier</label>
                <select name="shipment_default_carrier" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @foreach($settings['shipment_supported_carriers'] as $carrier)
                        <option value="{{ $carrier }}" {{ $settings['shipment_default_carrier'] === $carrier ? 'selected' : '' }}>
                            {{ $carrier }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Supported Carriers</label>
            @php
                $carrierOptions = ['DHL', 'FedEx', 'UPS', 'USPS', 'TNT', 'Aramex', 'DPD', 'GLS', 'Royal Mail', 'Canada Post', 'Australia Post'];
                $carrierOptions = array_values(array_unique(array_merge($carrierOptions, $settings['shipment_supported_carriers'])));
            @endphp
            <select name="shipment_supported_carriers" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">Select supported carrier</option>
                @foreach($carrierOptions as $carrierOption)
                    <option value="{{ $carrierOption }}" {{ ($settings['shipment_supported_carriers'][0] ?? '') === $carrierOption ? 'selected' : '' }}>
                        {{ $carrierOption }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="border-t border-gray-700 pt-6 space-y-4">
            <h3 class="text-lg font-semibold text-white">Shipping Label Provider</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Provider</label>
                    <select name="shipping_label_provider" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="manual" {{ $settings['shipping_label_provider'] === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="shippo" {{ $settings['shipping_label_provider'] === 'shippo' ? 'selected' : '' }}>Shippo</option>
                        <option value="easypost" {{ $settings['shipping_label_provider'] === 'easypost' ? 'selected' : '' }}>EasyPost</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Shippo API Key</label>
                    <input type="password" name="shippo_api_key" value="{{ $settings['shippo_api_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="shippo_live_...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">EasyPost API Key</label>
                    <input type="password" name="easypost_api_key" value="{{ $settings['easypost_api_key'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="EZAK...">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Length</label>
                    <input type="number" name="label_parcel_length" min="0.1" step="0.1" value="{{ $settings['label_parcel_length'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Width</label>
                    <input type="number" name="label_parcel_width" min="0.1" step="0.1" value="{{ $settings['label_parcel_width'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Height</label>
                    <input type="number" name="label_parcel_height" min="0.1" step="0.1" value="{{ $settings['label_parcel_height'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight</label>
                    <input type="number" name="label_parcel_weight" min="0.1" step="0.1" value="{{ $settings['label_parcel_weight'] }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Distance Unit</label>
                    <select name="label_parcel_distance_unit" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="in" {{ $settings['label_parcel_distance_unit'] === 'in' ? 'selected' : '' }}>Inches</option>
                        <option value="cm" {{ $settings['label_parcel_distance_unit'] === 'cm' ? 'selected' : '' }}>Centimeters</option>
                    </select>
                </div>
            </div>

            <div class="w-full md:w-48">
                <label class="block text-sm font-medium text-gray-300 mb-2">Mass Unit</label>
                <select name="label_parcel_mass_unit" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="lb" {{ $settings['label_parcel_mass_unit'] === 'lb' ? 'selected' : '' }}>lb</option>
                    <option value="oz" {{ $settings['label_parcel_mass_unit'] === 'oz' ? 'selected' : '' }}>oz</option>
                    <option value="g" {{ $settings['label_parcel_mass_unit'] === 'g' ? 'selected' : '' }}>g</option>
                    <option value="kg" {{ $settings['label_parcel_mass_unit'] === 'kg' ? 'selected' : '' }}>kg</option>
                </select>
            </div>
        </div>

        <div>
            <button type="submit" class="bg-brand-gradient hover:opacity-90 text-white px-6 py-2 rounded-lg font-medium">
                Save Shipping Settings
            </button>
        </div>
    </form>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-6">
        <h2 class="text-xl font-bold text-white">Delivery Zones</h2>
        <p class="text-gray-400 text-sm">When mode is set to "Delivery Zone Based", shipping fee uses matching zone base fee by city/postal code.</p>

        <form method="POST" action="{{ route('admin.shipping-settings.zones.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 border border-gray-700 rounded-lg p-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-300 mb-1">Zone Name</label>
                <input type="text" name="name" required class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Base Fee (USD)</label>
                <input type="number" name="base_fee" min="0" step="0.01" required class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Cities</label>
                <select name="cities" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                    <option value="">Select city</option>
                    @foreach($cityOptions as $cityOption)
                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Postal Codes</label>
                <select name="postal_codes" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                    <option value="">Select postal code</option>
                    @foreach($postalCodeOptions as $postalCodeOption)
                        <option value="{{ $postalCodeOption }}">{{ $postalCodeOption }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Per KM Rate (optional)</label>
                <input type="number" name="per_km_rate" min="0" step="0.01" value="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Max Distance KM (optional)</label>
                <input type="number" name="max_distance_km" min="0" step="0.01" value="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
            </div>
            <div class="md:col-span-2 flex items-center justify-between">
                <label class="inline-flex items-center text-gray-300 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked class="mr-2 rounded border-gray-600 bg-gray-700">
                    Active Zone
                </label>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Add Zone
                </button>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($zones as $zone)
                <form method="POST" action="{{ route('admin.shipping-settings.zones.update', $zone) }}" class="border border-gray-700 rounded-lg p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Zone Name</label>
                        <input type="text" name="name" value="{{ $zone->name }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Base Fee</label>
                        <input type="number" name="base_fee" min="0" step="0.01" value="{{ $zone->base_fee }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Active</label>
                        <select name="is_active" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                            <option value="1" {{ $zone->is_active ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ ! $zone->is_active ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Cities</label>
                        <select name="cities" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                            <option value="">Select city</option>
                            @foreach($cityOptions as $cityOption)
                                <option value="{{ $cityOption }}" {{ ($zone->cities[0] ?? '') === $cityOption ? 'selected' : '' }}>
                                    {{ $cityOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Postal Codes</label>
                        <select name="postal_codes" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                            <option value="">Select postal code</option>
                            @foreach($postalCodeOptions as $postalCodeOption)
                                <option value="{{ $postalCodeOption }}" {{ ($zone->postal_codes[0] ?? '') === $postalCodeOption ? 'selected' : '' }}>
                                    {{ $postalCodeOption }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Per KM</label>
                            <input type="number" name="per_km_rate" min="0" step="0.01" value="{{ $zone->per_km_rate }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Max KM</label>
                            <input type="number" name="max_distance_km" min="0" step="0.01" value="{{ $zone->max_distance_km }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="md:col-span-3 flex items-center justify-end gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">Update</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.shipping-settings.zones.destroy', $zone) }}" class="text-right -mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-red-400 hover:text-red-300">Delete Zone</button>
                </form>
            @empty
                <p class="text-gray-400">No delivery zones created yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

