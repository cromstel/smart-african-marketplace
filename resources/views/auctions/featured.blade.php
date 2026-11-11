@extends('layouts.shop')

@section('title', 'Featured Auctions')

@section('content')
<x-hero-section
    pageIdentifier="auctions-featured"
    fallbackTitle="Featured Auctions"
    fallbackSubtitle="Hand-picked auctions with special items from verified African vendors"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :fallbackStats="[
        ['value' => $featuredAuctions->count(), 'label' => 'Featured Now']
    ]"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Featured Auctions</h2>
            <p class="text-sm text-gray-600">Curated highlights from our live auctions.</p>
        </div>
        @include('auctions._nav-tabs', ['active' => 'featured'])
    </div>

    @if($featuredAuctions->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">No featured auctions right now</h2>
            <p class="text-gray-600 mb-6">Check back soon, or browse all active auctions.</p>
            <a href="{{ route('auctions.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                Browse All Auctions
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredAuctions as $auction)
                @include('auctions._card', ['auction' => $auction])
            @endforeach
        </div>
    @endif
</div>
@endsection
