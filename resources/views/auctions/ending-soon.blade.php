@extends('layouts.shop')

@section('title', 'Auctions Ending Soon')

@section('content')
<x-hero-section
    pageIdentifier="auctions-ending-soon"
    fallbackTitle="Auctions Ending Soon"
    fallbackSubtitle="Don't miss out! Bid now before these auctions close"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :fallbackStats="[
        ['value' => $auctions->count(), 'label' => 'Closing Soon']
    ]"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Ending Soon</h2>
            <p class="text-sm text-gray-600">Auctions closing within the next 24 hours.</p>
        </div>
        @include('auctions._nav-tabs', ['active' => 'ending-soon'])
    </div>

    @if($auctions->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Nothing ending soon</h2>
            <p class="text-gray-600 mb-6">No auctions are closing within the next 24 hours. Check back later or browse all active auctions.</p>
            <a href="{{ route('auctions.index') }}" class="inline-block bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg">
                Browse All Auctions
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($auctions as $auction)
                @include('auctions._card', ['auction' => $auction])
            @endforeach
        </div>
    @endif
</div>
@endsection
