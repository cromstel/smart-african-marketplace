@php
    $bookingType = $bookingType ?? null;
@endphp

<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
        <input type="text" name="name" value="{{ old('name', $bookingType->name ?? '') }}" required
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from"
               placeholder="e.g., Appointment, Reservation, Rental">
        @error('name')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
        <textarea name="description" rows="3"
                  class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from"
                  placeholder="What kind of booking does this represent?">{{ old('description', $bookingType->description ?? '') }}</textarea>
        @error('description')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Icon</label>
        <input type="text" name="icon" value="{{ old('icon', $bookingType->icon ?? '') }}" maxlength="50"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from"
               placeholder="Optional icon identifier">
        @error('icon')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-gray-500 text-xs mt-1">
            The list page shows a built-in icon for the names Appointment, Reservation, Rental, Consultation, Class
            and Event automatically; this field is for your own reference or a future custom icon.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Default Duration (min) *</label>
            <input type="number" name="default_duration_minutes" value="{{ old('default_duration_minutes', $bookingType->default_duration_minutes ?? 60) }}" min="15" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
            @error('default_duration_minutes')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Minimum Duration (min)</label>
            <input type="number" name="min_duration_minutes" value="{{ old('min_duration_minutes', $bookingType->min_duration_minutes ?? '') }}" min="1"
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
            @error('min_duration_minutes')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Maximum Duration (min)</label>
            <input type="number" name="max_duration_minutes" value="{{ old('max_duration_minutes', $bookingType->max_duration_minutes ?? '') }}" min="15"
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
            @error('max_duration_minutes')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Booking Window (days) *</label>
            <input type="number" name="booking_window_days" value="{{ old('booking_window_days', $bookingType->booking_window_days ?? 30) }}" min="1" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
            @error('booking_window_days')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-gray-500 text-xs mt-1">How far in advance customers can book</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Cancellation Notice (hours) *</label>
            <input type="number" name="cancellation_policy_hours" value="{{ old('cancellation_policy_hours', $bookingType->cancellation_policy_hours ?? 24) }}" min="0" required
                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
            @error('cancellation_policy_hours')
                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-gray-500 text-xs mt-1">Minimum notice required to cancel without penalty</p>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $bookingType->sort_order ?? 0) }}" min="0"
               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-gray-400 focus:border-brand-from">
        @error('sort_order')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
        <p class="text-gray-500 text-xs mt-1">Lower numbers appear first; you can also reorder from the list page</p>
    </div>

    <div>
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bookingType->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded focus:ring-2 focus:ring-gray-400">
            <span class="ml-2 text-gray-300">Active (available for services to use)</span>
        </label>
    </div>
</div>
