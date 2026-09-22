@extends('admin.layout')

@section('title', 'Booking Calendar')
@section('header', 'Booking Calendar')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h3 class="text-2xl font-bold text-white">Booking Calendar</h3>
        <p class="text-gray-400 mt-1">Overview of bookings for {{ $month->format('F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.bookings.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
           class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Previous
        </a>
        <a href="{{ route('admin.bookings.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
           class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Next
        </a>
    </div>
</div>

@php
    $start = $month->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::SUNDAY);
    $end = $month->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
    $days = [];
    $current = $start->copy();
    while ($current->lte($end)) {
        $days[] = $current->copy();
        $current->addDay();
    }
@endphp

<div class="grid grid-cols-7 gap-2 text-xs text-gray-400 mb-2">
    <div>Sun</div>
    <div>Mon</div>
    <div>Tue</div>
    <div>Wed</div>
    <div>Thu</div>
    <div>Fri</div>
    <div>Sat</div>
</div>

<div class="grid grid-cols-7 gap-2">
    @foreach($days as $day)
        @php
            $dateKey = $day->toDateString();
            $isCurrentMonth = $day->month === $month->month;
            $bookings = $bookingsByDate[$dateKey] ?? collect();
        @endphp
        <div class="min-h-[140px] rounded-lg border border-gray-700 p-2 {{ $isCurrentMonth ? 'bg-gray-800' : 'bg-gray-900/60' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs {{ $isCurrentMonth ? 'text-white' : 'text-gray-400' }}">{{ $day->format('j') }}</span>
                @if($bookings->count())
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-600 text-white">
                        {{ $bookings->count() }}
                    </span>
                @endif
            </div>

            <div class="space-y-1">
                @foreach($bookings->take(3) as $booking)
                    <div class="bg-gray-900 border border-gray-700 rounded px-2 py-1">
                        <p class="text-[10px] text-gray-300">{{ \Carbon\Carbon::parse($booking->booking_time)->format('g:i A') }}</p>
                        <p class="text-xs text-white truncate">{{ $booking->salonService->name }}</p>
                        <p class="text-[10px] text-gray-400 truncate">{{ $booking->user->name }}</p>
                    </div>
                @endforeach
                @if($bookings->count() > 3)
                    <p class="text-[10px] text-gray-400">+{{ $bookings->count() - 3 }} more</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection

