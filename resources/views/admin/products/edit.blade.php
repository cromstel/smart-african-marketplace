@extends('admin.layout')

@section('title', 'Edit Product')
@section('header', 'Edit Product: ' . $product->name)

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Basic Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-300">Category *</label>
                            <button type="button" id="add-product-category" class="text-xs px-2 py-1 rounded bg-indigo-600 text-white">+ Add</button>
                        </div>
                        <select name="category_id" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->fullPath() }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                            <option value="">No Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ old('vendor_id', $product->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Brand</label>
                    <select name="brand_id" id="brand_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">No Brand</option>
                        @foreach($brands as $brand)
                            <option
                                value="{{ $brand->id }}"
                                data-vendor-id="{{ $brand->vendor_id ?? '' }}"
                                {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}
                            >
                                {{ $brand->name }}@if($brand->vendor) — {{ $brand->vendor->business_name }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('brand_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    <p class="text-gray-400 text-xs mt-1">Vendor products can use global brands or their own vendor brands.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Short Description</label>
                    <textarea name="short_description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('short_description', $product->short_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Full Description *</label>
                    <textarea name="description" rows="5" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Pricing & Inventory</h2>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compare Price</label>
                    <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('stock_quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Origin Country</label>
                    <input type="text" name="origin_country" value="{{ old('origin_country', $product->origin_country) }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Product Images</h2>
            
            @if($product->images->count() > 0)
                <div class="grid grid-cols-4 gap-4 mb-4">
                    @foreach($product->images as $image)
                        <div class="relative group">
                            <img src="{{ '/storage/' . ltrim($image->image_path ?? '', '/') }}" alt="{{ $image->alt_text }}" class="w-full h-32 object-cover rounded border {{ $image->is_primary ? 'border-indigo-500' : 'border-gray-600' }}">
                            
                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded">Primary</span>
                            @endif

                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                @if(!$image->is_primary)
                                    <form action="{{ route('admin.products.images.primary', $image) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-2 py-1 rounded">
                                            Set Primary
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.products.images.replace', $image) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="image" id="replace-image-admin-{{ $image->id }}" class="hidden" accept="image/*" onchange="submitReplaceImage(this)">
                                    <button type="button" onclick="openReplaceImage('replace-image-admin-{{ $image->id }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-2 py-1 rounded">
                                        Replace
                                    </button>
                                </form>

                                <form action="{{ route('admin.products.images.delete', $image) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs px-2 py-1 rounded">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 mb-4">No images uploaded yet.</p>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload New Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                <p class="text-gray-400 text-sm mt-2">Select multiple images to add to this product.</p>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Settings</h2>

            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                           class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-300">Featured Product</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-300">Active (Visible on storefront)</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.products.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Product
            </button>
        </div>
    </form>
</div>
<script>
function openReplaceImage(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.click();
    }
}

function submitReplaceImage(input) {
    if (input.files && input.files.length > 0) {
        input.form.submit();
    }
}

document.getElementById('add-product-category')?.addEventListener('click', async () => {
    const name = prompt('New category name');
    if (!name) return;

    const response = await fetch('{{ route('admin.categories.store') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name })
    });

    if (!response.ok) {
        alert('Could not create category.');
        return;
    }

    const payload = await response.json();
    const select = document.querySelector('select[name="category_id"]');
    const option = document.createElement('option');
    option.value = payload.id;
    option.textContent = payload.path || payload.name;
    option.selected = true;
    select.appendChild(option);
});

function syncBrandOptions() {
    const vendorSelect = document.getElementById('vendor_id');
    const brandSelect = document.getElementById('brand_id');
    if (!vendorSelect || !brandSelect) return;

    const vendorId = vendorSelect.value;
    Array.from(brandSelect.options).forEach((option) => {
        if (option.value === '') {
            option.hidden = false;
            return;
        }
        const optionVendorId = option.dataset.vendorId || '';
        const allowed = vendorId ? (optionVendorId === '' || optionVendorId === vendorId) : (optionVendorId === '');
        option.hidden = !allowed;
    });

    const selected = brandSelect.selectedOptions[0];
    if (selected && selected.hidden) {
        brandSelect.value = '';
    }
}

document.getElementById('vendor_id')?.addEventListener('change', syncBrandOptions);
document.addEventListener('DOMContentLoaded', syncBrandOptions);
</script>
@endsection

