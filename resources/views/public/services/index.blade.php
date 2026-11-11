@extends('layouts.shop')

@section('title', 'Services - Smart African Market')
@section('seoDescription', 'Discover and book professional services from vetted vendors across beauty, automotive, home, and commercial categories.')
@section('seoKeywords', 'services, beauty, automotive, home, commercial, vendors, booking, Smart African Market')

@section('content')
 {{-- Hero Section --}}
 @php
     $filterParams = [
         'search' => request('search'),
         'category_type' => request('category_type'),
         'gender_target' => request('gender_target'),
     ];
     $fallbackStats = [
         ['value' => $services->total() . '+', 'label' => 'Services Available'],
         ['value' => $parentCategories->count() . '+', 'label' => 'Categories'],
         ['value' => '100%', 'label' => 'Professional Vendors']
     ];
 @endphp

 <x-hero-section
     pageIdentifier="services"
     heightPreset="standard"
     heading-tag="h1"
     :fallbackTitle="__('Services')"
     :fallbackSubtitle="__('Discover and book professional services from vetted vendors across beauty, automotive, home, and commercial categories.')"
     fallbackImage="/images/hero-banners/salon-banner.avif"
     :fallbackStats="$fallbackStats"
     :animated="true"
 />

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
