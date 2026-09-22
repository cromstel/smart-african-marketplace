@extends('admin.layout')

@section('title', 'Theme')
@section('header', 'Theme')

@php
    $presets = [
        [
            'id' => 'modern-commerce',
            'name' => 'Modern Commerce',
            'description' => 'Balanced layout for marketplaces with strong conversion-focused accents.',
            'preview' => 'from-orange-500 to-red-600',
            'header' => 'Centered',
            'footer' => 'Columns',
        ],
        [
            'id' => 'minimal-editorial',
            'name' => 'Minimal Editorial',
            'description' => 'Calm typography and spacious rhythm for storytelling and content-led brands.',
            'preview' => 'from-amber-700 to-stone-800',
            'header' => 'Minimal',
            'footer' => 'Minimal',
        ],
        [
            'id' => 'dark-pro',
            'name' => 'Dark Pro',
            'description' => 'Bold dark surfaces with vivid accents for modern and premium product catalogs.',
            'preview' => 'from-rose-500 to-purple-600',
            'header' => 'Split',
            'footer' => 'Dense',
        ],
        [
            'id' => 'classic',
            'name' => 'Classic',
            'description' => 'Conservative structure with familiar spacing for broad audience readability.',
            'preview' => 'from-blue-500 to-indigo-700',
            'header' => 'Centered',
            'footer' => 'Columns',
        ],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h3 class="text-xl font-semibold text-white">Theme Studio</h3>
        <p class="text-gray-400 mt-2">Preset-driven theme styles with centralized customization controls.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5 text-sm">
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-4">
                <div class="text-gray-400">Active Preset</div>
                <div class="text-white font-semibold mt-1">{{ str($settings['theme_preset'])->replace('-', ' ')->title() }}</div>
            </div>
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-4">
                <div class="text-gray-400">Header / Footer</div>
                <div class="text-white font-semibold mt-1">{{ str($settings['theme_header_preset'])->title() }} / {{ str($settings['theme_footer_preset'])->title() }}</div>
            </div>
            <div class="bg-gray-900/60 border border-gray-700 rounded-lg p-4">
                <div class="text-gray-400">Container / Mode</div>
                <div class="text-white font-semibold mt-1">{{ $settings['theme_container_width'] }}px / {{ strtoupper($settings['site_theme']) }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @foreach($presets as $preset)
            @php $isActive = ($settings['theme_preset'] ?? 'modern-commerce') === $preset['id']; @endphp
            <div class="bg-gray-800 border {{ $isActive ? 'border-orange-500' : 'border-gray-700' }} rounded-xl overflow-hidden">
                <div class="h-28 bg-gradient-to-r {{ $preset['preview'] }}"></div>
                <div class="p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="text-white text-lg font-semibold">{{ $preset['name'] }}</h4>
                        @if($isActive)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-900 text-green-200">Active</span>
                        @endif
                    </div>
                    <p class="text-gray-400 text-sm mt-2">{{ $preset['description'] }}</p>
                    <div class="text-xs text-gray-400 mt-3">Header: {{ $preset['header'] }} | Footer: {{ $preset['footer'] }}</div>

                    <form action="{{ route('admin.appearance.theme.preset') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="preset" value="{{ $preset['id'] }}">
                        <button type="submit" class="w-full {{ $isActive ? 'bg-gray-700 cursor-default' : 'bg-brand-gradient hover:opacity-90' }} text-white px-4 py-2 rounded-lg" {{ $isActive ? 'disabled' : '' }}>
                            {{ $isActive ? 'Preset Active' : 'Apply Preset' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div>
        <a href="{{ route('admin.appearance.customization') }}" class="inline-flex items-center bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">
            Open Customization
        </a>
    </div>
</div>
@endsection

