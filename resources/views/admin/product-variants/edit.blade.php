@extends('admin.layout')

@section('title', 'Edit Product Variation')
@section('header', 'Edit Variation: '.$variant->sku)

@section('content')
<div class="max-w-5xl">
    <div class="mb-6">
        <a href="{{ route('admin.products.variants.index', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Variations
        </a>
    </div>

    <form action="{{ route('admin.products.variants.update', [$product, $variant]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Variation Pricing & Inventory</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $variant->sku) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('sku') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                    <input type="number" name="price" value="{{ old('price', $variant->price) }}" step="0.01" min="0" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compare At Price</label>
                    <input type="number" name="compare_at_price" value="{{ old('compare_at_price', $variant->compare_at_price) }}" step="0.01" min="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('compare_at_price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $variant->stock_quantity) }}" min="0" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('stock_quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $variant->low_stock_threshold) }}" min="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('low_stock_threshold') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight</label>
                    <input type="number" name="weight" value="{{ old('weight', $variant->weight) }}" step="0.01" min="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('weight') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Variation Attributes</h2>
            @php
                $attributes = old('attributes');
                if (!is_array($attributes) || count($attributes) === 0) {
                    $attributes = collect($variant->attributes ?? [])->map(fn ($value, $name) => ['name' => $name, 'value' => $value])->values()->all();
                }
            @endphp
            <div id="attributes-container" class="space-y-3">
                @foreach($attributes as $idx => $attribute)
                    <div class="attribute-row flex gap-3">
                        <input type="text" name="attributes[{{ $idx }}][name]" value="{{ $attribute['name'] ?? '' }}" placeholder="Attribute name" class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" required>
                        <input type="text" name="attributes[{{ $idx }}][value]" value="{{ $attribute['value'] ?? '' }}" placeholder="Value" class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" required>
                        <button type="button" onclick="removeAttribute(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 rounded-lg">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" onclick="addAttribute()" class="mt-3 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">+ Add Attribute</button>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Image & Dimensions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Variation Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @if($variant->image)
                        <img src="{{ '/storage/' . ltrim($variant->image ?? '', '/') }}" alt="Variation image" class="h-16 w-16 rounded object-cover mt-3">
                    @endif
                    @error('image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <input type="number" name="dimensions[length]" value="{{ old('dimensions.length', data_get($variant->dimensions, 'length')) }}" min="0" step="0.01" placeholder="Length" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2">
                    <input type="number" name="dimensions[width]" value="{{ old('dimensions.width', data_get($variant->dimensions, 'width')) }}" min="0" step="0.01" placeholder="Width" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2">
                    <input type="number" name="dimensions[height]" value="{{ old('dimensions.height', data_get($variant->dimensions, 'height')) }}" min="0" step="0.01" placeholder="Height" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-3 py-2">
                </div>
            </div>
            <div class="mt-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $variant->is_active) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-indigo-500">
                    <span class="ml-2 text-sm text-gray-300">Active</span>
                </label>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">Update Variation</button>
            <a href="{{ route('admin.products.variants.index', $product) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
        </div>
    </form>
</div>

<script>
let attributeIndex = {{ count($attributes) }};
function addAttribute() {
    const container = document.getElementById('attributes-container');
    const row = document.createElement('div');
    row.className = 'attribute-row flex gap-3';
    row.innerHTML = `
        <input type="text" name="attributes[${attributeIndex}][name]" placeholder="Attribute name" class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" required>
        <input type="text" name="attributes[${attributeIndex}][value]" placeholder="Value" class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" required>
        <button type="button" onclick="removeAttribute(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 rounded-lg">Remove</button>
    `;
    container.appendChild(row);
    attributeIndex++;
}
function removeAttribute(button) {
    const container = document.getElementById('attributes-container');
    if (container.children.length > 1) {
        button.closest('.attribute-row').remove();
    }
}
</script>
@endsection

