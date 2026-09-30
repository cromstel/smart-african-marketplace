@extends('vendor.layout')

@section('title', 'Pricing Tiers')
@section('header', 'Pricing Tiers: ' . $salonService->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('vendor.salon-services.index') }}" class="text-indigo-400 hover:text-indigo-300 inline-flex items-center rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Salon Services
    </a>
</div>

<div class="max-w-4xl space-y-6">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h2 class="text-xl font-bold text-white">{{ $salonService->name }}</h2>
        <p class="text-gray-400 mt-1">
            Base price <span class="text-white font-medium">${{ number_format($salonService->price, 2) }}</span>
            for {{ $salonService->duration_minutes }} min. Tiers adjust this price for bookings on a given day or time.
            When several tiers match, the most specific one (day and time, then day, then time) is used.
        </p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900 border-b border-gray-700">
                    <tr>
                        <th scope="col" class="text-left px-6 py-3 text-sm font-semibold text-gray-300">Tier</th>
                        <th scope="col" class="text-left px-6 py-3 text-sm font-semibold text-gray-300">Adjustment</th>
                        <th scope="col" class="text-left px-6 py-3 text-sm font-semibold text-gray-300">Price</th>
                        <th scope="col" class="text-left px-6 py-3 text-sm font-semibold text-gray-300">Status</th>
                        <th scope="col" class="text-right px-6 py-3 text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($tiers as $tier)
                        <tr>
                            <td class="px-6 py-4 text-white">{{ $tier->label }}</td>
                            <td class="px-6 py-4 text-gray-300 whitespace-nowrap">{{ $tier->modifier_display }}</td>
                            <td class="px-6 py-4 text-gray-300 whitespace-nowrap">${{ number_format(max(0, $tier->calculatePrice((float) $salonService->price)), 2) }}</td>
                            <td class="px-6 py-4">
                                @if($tier->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap text-sm">
                                <a href="{{ route('vendor.salon-services.pricing-tiers.edit', [$salonService, $tier]) }}" class="text-indigo-400 hover:text-indigo-300 mr-3 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">Edit</a>
                                <form action="{{ route('vendor.salon-services.pricing-tiers.destroy', [$salonService, $tier]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this pricing tier?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">No pricing tiers yet. Every booking uses the base price.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('vendor.salon-services.pricing-tiers.store', $salonService) }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        @csrf
        <h2 class="text-lg font-bold text-white mb-4">Add a pricing tier</h2>

        @include('vendor.salon-services.pricing-tiers._form')

        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500">
                Add Tier
            </button>
        </div>
    </form>
</div>
@endsection
