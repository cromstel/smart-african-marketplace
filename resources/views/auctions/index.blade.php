@extends('layouts.shop')

@section('title', 'Auctions')

@section('content')
<x-hero-section
    pageIdentifier="auctions"
    fallbackTitle="Auctions"
    fallbackSubtitle="Discover unique items through our live bidding system"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :fallbackStats="[
        ['value' => $auctions->total(), 'label' => 'Active Auctions']
    ]"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <form action="{{ route('auctions.search') }}" method="GET" class="flex w-full md:w-auto">
            <input type="text" name="q" class="flex-1 md:w-64 border border-gray-300 rounded-l-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-600 focus:border-orange-500" placeholder="Search auctions...">
            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-r-lg text-sm font-semibold transition-colors duration-200">
                Search
            </button>
        </form>
        @include('auctions._nav-tabs', ['active' => 'index'])
    </div>

    @if($auctions->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m0 0L3 9m6-6l6 6"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No active auctions</h2>
            <p class="text-gray-600 mb-6">There are no active auctions at the moment. Check back later!</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                Browse Products
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($auctions as $auction)
                @include('auctions._card', ['auction' => $auction])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $auctions->links() }}
        </div>
    @endif
</div>
@endsection
