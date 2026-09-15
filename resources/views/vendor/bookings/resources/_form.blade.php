{{-- Shared fields for create.blade.php and edit.blade.php. Expects an
     optional $bookingResource variable (present on edit) to seed defaults. --}}
@php
    $resource = $bookingResource ?? null;
@endphp
<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
        <input type="text" id="name" name="name" required maxlength="255"
               value="{{ old('name', $resource->name ?? '') }}"
               placeholder="e.g. Cargo Van, Projector, Event Hall"
               class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
        <textarea id="description" name="description" rows="3"
                  placeholder="Describe this resource for customers..."
                  class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">{{ old('description', $resource->description ?? '') }}</textarea>
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
        <select id="type" name="type" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500">
            <option value="">Select a type...</option>
            @foreach(['equipment' => 'Equipment', 'vehicle' => 'Vehicle', 'property' => 'Property', 'facility' => 'Facility', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" {{ old('type', $resource->resource_type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
    <h4 class="text-lg font-semibold text-white">Pricing &amp; Availability</h4>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="hourly_rate" class="block text-sm font-medium text-gray-300 mb-2">Hourly Rate *</label>
            <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" required
                   value="{{ old('hourly_rate', $resource->hourly_rate ?? '') }}"
                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label for="daily_rate" class="block text-sm font-medium text-gray-300 mb-2">Daily Rate</label>
            <input type="number" id="daily_rate" name="daily_rate" step="0.01" min="0"
                   value="{{ old('daily_rate', $resource->daily_rate ?? '') }}"
                   placeholder="Optional"
                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label for="quantity_available" class="block text-sm font-medium text-gray-300 mb-2">Quantity Available *</label>
            <input type="number" id="quantity_available" name="quantity_available" min="1" required
                   value="{{ old('quantity_available', $resource->quantity_available ?? 1) }}"
                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">
        </div>
        <div>
            <label for="min_rental_hours" class="block text-sm font-medium text-gray-300 mb-2">Minimum Rental Hours</label>
            <input type="number" id="min_rental_hours" name="min_rental_hours" min="1"
                   value="{{ old('min_rental_hours', $resource->minimum_rental_hours ?? 1) }}"
                   class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-indigo-500">
        </div>
    </div>

    <label class="flex items-center gap-3 mt-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1"
               {{ old('is_active', $resource->is_active ?? true) ? 'checked' : '' }}
               class="w-5 h-5 rounded bg-gray-700 border-gray-600 text-indigo-600 focus:ring-indigo-500">
        <span class="text-sm text-gray-300">Available for booking (active)</span>
    </label>
</div>
