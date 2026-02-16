@extends('admin.layout')

@section('title', 'Edit Product Variant')
@section('header', 'Edit Variant: ' . $variant->name)

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.products.variants.index', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
            ← Back to Variants
        </a>
    </div>

    <form action="{{ route('admin.products.variants.update', [$product, $variant]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Variant Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Variant Name *</label>
                    <input type="text" name="name" value="{{ old('name', $variant->name) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Descriptive name for this variant</p>
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">SKU *</label>
                        <input type="text" name="sku" value="{{ old('sku', $variant->sku) }}" required
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @error('sku') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Price * ($)</label>
                        <input type="number" name="price" value="{{ old('price', $variant->price) }}" required min="0" step="0.01"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $variant->stock_quantity) }}" required min="0"
                               class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @error('stock_quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Variant Attributes</h2>
            <p class="text-gray-400 text-sm mb-4">Define characteristics that make this variant unique</p>

            <div id="attributes-container" class="space-y-3">
                @php
                    $attributes = old('attributes', $variant->attributes ?? []);
                    $attributeIndex = 0;
                @endphp
                @if(is_array($attributes) && count($attributes) > 0)
                    @foreach($attributes as $key => $value)
                        <div class="attribute-row flex gap-3">
                            <input type="text" name="attributes[{{ $attributeIndex }}][key]" value="{{ $key }}" placeholder="Attribute name"
                                   class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <input type="text" name="attributes[{{ $attributeIndex }}][value]" value="{{ $value }}" placeholder="Value"
                                   class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <button type="button" onclick="removeAttribute(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 rounded-lg">
                                Remove
                            </button>
                        </div>
                        @php $attributeIndex++; @endphp
                    @endforeach
                @else
                    <div class="attribute-row flex gap-3">
                        <input type="text" name="attributes[0][key]" placeholder="Attribute name (e.g., Size)"
                               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <input type="text" name="attributes[0][value]" placeholder="Value (e.g., Large)"
                               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <button type="button" onclick="removeAttribute(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 rounded-lg">
                            Remove
                        </button>
                    </div>
                @endif
            </div>

            <button type="button" onclick="addAttribute()" class="mt-3 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
                + Add Attribute
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $variant->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                <label for="is_active" class="ml-2 text-sm text-gray-300">Active (variant is available for purchase)</label>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Variant
            </button>
            <a href="{{ route('admin.products.variants.index', $product) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
let attributeIndex = {{ count($attributes ?? []) }};

function addAttribute() {
    const container = document.getElementById('attributes-container');
    const div = document.createElement('div');
    div.className = 'attribute-row flex gap-3';
    div.innerHTML = `
        <input type="text" name="attributes[${attributeIndex}][key]" placeholder="Attribute name"
               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        <input type="text" name="attributes[${attributeIndex}][value]" placeholder="Value"
               class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
        <button type="button" onclick="removeAttribute(this)" class="bg-red-600 hover:bg-red-700 text-white px-4 rounded-lg">
            Remove
        </button>
    `;
    container.appendChild(div);
    attributeIndex++;
}

function removeAttribute(button) {
    const container = document.getElementById('attributes-container');
    if (container.children.length > 1) {
        button.closest('.attribute-row').remove();
    } else {
        alert('At least one attribute row must remain.');
    }
}
</script>
@endsection
