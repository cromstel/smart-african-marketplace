@extends('layouts.shop')

@section('title', 'Services - Smart African Market')
@section('seoDescription', 'Discover and book professional services from vetted vendors across beauty, automotive, home, and commercial categories.')
@section('seoKeywords', 'services, beauty, automotive, home, commercial, vendors, booking, Smart African Market')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-gray-900 to-gray-950 py-20 md:py-28 overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image: url('{{ asset('images/hero-banners/hero-banner.avif') }}'); background-size: cover; background-position: center;"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 tracking-tight">
            Professional <span class="text-transparent bg-gradient-to-r from-indigo-400 to-indigo-600 bg-clip-text">Services</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            Discover and book verified services from vetted vendors across Africa. From beauty and grooming to automotive care, home services, and commercial solutions.
        </p>
        
        <!-- Quick Search -->
        <div class="max-w-4xl mx-auto">
            <form method="GET" action="{{ route('services.index') }}" class="flex flex-col md:flex-row gap-4">
                @csrf
                <input type="hidden" name="category_type" value="{{ $filterParams['category_type'] ?? '' }}">
                <input type="hidden" name="gender_target" value="{{ $filterParams['gender_target'] ?? '' }}">
                
                <div class="flex-1 relative">
                    <input type="text" name="search" 
                           value="{{ $filterParams['search'] ?? '' }}"
                           placeholder="Search for services (e.g., haircut, car wash, painting)..."
                           class="w-full bg-gray-800/50 border border-gray-600 text-white rounded-xl px-5 py-4 text-sm focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/50 transition-all placeholder-gray-500">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                
                <button type="submit" 
                        class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-6 py-4 rounded-lg font-semibold transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Search
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($services->isEmpty() && !$services->currentPage() > 1)
        <!-- Empty State -->
        <div class="text-center py-20">
            <div class="w-20 h-20 bg-gray-800 border border-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">No services found</h3>
            <p class="text-gray-400 max-w-md mx-auto">
                Try adjusting your search terms or filters to find the service you're looking for.
            </p>
            <a href="{{ route('services.index') }}" class="mt-6 inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors">
                Reset Filters
            </a>
        </div>
    @else
        <!-- Results Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">
                    {{ $filterParams['search'] ? 'Search Results for "' . e($filterParams['search']) . '"' : 'All Services' }}
                </h2>
                <p class="text-gray-400 text-sm">
                    {{ $services->total() }} service{{ $services->total() !== 1 ? 's' : '' }} available
                </p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('services.index') }}" class="px-4 py-2 bg-gray-800 border border-gray-700 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
                    Clear All
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-9">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($services as $service)
                        @include('public.services._service-card', ['service' => $service])
                    @empty
                        <!-- handled above -->
                    @endforelse
                </div>

                <!-- Pagination -->
                {{ $services->links() }}
            </div>

            <!-- Sidebar -->
            <aside class="lg:sticky lg:top-24 order-first lg:order-first lg:w-[300px]">
                @include('public.services._sidebar', [
                    'parentCategories' => $parentCategories,
                    'currentCategorySlug' => request()->route('category') ?? null,
                    'currentRouteName' => request()->route()->getName(),
                    'filterParams' => [
                        'search' => $filterParams['search'] ?? null,
                        'category_type' => $filterParams['category_type'] ?? null,
                        'gender_target' => $filterParams['gender_target'] ?? null,
                        'categoryTypes' => $categoryTypes,
                        'genderTargets' => $genderTargets,
                    ],
                ])
            </aside>
        </div>
    @endif
</div>

<script>
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileSidebar = document.querySelector('[data-mobile-sidebar]');
        if (mobileSidebar) {
            const toggleBtn = document.querySelector('[data-sidebar-toggle]');
            const closeBtn = document.querySelector('[data-sidebar-close]');
            const overlay = document.querySelector('[data-sidebar-overlay]');
            
            function openSidebar() {
                mobileSidebar.classList.remove('translate-x-full');
                overlay?.classList.remove('hidden');
            }
            
            function closeSidebar() {
                mobileSidebar.classList.add('translate-x-full');
                overlay?.classList.add('hidden');
            }
            
            toggleBtn?.addEventListener('click', openSidebar);
            closeBtn?.addEventListener('click', closeSidebar);
            overlay?.addEventListener('click', closeSidebar);
        }
    });
</script>

@endsection
