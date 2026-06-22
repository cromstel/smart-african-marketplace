@php
    $settings = $widget->settings ?? [];
    $items = $settings['items'] ?? [];
@endphp
<section class="py-10 bg-white">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-2xl font-bold mb-6">{{ $settings['title'] ?? $widget->title ?? 'FAQ' }}</h3>
        <div class="space-y-4">
            @forelse($items as $item)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="font-semibold text-gray-800">{{ $item['question'] ?? '' }}</div>
                    <div class="text-gray-600 mt-2">{{ $item['answer'] ?? '' }}</div>
                </div>
            @empty
                <div class="text-gray-500">Add FAQ items in widget settings.</div>
            @endforelse
        </div>
    </div>
</section>

