@extends('layouts.shop')

@section('title', 'Services - Smart African Market')
@section('seoDescription', 'Browse and book professional services from vetted vendors across beauty, automotive, home, and commercial categories.')
@section('seoKeywords', 'services, beauty, automotive, home, commercial, vendors, booking, Smart African Market')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Services</h1>
        <p class="text-gray-400">
            Discover and book professional services from vetted vendors across Africa.
        </p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Category Sidebar -->
        <aside class="lg:col-span-3">
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mb-8 sticky top-24">
                <h3 class="text-lg font-semibold text-white mb-6">Service Categories</h3>

                <form method="GET" class="space-y-2 mb-6">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="category_type" value="{{ request('category_type') }}">
                    <input type="hidden" name="gender_target" value="{{ request('gender_target') }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Category Type</label>
                        <select name="category_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categoryTypes as $type)
                                <option value="{{ $type }}" {{ request('category_type') === $type ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Gender</label>
                        <select name="gender_target" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">All Genders</option>
                            @foreach($genderTargets as $gender)
                                <option value="{{ $gender }}" {{ request('gender_target') === $gender ? 'selected' : '' }}>
                                    {{ ucfirst($gender) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            Filter
                        </button>
                    </div>
                </form>

                <!-- Hierarchical Category Tree -->
                <div class="space-y-4">
                    @foreach($parentCategories as $parent)
                        <div>
                            <div class="flex items-center justify-between">
                                <a href="{{ route('services.category', $parent->slug) }}"
                                   class="block px-3 py-2 rounded-lg text-lg font-medium {{ request()->routeIs('services.category', $parent->slug) ? 'bg-indigo-600 text-white' : 'text-white hover:bg-gray-700' }}">
                                    {{ $parent->name }}
                                </a>
                                @if($parent->children->count() > 0)
                                    <button type="button" class="ml-2 text-gray-400 hover:text-white" aria-label="Toggle {{ $parent->name }} subcategories">
                                        <svg class="w-4 h-4 transform transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            @if($parent->children->count() > 0)
                                <div class="mt-2 space-y-1 ml-4 border-l-2 border-gray-700 pl-4">
                                    @foreach($parent->children as $child)
                                        <a href="{{ route('services.category', $child->slug) }}"
                                           class="block px-2 py-1.5 rounded text-sm {{ request()->routeIs('services.category', $child->slug) ? 'bg-indigo-600/50 text-indigo-300' : 'text-gray-400 hover:text-white hover:bg-gray-700' }} {{ $child->vendorServices()->count() > 0 ? '' : 'opacity-50' }}">
                                            {{ $child->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- Services Grid -->
        <div class="lg:col-span-9">
            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <article class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden hover:border-indigo-500 transition-all group">
                        <div class="h-48 bg-gray-700 relative">
                            @if($service->image)
                                <img src="{{ asset('storage/'.ltrim($service->image, '/')) }}"
                                     alt="{{ $service->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                            @endif

                            <!-- Service Type Badge -->
                            <span class="absolute top-3 left-3 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-tighter">
                                {{ ucfirst(optional($service->category)->gender_target ?? 'unisex') }}
                            </span>

                            <!-- Featured Badge -->
                            @if($service->is_featured)
                                <span class="absolute top-3 right-3 bg-indigo-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-tighter">
                                    Featured
                                </span>
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors">
                                    {{ $service->name }}
                                </h3>
                                <span class="text-indigo-400 font-semibold">${{ number_format($service->price, 2) }}</span>
                            </div>

                            <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>

                            <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
                                <span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    {{ $service->duration_minutes }} min
                                </span>
                                <span class="text-green-400">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M22 11c0-4.971-3.468-9-7.736-9H9.736C6.268 2 2 6.268 2 11.24c0 2.264.925 4.29 2.312 5.746l2.784 2.784a.75.75 0 101.06-1.06l-2.782-2.782A5.24 5.24 0 019.24 9.24h2.52c4.268 0 7.736-4.029 7.736-8.99zM2 11.24c0-2.775 2.253-5 5.031-5H18.97c2.778 0 5.031 2.225 5.031 5v4.875a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V11.24z" clip-rule="evenodd"></path>
                                    </svg>
                                    Available
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <a href="{{ route('services.show', $service) }}"
                                   class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 rounded-lg text-sm font-medium transition-colors">
                                    View Details
                                </a>
                                <a href="{{ $service->vendor->storefrontUrl() }}"
                                   class="text-gray-400 hover:text-white text-sm transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 6l2.287 6.862M14.5 4.956a1 1 0 011.493 1.493l1.5 7.5a1 1 0 01-1.493 1.493L17 16h-5l-2.287 6.863A1 1 0 019.217 19h-5a1 1 0 011-1v-4a1 1 0 011-1h2.287l5.287-15.863a1 1 0 011.493-1.493z"></path>
                                    </svg>
                                    {{ $service->vendor->business_name }}
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="text-xl font-medium text-white mb-2">No services found</h3>
                        <p class="text-gray-400">Try adjusting your filters or check back later.</p>
                    </div>
                @endforelse
            </div>

            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
