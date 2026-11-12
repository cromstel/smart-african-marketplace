@extends('layouts.shop')

@section('title', $stylist->name . ' - Smart African Market')
@section('seoDescription', $stylist->bio ?? "Professional salon stylist offering expert hair styling and beauty services.")
@section('seoKeywords', 'salon stylist, hair stylist, beauty professional, book stylist, ' . $stylist->name)

@section('content')
<x-hero-section
    pageIdentifier="stylists"
    fallbackTitle="$stylist->name"
    fallbackSubtitle="$stylist->salon->name ?? 'Independent Stylist'"
    fallbackImage="/images/salon-banner.avif"
    headingTag="h2"
    
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('stylists.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Stylists
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xl font-bold">
                    {{ substr($stylist->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">{{ $stylist->name }}</h2>
                    <p class="text-gray-400 text-sm">{{ $stylist->salon->name ?? 'Independent' }}</p>
                </div>
            </div>
            <div class="flex gap-6 text-sm text-gray-400">
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Experience</p>
                    <p class="text-white font-semibold">{{ $stylist->years_experience ? $stylist->years_experience.' years' : 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs uppercase tracking-wide">Contact</p>
                    <p class="text-white font-semibold">{{ $stylist->phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        @if($stylist->bio)
            <div class="mt-6 text-gray-300">
                {{ $stylist->bio }}
            </div>
        @endif
    </div>

    <div class="space-y-6">
        <h3 class="text-xl font-semibold text-white">Services by {{ $stylist->name }}</h3>

        @if($stylist->services->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($stylist->services as $service)
                    <div class="bg-gray-800 rounded-lg border border-gray-700 p-5 hover:border-indigo-500 transition">
                        <h4 class="text-lg font-semibold text-white mb-2">{{ $service->name }}</h4>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>
                        <div class="flex items-center justify-between text-sm text-gray-400">
                            <span>${{ number_format($service->price, 2) }}</span>
                            <span>{{ $service->duration_minutes }} mins</span>
                        </div>
                        <a href="{{ route('salon.show', $service) }}" class="mt-4 block text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                            View Service
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-8 text-center text-gray-400">
                No services listed for this stylist yet.
            </div>
        @endif
    </div>
</div>
@endsection

