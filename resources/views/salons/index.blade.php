@extends('layouts.app')

@section('title', 'Salon Services')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Salon Services</h1>
        <p class="text-gray-400">Book professional salon services</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <aside class="lg:col-span-1">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 sticky top-4">
                <h3 class="text-lg font-bold text-white mb-4">Filter Services</h3>

                <form method="GET" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search services..."
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                        <select name="category" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->salon_services_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Sort By</label>
                        <select name="sort" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm">
                            <option value="">Latest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="duration" {{ request('sort') == 'duration' ? 'selected' : '' }}>Duration</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm">
                            Apply
                        </button>
                        @if(request()->hasAny(['search', 'category', 'sort']))
                            <a href="{{ route('salons.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </aside>

        <div class="lg:col-span-3">
            @if($services->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($services as $service)
                        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden hover:border-indigo-500 transition-colors">
                            @if($service->image)
                                <picture>
                                    @php
                                        $salonIdxWebpPath = str_replace(pathinfo($service->image, PATHINFO_EXTENSION), 'webp', $service->image);
                                    @endphp
                                    @if(Storage::disk('public')->exists($salonIdxWebpPath))
                                        <source srcset="{{ asset('storage/' . $salonIdxWebpPath) }}" type="image/webp">
                                    @endif
                                    <img src="{{ Storage::url($service->image) }}" alt="{{ $service->name }}" class="w-full h-48 object-cover" loading="lazy">
                                </picture>
                            @else
                                <div class="w-full h-48 bg-gray-700 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-bold text-white">{{ $service->name }}</h3>
                                    <span class="px-2 py-1 text-xs rounded bg-indigo-900 text-indigo-300">
                                        {{ $service->category->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>

                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <p class="text-2xl font-bold text-white">${{ number_format($service->price, 2) }}</p>
                                        <p class="text-xs text-gray-500">{{ $service->duration_minutes }} minutes</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">By</p>
                                        <p class="text-sm text-white">{{ $service->user->name ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('salons.show', $service) }}" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white text-center px-4 py-2 rounded-lg text-sm">
                                        View Details
                                    </a>
                                    @auth
                                        <a href="{{ route('bookings.create', $service) }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center px-4 py-2 rounded-lg text-sm">
                                            Book Now
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center px-4 py-2 rounded-lg text-sm">
                                            Book Now
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($services->hasPages())
                    <div class="mt-8">
                        {{ $services->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="bg-gray-800 rounded-lg border border-gray-700 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-400 text-lg">No salon services found</p>
                    <p class="text-gray-500 text-sm mt-2">Try adjusting your filters</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
