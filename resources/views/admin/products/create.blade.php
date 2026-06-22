@extends('admin.layout')

@section('title', 'Add Product')
@section('header', 'Add New Product')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Basic Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="Enter product name">
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
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
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
                                {{ old('brand_id') == $brand->id ? 'selected' : '' }}
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
                    <textarea name="short_description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Brief product description">{{ old('short_description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Full Description *</label>
                    <textarea name="description" rows="5" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Detailed product description">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Pricing & Inventory</h2>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price *</label>
                    <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="0.00">
                    @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compare Price</label>
                    <input type="number" name="compare_price" value="{{ old('compare_price') }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="0.00">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cost Price</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('stock_quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 10) }}" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                    <input type="number" name="weight" value="{{ old('weight') }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="0.00">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Origin Country</label>
                    <input type="text" name="origin_country" value="{{ old('origin_country') }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="e.g., Nigeria">
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Product Images</h2>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                <p class="text-gray-400 text-sm mt-2">Select multiple images. First image will be set as primary.</p>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Settings</h2>

            <div class="space-y-3">
                <label class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                           class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-gray-300">Featured Product</span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
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
                Create Product
            </button>
        </div>
    </form>
</div>

<script>
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

