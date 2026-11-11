@php
    $settings = $widget->settings ?? [];
@endphp
<section class="py-12 bg-gradient-to-r from-orange-600 to-red-600 text-white">
    <div class="max-w-[1600px] mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold">{{ $settings['title'] ?? $widget->title ?? 'Hero Section' }}</h2>
        @if(!empty($settings['subtitle']))
            <p class="mt-3 text-lg">{{ $settings['subtitle'] }}</p>
        @endif
        @if(!empty($settings['primary_button_text']) && !empty($settings['primary_button_link']))
            <a href="{{ $settings['primary_button_link'] }}" class="inline-block mt-6 bg-white text-orange-600 px-6 py-3 rounded-lg font-semibold">
                {{ $settings['primary_button_text'] }}
            </a>
        @endif
    </div>
</section>

