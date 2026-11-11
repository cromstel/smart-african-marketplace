@extends('layouts.shop')

@section('title', 'Book ' . $resource->name)

@section('content')
<x-hero-section
    pageIdentifier="resource-bookings"
    :fallbackTitle="'Reserve ' . $resource->name"
    fallbackSubtitle="Choose your dates and quantity"
    fallbackImage="/images/booking-banner.avif"
    headingTag="h2"
    
/>

<div class="container mx-auto px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('booking-types.show', 'rental') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            &larr; Back to Rentals
        </a>
    </div>

    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6">
                <h2 class="text-2xl font-bold text-white mb-1">{{ $resource->name }}</h2>
                <p class="text-gray-400 text-sm mb-4">{{ $resource->vendor->business_name ?? 'N/A' }}</p>

                @if($resource->description)
                    <p class="text-gray-300 mb-6">{{ $resource->description }}</p>
                @endif

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500 rounded-lg p-4 mb-6">
                        <ul class="list-disc list-inside text-red-400 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-500/10 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('resource-bookings.store', $resource) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Start Date</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                   min="{{ now()->addDay()->toDateString() }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                   min="{{ now()->addDay()->toDateString() }}"
                                   class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Quantity</label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1"
                               max="{{ $resource->quantity_available ?? $resource->quantity_total }}" required
                               class="w-32 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    @if(count($unavailableDates) > 0)
                        <p class="text-xs text-gray-500">
                            Fully booked on: {{ collect($unavailableDates)->sort()->take(10)->implode(', ') }}{{ count($unavailableDates) > 10 ? ', ...' : '' }}
                        </p>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="customer_notes" rows="3"
                                  class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('customer_notes') }}</textarea>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        Request Booking
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 h-fit">
            <h3 class="text-lg font-semibold text-white mb-4">Pricing</h3>
            <p class="text-3xl font-bold text-white mb-1">{{ $resource->price_label }}</p>
            <p class="text-gray-400 text-sm">Minimum {{ $resource->minimum_rental_hours }} hour(s)</p>
            <p class="text-gray-500 text-xs mt-4">Final price is calculated from your selected dates and quantity, and shown once your request is submitted.</p>
        </div>
    </div>
</div>
@endsection
