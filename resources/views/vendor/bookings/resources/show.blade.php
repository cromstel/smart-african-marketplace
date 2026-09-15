@extends('vendor.layout')

@section('title', $bookingResource->name)
@section('header', 'Resource: ' . $bookingResource->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('vendor.booking-resources.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h3 class="text-2xl font-bold text-white">{{ $bookingResource->name }}</h3>
            <p class="text-gray-400 mt-1 text-sm">{{ ucfirst($bookingResource->resource_type) }}
                @if($bookingResource->sku) &middot; SKU: {{ $bookingResource->sku }} @endif
            </p>
        </div>
        <span class="px-3 py-1 text-xs rounded {{ $bookingResource->is_active ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' }}">
            {{ $bookingResource->is_active ? 'Active' : 'Inactive' }}
        </span>
        <div class="flex gap-2">
            <a href="{{ route('vendor.booking-resources.availability', $bookingResource) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Availability
            </a>
            <a href="{{ route('vendor.booking-resources.statistics', $bookingResource) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Statistics
            </a>
            <a href="{{ route('vendor.booking-resources.edit', $bookingResource) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Edit
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-sm text-gray-400 mb-1">Pricing</h4>
            <p class="text-2xl font-bold text-white">{{ $bookingResource->price_label }}</p>
            <p class="text-gray-500 text-xs mt-1">Min. {{ $bookingResource->minimum_rental_hours }} hour(s) per booking</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-sm text-gray-400 mb-1">Availability</h4>
            <p class="text-2xl font-bold text-white">{{ $bookingResource->quantity_available }} / {{ $bookingResource->quantity_total }}</p>
            <p class="text-gray-500 text-xs mt-1">units available now</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-sm text-gray-400 mb-1">Utilization (30 days)</h4>
            <p class="text-2xl font-bold text-white">{{ $stats['utilization_rate'] }}%</p>
            <p class="text-gray-500 text-xs mt-1">{{ $stats['booking_count'] }} booking(s) &middot; ${{ number_format($stats['total_revenue'], 2) }} revenue</p>
        </div>
    </div>

    @if($bookingResource->description)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-2">Description</h4>
            <p class="text-gray-300 whitespace-pre-line">{{ $bookingResource->description }}</p>
        </div>
    @endif

    @if(!empty($bookingResource->specifications))
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Specifications</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($bookingResource->specifications as $key => $value)
                    <div>
                        <dt class="text-sm text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h4 class="text-lg font-semibold text-white mb-4">Manage</h4>
        <div class="flex flex-wrap gap-4">
            <form method="POST" action="{{ route('vendor.booking-resources.toggle', $bookingResource) }}">
                @csrf
                <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold">
                    {{ $bookingResource->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <form method="POST" action="{{ route('vendor.booking-resources.destroy', $bookingResource) }}"
                  onsubmit="return confirm('Delete this resource? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600/20 hover:bg-red-600/30 text-red-400 px-6 py-3 rounded-lg font-semibold">
                    Delete Resource
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
