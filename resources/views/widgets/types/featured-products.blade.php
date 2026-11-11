@php
    $settings = $widget->settings ?? [];
    $limit = (int) ($settings['limit'] ?? 8);
    $products = \App\Models\Product::active()->featured()->published()->limit($limit)->get();
@endphp
<section class="py-12 bg-white">
    <div class="max-w-[1600px] mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold">{{ $settings['title'] ?? $widget->title ?? 'Featured Products' }}</h3>
            <a href="{{ route('shop.index') }}" class="text-orange-600 font-semibold">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($products as $product)
                <a href="{{ route('shop.show', $product) }}" class="bg-gray-50 rounded-lg p-4 hover:shadow transition">
                    <div class="h-32 bg-gray-100 mb-3 rounded"></div>
                    <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                    <div class="text-sm text-gray-500">{{ $product->category?->name }}</div>
                </a>
            @endforeach
        </div>
    </div>
</section>

