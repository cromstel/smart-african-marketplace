@extends('layouts.customer-console')

@section('title', 'Wishlist - Customer Console')

@section('content')
<h2 class="text-lg font-semibold text-white mb-6">My Wishlist</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
    @forelse($items as $item)
    <div class="flex items-center space-x-4 border-b border-gray-700 pb-4 last:border-0">
        <div class="w-16 h-16 bg-gray-700 rounded-lg flex-shrink-0">
            <span class="text-gray-500 text-sm">Item</span>
        </div>
        <div class="flex-1">
            <h4 class="text-white">{{ $item->name }}</h4>
            <p class="text-gray-400 text-sm">${{ number_format($item->price, 2) }}</p>
        </div>
    </div>
    @empty
    <div class="text-center py-8">
        <p class="text-gray-400">Your wishlist is empty.</p>
    </div>
    @endforelse
</div>
@endsection
