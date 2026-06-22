@php
    $settings = $widget->settings ?? [];
    $items = $settings['items'] ?? [];
@endphp
<section class="py-12 bg-white">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-2xl font-bold mb-6">{{ $settings['title'] ?? $widget->title ?? 'Testimonials' }}</h3>
        <div class="grid md:grid-cols-3 gap-6">
            @forelse($items as $item)
                <div class="bg-gray-50 rounded-lg p-5">
                    <p class="text-gray-700">“{{ $item['quote'] ?? '' }}”</p>
                    <p class="text-sm text-gray-500 mt-3">{{ $item['name'] ?? '' }}</p>
                </div>
            @empty
                <div class="text-gray-500">Add testimonials in widget settings.</div>
            @endforelse
        </div>
    </div>
</section>

