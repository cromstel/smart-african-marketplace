@props(['service'])

<article class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden hover:border-indigo-500 transition-all group">
    <div class="h-48 bg-gray-700 relative">
        @if($service->image)
            <img src="{{ asset('storage/'.ltrim($service->image, '/')) }}"
                 alt="{{ $service->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        @php
            $genderTarget = optional($service->category)->gender_target ?? 'unisex';
        @endphp

        <!-- Service Type Badge -->
        <span class="absolute top-3 left-3 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full uppercase tracking-tighter">
            {{ ucfirst($genderTarget) }}
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
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $service->duration_minutes }} min
            </span>
            <span class="text-green-400 flex items-center">
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
            <a href="{{ optional($service->vendor)->storefrontUrl() ?? route('services.index') }}"
               class="text-gray-400 hover:text-white text-sm transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 6l2.287 6.862M14.5 4.956a1 1 0 011.493 1.493l1.5 7.5a1 1 0 01-1.493 1.493L17 16h-5l-2.287 6.863A1 1 0 019.217 19h-5a1 1 0 011-1v-4a1 1 0 011-1h2.287l5.287-15.863a1 1 0 011.493-1.493z"></path>
                </svg>
                {{ optional($service->vendor)->business_name ?? 'Vendor' }}
            </a>
        </div>
    </div>
</article>
