@extends('vendor.layout')

@section('title', 'Edit Product')
@section('header', 'Edit Product: ' . $product->name)

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('vendor.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-white">Basic Information</h2>
                <button type="button" onclick="generateAllContent()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    AI Regenerate All
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-300">Product Name *</label>
                        <button type="button" onclick="generateTitle()" class="text-xs px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded transition flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            AI Regenerate
                        </button>
                    </div>
                    <input type="text" name="name" id="product-name" value="{{ old('name', $product->name) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                    <select name="category_id" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @if($category->children)
                                @foreach($category->children as $child)
                                    <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;→ {{ $child->name }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Select the most appropriate category for your product.</p>
                    @error('category_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Brand</label>
                    <select name="brand_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">Select Brand (Optional)</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Assign a brand to your product. <a href="{{ route('vendor.brands.create') }}" class="text-indigo-400 hover:text-indigo-300">Create new brand</a></p>
                    @error('brand_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Product Tags</label>
                    <select name="tags[]" multiple class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" size="4">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $product->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple tags.</p>
                    @error('tags') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-300">Short Description</label>
                        <button type="button" onclick="generateShortDescription()" class="text-xs px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded transition flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            AI Regenerate
                        </button>
                    </div>
                    <textarea name="short_description" id="short-description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Brief product description">{{ old('short_description', $product->short_description) }}</textarea>
                    @error('short_description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium text-gray-300">Full Description *</label>
                        <button type="button" onclick="generateDescription()" class="text-xs px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded transition flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            AI Regenerate
                        </button>
                    </div>
                    <textarea name="description" id="product-description" rows="6" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Pricing & Inventory</h2>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Price * ($)</label>
                    <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compare Price ($)</label>
                    <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Original price (for sale badge)</p>
                    @error('compare_price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cost Price ($)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Your cost (for profit tracking)</p>
                    @error('cost_price') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">SKU * (Stock Keeping Unit)</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('sku') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('stock_quantity') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Used for low-stock alerts and inventory filters.</p>
                    @error('low_stock_threshold') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Stock Status</label>
                    <select name="stock_status" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="in_stock" {{ old('stock_status', $product->stock_status) === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="out_of_stock" {{ old('stock_status', $product->stock_status) === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="backorder" {{ old('stock_status', $product->stock_status) === 'backorder' ? 'selected' : '' }}>Backorder</option>
                    </select>
                    @error('stock_status') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Weight (kg)</label>
                    <input type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.01" min="0"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('weight') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Origin Country</label>
                    <input type="text" name="origin_country" value="{{ old('origin_country', $product->origin_country) }}"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('origin_country') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Ingredients/Contents</label>
                <textarea name="ingredients" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('ingredients', $product->ingredients) }}</textarea>
                @error('ingredients') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        @if($product->images->count() > 0)
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h2 class="text-xl font-bold text-white mb-4">Current Images</h2>
                <div class="grid grid-cols-4 gap-4">
                    @foreach($product->images as $image)
                        <div class="relative group">
                            <img src="{{ '/storage/' . ltrim($image->image_path ?? '', '/') }}" alt="{{ $product->name }}" 
                                 class="w-full h-32 object-cover rounded border border-gray-600">
                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 px-2 py-1 text-xs bg-indigo-600 text-white rounded">Primary</span>
                            @endif
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                @if(!$image->is_primary)
                                    <form action="{{ route('vendor.products.images.primary', $image) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-2 py-1 rounded">
                                            Set Primary
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('vendor.products.images.replace', $image) }}" method="POST" enctype="multipart/form-data" class="inline">
                                    @csrf
                                    <input type="file" name="image" id="replace-image-vendor-{{ $image->id }}" class="hidden" accept="image/*" onchange="submitReplaceImage(this)">
                                    <button type="button" onclick="openReplaceImage('replace-image-vendor-{{ $image->id }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-2 py-1 rounded">
                                        Replace
                                    </button>
                                </form>
                                <form action="{{ route('vendor.products.images.delete', $image) }}" method="POST" class="inline" onsubmit="return confirm('Delete this image?')">
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
            </div>
        @endif

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Add More Images</h2>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Upload Images</label>
                <input type="file" name="images[]" multiple accept="image/*"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <p class="text-xs text-gray-400 mt-1">Upload additional images (Max 2MB each)</p>
                @error('images') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                @error('images.*') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded focus:ring-indigo-500">
                <label for="is_active" class="ml-2 text-sm text-gray-300">Active (product is visible in store)</label>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Product
            </button>
            <a href="{{ route('vendor.products.variants.index', $product) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Manage Variations
            </a>
            <a href="{{ route('vendor.products.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
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

    function getProductData() {
        const name = document.getElementById('product-name').value;
        const categorySelect = document.querySelector('select[name="category_id"]');
        const brandSelect = document.querySelector('select[name="brand_id"]');
        const priceInput = document.querySelector('input[name="price"]');
        
        return {
            name: name,
            category: categorySelect.options[categorySelect.selectedIndex]?.text,
            brand: brandSelect.options[brandSelect.selectedIndex]?.text,
            price: priceInput?.value || null
        };
    }

    function showLoading(button) {
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    }

    function hideLoading(button, text) {
        button.disabled = false;
        button.innerHTML = `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> ${text}`;
    }

    async function generateTitle() {
        const productData = getProductData();
        if (!productData.name) {
            alert('Please enter a product name first');
            return;
        }

        const button = event.target.closest('button');
        showLoading(button);

        try {
            const response = await fetch('{{ route("vendor.products.generate-content") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ...productData,
                    type: 'title',
                    style: 'standard'
                })
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('product-name').value = result.data.title;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate title. Please try again.');
        } finally {
            hideLoading(button, 'AI Regenerate');
        }
    }

    async function generateDescription() {
        const productData = getProductData();
        if (!productData.name) {
            alert('Please enter a product name first');
            return;
        }

        const button = event.target.closest('button');
        showLoading(button);

        try {
            const response = await fetch('{{ route("vendor.products.generate-content") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ...productData,
                    type: 'description',
                    style: 'detailed'
                })
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('product-description').value = result.data.description;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate description. Please try again.');
        } finally {
            hideLoading(button, 'AI Regenerate');
        }
    }

    async function generateShortDescription() {
        const productData = getProductData();
        if (!productData.name) {
            alert('Please enter a product name first');
            return;
        }

        const button = event.target.closest('button');
        showLoading(button);

        try {
            const response = await fetch('{{ route("vendor.products.generate-content") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ...productData,
                    type: 'short_description'
                })
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('short-description').value = result.data.short_description;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate short description. Please try again.');
        } finally {
            hideLoading(button, 'AI Regenerate');
        }
    }

    async function generateAllContent() {
        const productData = getProductData();
        if (!productData.name) {
            alert('Please enter a product name first');
            return;
        }

        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

        try {
            const response = await fetch('{{ route("vendor.products.generate-content") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ...productData,
                    type: 'all'
                })
            });

            const result = await response.json();
            if (result.success) {
                document.getElementById('product-name').value = result.data.title;
                document.getElementById('product-description').value = result.data.description;
                document.getElementById('short-description').value = result.data.short_description;
                
                alert('All content regenerated successfully!');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate content. Please try again.');
        } finally {
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    }
</script>
@endpush

@endsection

