@extends('layouts.shop')

@section('title', 'My Addresses')

@section('content')
<x-hero-section
    pageIdentifier="addresses"
    fallbackTitle="Address Book"
    fallbackSubtitle="Manage your saved shipping and billing addresses"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-white">Your Addresses</h2>
        <a href="{{ route('addresses.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
            + Add Address
        </a>
    </div>

    @if($addresses->isEmpty())
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-8 text-center">
            <p class="text-gray-400 mb-4">You haven't saved any addresses yet.</p>
            <a href="{{ route('addresses.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                Add your first address
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($addresses as $address)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <span class="inline-block text-xs uppercase tracking-wide px-2 py-1 rounded bg-gray-700 text-gray-300">
                                {{ ucfirst($address->type) }}
                            </span>
                            @if($address->is_default)
                                <span class="inline-block text-xs uppercase tracking-wide px-2 py-1 rounded bg-indigo-900 text-indigo-300 ml-1">
                                    Default
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-white font-medium">{{ $address->full_name }}</div>
                    @if($address->company)
                        <div class="text-gray-400 text-sm">{{ $address->company }}</div>
                    @endif
                    <div class="text-gray-400 text-sm mt-1">{{ $address->full_address }}</div>
                    @if($address->phone)
                        <div class="text-gray-400 text-sm">{{ $address->phone }}</div>
                    @endif

                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-700">
                        <a href="{{ route('addresses.edit', $address) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">
                            Edit
                        </a>
                        <form action="{{ route('addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Delete this address?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300 text-sm font-medium">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
