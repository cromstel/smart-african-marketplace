@php
    $settings = $widget->settings ?? [];
    $limit = (int) ($settings['limit'] ?? 8);
    $categories = \App\Models\Category::parents()->active()->orderBy('sort_order')->limit($limit)->get();
@endphp
<section class="py-12 bg-gray-50">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-2xl font-bold mb-6">{{ $settings['title'] ?? $widget->title ?? 'Shop by Category' }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('shop.index', ['category' => $category->id]) }}" class="bg-white rounded-lg p-4 text-center shadow-sm hover:shadow-md transition">
                    <div class="h-10 w-10 mx-auto mb-2 rounded-full bg-orange-100"></div>
                    <div class="font-semibold text-gray-800">{{ $category->name }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>

