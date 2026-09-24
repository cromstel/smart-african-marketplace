@php
    $tier = $tier ?? null;
    $inputClass = 'w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500';
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $selectedDay = old('day_of_week', $tier?->day_of_week);
    $selectedType = old('price_modifier_type', $tier?->price_modifier_type ?? 'percentage');
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label for="tier_name" class="block text-sm font-medium text-gray-300 mb-2">Tier name *</label>
        <input type="text" id="tier_name" name="tier_name" required maxlength="100"
               value="{{ old('tier_name', $tier?->tier_name) }}" class="{{ $inputClass }}"
               placeholder="e.g. Weekend peak, Weekday morning">
        @error('tier_name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="day_of_week" class="block text-sm font-medium text-gray-300 mb-2">Day</label>
        <select id="day_of_week" name="day_of_week" class="{{ $inputClass }}">
            <option value="">Every day</option>
            @foreach($days as $index => $day)
                <option value="{{ $index }}" @selected($selectedDay !== null && $selectedDay !== '' && (int) $selectedDay === $index)>{{ $day }}</option>
            @endforeach
        </select>
        @error('day_of_week') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="start_time" class="block text-sm font-medium text-gray-300 mb-2">From</label>
            <input type="time" id="start_time" name="start_time" class="{{ $inputClass }}"
                   value="{{ old('start_time', $tier?->start_time ? substr($tier->start_time, 0, 5) : '') }}">
            @error('start_time') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="end_time" class="block text-sm font-medium text-gray-300 mb-2">Until</label>
            <input type="time" id="end_time" name="end_time" class="{{ $inputClass }}"
                   value="{{ old('end_time', $tier?->end_time ? substr($tier->end_time, 0, 5) : '') }}">
            @error('end_time') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        <p class="col-span-2 text-gray-500 text-xs -mt-2">Leave both empty for all day. Applies to bookings starting in this window.</p>
    </div>

    <div>
        <label for="price_modifier_type" class="block text-sm font-medium text-gray-300 mb-2">Adjustment type *</label>
        <select id="price_modifier_type" name="price_modifier_type" class="{{ $inputClass }}">
            <option value="percentage" @selected($selectedType === 'percentage')>Percentage (%)</option>
            <option value="fixed" @selected($selectedType === 'fixed')>Fixed amount ($)</option>
        </select>
        @error('price_modifier_type') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="price_modifier" class="block text-sm font-medium text-gray-300 mb-2">Adjustment *</label>
        <input type="number" id="price_modifier" name="price_modifier" step="0.01" required
               value="{{ old('price_modifier', $tier?->price_modifier) }}" class="{{ $inputClass }}"
               placeholder="e.g. 20 for +20%, -15 for a discount">
        @error('price_modifier') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        <p class="text-gray-500 text-xs mt-1">Positive raises the price, negative lowers it.</p>
    </div>

    <div>
        <label for="minimum_duration" class="block text-sm font-medium text-gray-300 mb-2">Minimum duration (minutes)</label>
        <input type="number" id="minimum_duration" name="minimum_duration" min="1"
               value="{{ old('minimum_duration', $tier?->minimum_duration) }}" class="{{ $inputClass }}">
        @error('minimum_duration') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
        <p class="text-gray-500 text-xs mt-1">Optional. Only applies when the service is at least this long.</p>
    </div>

    <div class="flex items-end">
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tier?->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-indigo-600 focus:ring-2 focus:ring-indigo-500">
            <span class="ml-2 text-gray-300">Active</span>
        </label>
    </div>
</div>
