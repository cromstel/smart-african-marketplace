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

                @include('public.services._category-filter', [
                    'categoryTypes' => $categoryTypes,
                    'genderTargets' => $genderTargets,
                    'currentCategoryType' => request('category_type'),
                    'currentGenderTarget' => request('gender_target'),
                ])

                <div class="mt-8">
                    @include('public.services._category-tree', [
                        'parentCategories' => $parentCategories,
                        'currentRoute' => request()->route()->getName(),
                    ])
                </div>
            </div>
        </aside>

        <!-- Services Grid -->
        <div class="lg:col-span-9">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    @include('public.services._service-card', ['service' => $service])
                @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
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
