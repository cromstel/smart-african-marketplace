@props([
    'size' => 'sm',
    'name' => null,
])

@php
    $brandName = $name ?? config('app.name');
    $textSize = $size === 'lg' ? 'text-xl sm:text-2xl' : 'text-sm sm:text-base lg:text-lg';
@endphp

<div class="inline-flex items-center space-x-3 mb-8 group">
    <a href="{{ route('home') }}" class="relative" aria-label="{{ config('app.name', 'Smart African Market') }} - Go to homepage">
        <img src="{{ asset(config('branding.logo')) }}" alt="{{ config('app.name', 'Smart African Market') }}" class="h-16 w-16 rounded-full p-2 bg-gradient-to-br from-orange-500 to-red-600 shadow-lg animate-pulse-glow transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
    </a>
    <span class="{{ $textSize }} font-extrabold uppercase tracking-wide bg-gradient-to-r from-orange-600 via-red-600 to-orange-600 bg-clip-text text-transparent animate-gradient">{{ $brandName }}</span>
</div>
