@extends('vendor.layout')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white mb-2">Edit Product Variant</h2>
            <nav class="flex text-sm text-gray-400">
                <a href="{{ route('vendor.products.index') }}" class="hover:text-white">Products</a>
                <span class="mx-2">/</span>
                <a href="{{ route('vendor.products.variants.index', $product) }}" class="hover:text-white">{{ $product->name }}</a>
                <span class="mx-2">/</span>
                <span class="text-white">Edit Variant</span>
            </nav>
        </div>

        <form action="{{ route('vendor.products.variants.update', [$product, $variant]) }}" method="POST" enctype="multipart/form-data" class="bg-gray-800 rounded-lg shadow p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-white mb-4">Variant Attributes</h3>
                    <div id="attributes-container" class="space-y-3">
                        @foreach($variant->attributes as $index => $attr)
                            <div class="attribute-row flex gap-3">
                                <input type="text" name="attributes[{{ $index }}][name]" value="{{ is_array($attr) ? ($attr['name'] ?? $index) : $index }}" placeholder="Attribute name" required
                                       class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <input type="text" name="attributes[{{ $index }}][value]" value="{{ is_array($attr) ? ($attr['value'] ?? $attr) : $attr }}" placeholder="Value" required
                                       class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                <button type="button" onclick="removeAttribute(this)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addAttribute()" class="mt-3 inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Attribute
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $variant->price) }}" required
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('price')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compare at Price</label>
                    <input type="number" name="compare_at_price" step="0.01" min="0" value="{{ old('compare_at_price', $variant->compare_at_price) }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('compare_at_price')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" min="0" value="{{ old('stock_quantity', $variant->stock_quantity) }}" required
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('stock_quantity')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $variant->low_stock_threshold) }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('low_stock_threshold')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $variant->sku) }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('sku')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" min="0" value="{{ old('weight', $variant->weight) }}"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    @error('weight')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Dimensions (cm)</label>
                    <div class="grid grid-cols-3 gap-3">
                        <input type="number" name="dimensions[length]" step="0.01" min="0" value="{{ old('dimensions.length', $variant->dimensions['length'] ?? '') }}" placeholder="Length"
                               class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <input type="number" name="dimensions[width]" step="0.01" min="0" value="{{ old('dimensions.width', $variant->dimensions['width'] ?? '') }}" placeholder="Width"
                               class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <input type="number" name="dimensions[height]" step="0.01" min="0" value="{{ old('dimensions.height', $variant->dimensions['height'] ?? '') }}" placeholder="Height"
                               class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Variant Image</label>
                    @if($variant->image)
                        <div class="mb-3">
                            <img src="{{ '/storage/' . ltrim($variant->image ?? '', '/') }}" alt="Current" class="h-24 w-24 object-cover rounded-lg">
                            <p class="text-xs text-gray-400 mt-1">Current image</p>
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                           class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-orange-600 file:text-white hover:file:bg-orange-700">
                    <p class="mt-1 text-xs text-gray-400">Upload new image to replace current</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $variant->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-orange-600 bg-gray-700 border-gray-600 rounded focus:ring-orange-500">
                        <span class="ml-2 text-sm text-gray-300">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-700">
                <a href="{{ route('vendor.products.variants.index', $product) }}" 
                   class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition">
                    Update Variant
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let attributeIndex = {{ count($variant->attributes) }};

function addAttribute() {
    const container = document.getElementById('attributes-container');
    const row = document.createElement('div');
    row.className = 'attribute-row flex gap-3';
    row.innerHTML = `
        <input type="text" name="attributes[${attributeIndex}][name]" placeholder="Attribute name (e.g., Size, Color)" required
               class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        <input type="text" name="attributes[${attributeIndex}][value]" placeholder="Value (e.g., Large, Red)" required
               class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent">
        <button type="button" onclick="removeAttribute(this)" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;
    container.appendChild(row);
    attributeIndex++;
    updateRemoveButtons();
}

function removeAttribute(button) {
    button.closest('.attribute-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.attribute-row');
    rows.forEach((row, index) => {
        const button = row.querySelector('button[onclick^="removeAttribute"]');
        if (rows.length === 1) {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}

updateRemoveButtons();
</script>
@endsection

