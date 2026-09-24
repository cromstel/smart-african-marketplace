@extends('vendor.layout')

@section('title', 'Edit Pricing Tier')
@section('header', 'Edit Pricing Tier: ' . $salonService->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('vendor.salon-services.pricing-tiers.index', $salonService) }}" class="text-indigo-400 hover:text-indigo-300 inline-flex items-center rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Pricing Tiers
    </a>
</div>

<div class="max-w-4xl">
    <form action="{{ route('vendor.salon-services.pricing-tiers.update', [$salonService, $tier]) }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        @csrf
        @method('PUT')

        @include('vendor.salon-services.pricing-tiers._form', ['tier' => $tier])

        <div class="flex justify-end gap-4 mt-6">
            <a href="{{ route('vendor.salon-services.pricing-tiers.index', $salonService) }}" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500">
                Update Tier
            </button>
        </div>
    </form>
</div>
@endsection
