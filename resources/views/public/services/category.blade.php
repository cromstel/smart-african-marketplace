@extends('layouts.shop')

@section('title', '{{ $category->name }} Services - Smart African Market')
@section('seoDescription', 'Browse {{ $category->name }} services from vetted vendors. Book appointments online.')
@section('seoKeywords', '{{ $category->name }}, services, vendors, booking, Smart African Market')

@section('content')
 {{-- Category Hero --}}
 <nav aria-label="Breadcrumb" class="flex items-center gap-2 mb-6 text-sm text-gray-400 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
     <li>
         <a href="{{ route('services.index') }}" class="hover:text-white transition-colors">
             Services
         </a>
         <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
         </svg>
     </li>
     @forelse($breadcrumb as $index => $crumb)
         @if($loop->last)
             <span class="text-white font-medium">{{ $crumb->name }}</span>
         @else
             <li>
                 <a href="{{ route('services.category', $crumb->slug) }}" class="hover:text-indigo-400 transition-colors">
                     {{ $crumb->name }}
                 </a>
                 <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                 </svg>
             </li>
         @endif
     @empty
     @endforelse
 </nav>

 <x-hero-section
     pageIdentifier="services.category"
     heightPreset="standard"
     heading-tag="h1"
     :fallbackTitle="$category->name"
     :fallbackSubtitle="$category->description"
     fallbackImage="/images/hero-banners/salon-banner.avif"
     
 />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($category->children->count() > 0)
        <!-- Sub-categories Section -->
        <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 mb-12 shadow-xl">
            <h3 class="text-2xl font-bold text-white mb-8 text-center">Choose a Service Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($category->children as $child)
                    @php
                        $hasServices = $child->vendorServices()->count() > 0;
                    @endphp
                    
                    <a href="{{ $hasServices ? route('services.category', $child->slug) : '#' }}"
                       class="block bg-gray-800/50 border border-gray-700 rounded-xl p-6 hover:bg-indigo-600/20 hover:border-indigo-400 transition-all duration-300 group {{ !$hasServices ? 'opacity-50 pointer-events-none' : '' }}"
                       {{ !$hasServices ? 'tabindex="-1" aria-disabled="true"' : '' }}
                       {{ !$hasServices ? 'onclick="return false;"' : '' }}>
                        <div class="w-14 h-14 bg-indigo-600/20 rounded-2xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition-colors">
                            <svg class="w-7 h-7 text-indigo-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="text-white font-semibold">{{ $child->name }}</h4>
                        <p class="text-gray-400 text-sm mt-2 line-clamp-2">{{ $child->description }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs font-medium text-indigo-400 bg-indigo-600/10 px-2 py-1 rounded">
                                {{ $child->vendorServices()->count() }} services
                            </span>
                            <span class="text-xs text-gray-500">Select</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <!-- Single Category - Show Services -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $category->name }} Services</h2>
                <p class="text-gray-400 text-sm">{{ $services->total() }} service{{ $services->total() !== 1 ? 's' : '' }} available</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('services.index') }}" class="px-4 py-2 bg-gray-800 border border-gray-700 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors">
                    Back to All Services
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
                        <div class="col-span-full text-center py-20">
                            <div class="w-20 h-20 bg-gray-800 border border-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-white mb-2">No services found</h3>
                            <p class="text-gray-400 max-w-md mx-auto">
                                No services available in this category yet. Check back soon!
                            </p>
                        </div>
                    @endforelse
                </div>

                {{ $services->links() }}
            </div>

            <!-- Sidebar -->
            <aside class="lg:sticky lg:top-24 order-first lg:order-first lg:w-[300px]">
                @include('public.services._sidebar', [
                    'parentCategories' => $parentCategories,
                    'currentCategorySlug' => $category->slug,
                    'currentRouteName' => request()->route()->getName(),
                    'filterParams' => [],
                ])
            </aside>
        </div>
    @endif
</div>

@endsection
