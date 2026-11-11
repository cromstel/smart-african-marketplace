@extends('layouts.shop')

@section('title', '{{ $category->name }} Services - Smart African Market')
@section('seoDescription', 'Browse {{ $category->name }} services from vetted vendors. Book appointments online.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">{{ $category->name }}</h1>
        <p class="text-gray-400">{{ $category->description }}</p>
    </header>

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

                    <span class="absolute top-3 left-3 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-tighter">
                        {{ ucfirst($service->category->gender_target ?? 'unisex') }}
                    </span>

                    @if($service->is_featured)
                        <span class="absolute top-3 right-3 bg-indigo-500 text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-tighter">
                            Featured
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    <h3 class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors">
                        {{ $service->name }}
                    </h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>

                    <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
                        <span class="text-indigo-400 font-semibold">${{ number_format($service->price, 2) }}</span>
                        <span>{{ $service->duration_minutes }} min</span>
                    </div>

                    <a href="{{ route('services.show', $service) }}"
                       class="block bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 rounded-lg text-sm font-medium transition-colors">
                        Book Service
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-400">No services in this category yet.</p>
            </div>
        @endforelse
    </div>

    {{ $services->links() }}
</div>
@endsection
