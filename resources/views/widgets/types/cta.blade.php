@php
    $settings = $widget->settings ?? [];
@endphp
<section class="py-12 bg-orange-50">
    <div class="max-w-[1600px] mx-auto px-4 text-center">
        <h3 class="text-2xl font-bold">{{ $settings['title'] ?? $widget->title ?? 'Call to Action' }}</h3>
        @if(!empty($settings['text']))
            <p class="mt-3 text-gray-700">{{ $settings['text'] }}</p>
        @endif
        @if(!empty($settings['button_text']) && !empty($settings['button_link']))
            <a href="{{ $settings['button_link'] }}" class="inline-block mt-6 bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $settings['button_text'] }}
            </a>
        @endif
    </div>
</section>

