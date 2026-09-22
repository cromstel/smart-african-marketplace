@extends('vendor.layout')

@section('title', 'Edit ' . $bookingResource->name)
@section('header', 'Edit Rental Resource')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('vendor.booking-resources.show', $bookingResource) }}" class="text-gray-400 hover:text-white transition" aria-label="Back">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h3 class="text-2xl font-bold text-white">Edit {{ $bookingResource->name }}</h3>
            <p class="text-gray-400 mt-1">Update pricing, availability, or details</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500 rounded-lg p-4">
            <p class="text-red-500 font-semibold mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-red-400">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('vendor.booking-resources.update', $bookingResource) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('vendor.bookings.resources._form')
        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                Save Changes
            </button>
            <a href="{{ route('vendor.booking-resources.show', $bookingResource) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
