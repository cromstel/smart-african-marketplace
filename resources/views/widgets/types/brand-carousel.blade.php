@php
    $settings = $widget->settings ?? [];
    $brands = \App\Models\Brand::active()->orderBy('name')->limit((int) ($settings['limit'] ?? 8))->get();
@endphp
<section class="py-10 bg-gray-50">
    <div class="max-w-[1600px] mx-auto px-4">
        <h3 class="text-2xl font-bold mb-6">{{ $settings['title'] ?? $widget->title ?? 'Brands' }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($brands as $brand)
                <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                    <div class="h-10 w-10 mx-auto mb-2 rounded-full bg-gray-100"></div>
                    <div class="text-gray-800 font-semibold">{{ $brand->name }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

