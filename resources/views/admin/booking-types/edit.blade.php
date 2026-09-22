@extends('admin.layout')

@section('title', 'Edit Booking Type')
@section('header', 'Edit Booking Type')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.booking-types.index') }}" class="text-brand-from hover:text-orange-300 inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Booking Types
    </a>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.booking-types.update', $bookingType) }}" method="POST" class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        @csrf
        @method('PUT')
        @include('admin.booking-types._form')

        <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-700">
            <a href="{{ route('admin.booking-types.index') }}" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
