@php
    $bookingType = $bookingType ?? null;
    $inputClass = 'w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-2 focus:ring-brand-from focus:border-brand-from';
    $numberFields = [
        'default_duration_minutes' => ['Default Duration (minutes) *', 15, 60, 'Used when a booking doesn\'t specify its own length. Minimum 15.', true],
        'min_duration_minutes' => ['Minimum Duration (minutes)', 1, null, 'Optional lower bound.', false],
        'max_duration_minutes' => ['Maximum Duration (minutes)', 15, null, 'Optional upper bound.', false],
        'booking_window_days' => ['Booking Window (days) *', 1, 30, 'How far ahead customers can book.', true],
        'cancellation_policy_hours' => ['Free Cancellation Notice (hours) *', 0, 24, 'Cancelling at least this long before the booking earns a full refund.', true],
        'sort_order' => ['Sort Order', 0, 0, 'Lower numbers appear first.', false],
    ];
@endphp

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $bookingType?->name) }}" required maxlength="255"
               class="{{ $inputClass }}" placeholder="e.g. appointment, rental, class">
        @error('name')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
        @if($bookingType)
            <p class="text-gray-500 text-xs mt-1">
                Renaming changes the label only. The slug
                <code class="text-purple-400">{{ $bookingType->slug }}</code>
                stays fixed because existing bookings reference it.
            </p>
        @endif
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
        <textarea id="description" name="description" rows="3" maxlength="2000"
                  class="{{ $inputClass }}">{{ old('description', $bookingType?->description) }}</textarea>
        @error('description')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="icon" class="block text-sm font-medium text-gray-300 mb-2">Icon</label>
        <input type="text" id="icon" name="icon" value="{{ old('icon', $bookingType?->icon) }}" maxlength="50"
               class="{{ $inputClass }}" placeholder="e.g. calendar">
        @error('icon')
            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($numberFields as $field => [$label, $min, $default, $help, $required])
            <div>
                <label for="{{ $field }}" class="block text-sm font-medium text-gray-300 mb-2">{{ $label }}</label>
                <input type="number" id="{{ $field }}" name="{{ $field }}" min="{{ $min }}" @if($required) required @endif
                       value="{{ old($field, $bookingType ? $bookingType->{$field} : $default) }}"
                       class="{{ $inputClass }}">
                @error($field)
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">{{ $help }}</p>
            </div>
        @endforeach
    </div>

    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $bookingType?->is_active ?? true) ? 'checked' : '' }}
                   class="w-4 h-4 text-brand-from bg-gray-700 border-gray-600 rounded focus:ring-2 focus:ring-brand-from">
            <span class="ml-2 text-gray-300">Active (offered to customers)</span>
        </label>
    </div>
</div>
