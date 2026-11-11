@extends('layouts.shop')

@section('title', '{{ $category->name }} Services - Smart African Market')
@section('seoDescription', 'Browse {{ $category->name }} services from vetted vendors. Book appointments online.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <!-- Breadcrumb -->
        <nav class="flex flex-col sm:flex-row sm:items-center gap-2 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-400">
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
                        <span class="text-white">{{ $crumb->name }}</span>
                    @else
                        <li>
                            <a href="{{ route('services.category', $crumb->slug) }}" class="hover:text-indigo-400 transition-colors">
                                {{ $crumb->name }}
                            </a>
                            <svg class="w-3 h-3 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </li>
                    @endif
                @empty
                @endforelse
            </ol>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">{{ $category->name }}</h1>
                <p class="text-gray-400">{{ $category->description }}</p>
            </div>
            <a href="{{ route('services.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                ← Back to all services
            </a>
        </div>
    </header>

    @if($category->children->count() > 0)
        <!-- Sub-categories grid -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-6">Choose a Service Type</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($category->children as $child)
                    <a href="{{ route('services.category', $child->slug) }}"
                       class="bg-gray-700/50 border border-gray-600 rounded-lg p-6 hover:bg-indigo-600 hover:border-indigo-400 transition-all group">
                        <div class="w-12 h-12 bg-indigo-600/20 rounded-lg flex items-center justify-center mb-3 group-hover:bg-indigo-600 transition-colors">
                            <svg class="w-6 h-6 text-indigo-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h4 class="text-white font-medium">{{ $child->name }}</h4>
                        <p class="text-gray-400 text-sm mt-2">{{ $child->description }}</p>
                        <span class="mt-3 inline-block text-xs font-medium text-indigo-400 bg-indigo-600/20 px-3 py-1 rounded-full uppercase tracking-tighter">
                            View Services
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <!-- Single category - show services directly -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">
                    {{ $services->total() }} service{{ $services->total() !== 1 ? 's' : '' }} available
                </span>
                <a href="{{ route('services.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                    Back to all services
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
            @include('public.services._service-card', ['service' => $service])
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-medium text-white mb-2">No services found</h3>
                <p class="text-gray-400">No services in this category yet.</p>
            </div>
        @endforelse
    </div>

    @if($category->children->count() > 0)
        {{ $services->links() }}
    @endif
</div>
@endsection
