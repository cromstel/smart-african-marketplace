@php
    $settings = $widget->settings ?? [];
    $items = $settings['items'] ?? [];
@endphp
<section class="py-8 bg-white">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-xl font-bold mb-4">{{ $settings['title'] ?? $widget->title ?? 'Menu' }}</h3>
        <ul class="space-y-2">
            @foreach($items as $item)
                <li>
                    <a href="{{ $item['url'] ?? '#' }}" class="text-gray-700 hover:text-orange-600">
                        {{ $item['label'] ?? 'Link' }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>

