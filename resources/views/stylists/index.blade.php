@extends('layouts.shop')

@section('title', 'Stylists')

@section('content')
<x-hero-section
    pageIdentifier="stylists"
    fallbackTitle="Salon Stylists"
    fallbackSubtitle="Meet the professionals behind the services"
    fallbackImage="/images/salon-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6 bg-gray-800 rounded-lg border border-gray-700 p-4">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search stylists by name or bio..."
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">
                Search
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('stylists.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Clear
                </a>
            @endif
        </form>
    </div>

    @if($stylists->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($stylists as $stylist)
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 hover:border-indigo-500 transition">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                            {{ substr($stylist->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">{{ $stylist->name }}</h3>
                            <p class="text-gray-400 text-sm">{{ $stylist->salon->name ?? 'Independent' }}</p>
                        </div>
                    </div>

                    <p class="text-gray-400 text-sm mb-4 line-clamp-3">
                        {{ $stylist->bio ?: 'Experienced stylist ready to help you look your best.' }}
                    </p>

                    <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                        <span>{{ $stylist->years_experience ? $stylist->years_experience.' yrs experience' : 'Experience N/A' }}</span>
                        <span>{{ $stylist->email ?? 'Email not listed' }}</span>
                    </div>

                    <a href="{{ route('stylists.show', $stylist) }}" class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                        View Profile
                    </a>
                </div>
            @endforeach
        </div>

        @if($stylists->hasPages())
            <div class="mt-8">
                {{ $stylists->links() }}
            </div>
        @endif
    @else
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
            <p class="text-gray-400 text-lg">No stylists found.</p>
        </div>
    @endif
</div>
@endsection

