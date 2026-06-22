@extends('vendor.layout')

@section('title', 'Availability Settings')
@section('header', 'Availability Settings')

@php
    $days = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];
@endphp

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Add Availability</h3>
        <p class="text-sm text-gray-400 mb-4">Define your working hours and slot settings for each day.</p>

        <form method="POST" action="{{ route('vendor.settings.availability.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-300 mb-2">Day of Week</label>
                <select name="day_of_week" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                    @foreach($days as $dayValue => $dayLabel)
                        <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Start Time</label>
                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">End Time</label>
                <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Slot Interval (min)</label>
                <input type="number" name="slot_interval_minutes" min="10" max="240" value="{{ old('slot_interval_minutes', 30) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Lead Time (min)</label>
                <input type="number" name="min_lead_time_minutes" min="0" max="1440" value="{{ old('min_lead_time_minutes', 60) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Buffer (min)</label>
                <input type="number" name="buffer_minutes" min="0" max="240" value="{{ old('buffer_minutes', 0) }}"
                       class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
            </div>
            <div class="md:col-span-2 flex items-center space-x-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded bg-gray-700 border-gray-600" checked>
                <label for="is_active" class="text-sm text-gray-300">Active</label>
            </div>
            <div class="md:col-span-6">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Add Availability
                </button>
            </div>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="text-lg font-semibold text-white">Current Availability</h3>
        </div>
        <div class="divide-y divide-gray-700">
            @forelse($availabilities as $availability)
                <div class="p-4 lg:grid lg:grid-cols-8 gap-4 items-center">
                    <form method="POST" action="{{ route('vendor.settings.availability.update', $availability) }}" class="grid grid-cols-1 lg:grid-cols-7 gap-4 items-center lg:col-span-7">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Day</label>
                            <select name="day_of_week" class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                                @foreach($days as $dayValue => $dayLabel)
                                    <option value="{{ $dayValue }}" {{ $availability->day_of_week === $dayValue ? 'selected' : '' }}>
                                        {{ $dayLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Start</label>
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($availability->start_time)->format('H:i') }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">End</label>
                            <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($availability->end_time)->format('H:i') }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Interval</label>
                            <input type="number" name="slot_interval_minutes" min="10" max="240" value="{{ $availability->slot_interval_minutes }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Lead</label>
                            <input type="number" name="min_lead_time_minutes" min="0" max="1440" value="{{ $availability->min_lead_time_minutes }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Buffer</label>
                            <input type="number" name="buffer_minutes" min="0" max="240" value="{{ $availability->buffer_minutes }}"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white text-sm">
                        </div>
                        <div class="flex items-center space-x-3">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" class="rounded bg-gray-700 border-gray-600"
                                       {{ $availability->is_active ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-300">Active</span>
                            </label>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm">
                                Update
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('vendor.settings.availability.destroy', $availability) }}" class="lg:col-span-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                            Delete
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-6 py-6 text-center text-gray-400">
                    No availability entries yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

