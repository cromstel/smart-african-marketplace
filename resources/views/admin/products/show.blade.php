@extends('admin.layout')

@section('title', 'Product Details')
@section('header', 'Product Details')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">{{ $product->name }}</h3>
        <p class="text-gray-400 mt-1">SKU: {{ $product->sku ?? 'N/A' }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.edit', $product) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Edit
        </a>
        <a href="{{ route('admin.products.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Back to Products
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Overview</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Category</p>
                    <p class="text-white font-medium">{{ $product->category->name ?? 'Uncategorized' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Vendor</p>
                    <p class="text-white font-medium">{{ $product->vendor->business_name ?? 'No Vendor' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Brand</p>
                    @if($product->brand)
                        <a href="{{ route('brands.show', $product->brand) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">
                            {{ $product->brand->name }}
                        </a>
                    @else
                        <p class="text-white font-medium">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-gray-400">Price</p>
                    <p class="text-white font-medium">${{ number_format($product->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Compare Price</p>
                    <p class="text-white font-medium">
                        {{ $product->compare_price ? '$' . number_format($product->compare_price, 2) : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400">Stock</p>
                    <p class="{{ $product->isLowStock() ? 'text-red-400' : 'text-white' }} font-medium">
                        {{ $product->stock_quantity }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400">Status</p>
                    @if($product->is_active)
                        <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Description</h2>
            @if($product->short_description)
                <p class="text-gray-300 mb-4">{{ $product->short_description }}</p>
            @endif
            <div class="text-gray-300 whitespace-pre-line">{{ $product->description }}</div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Images</h2>
            @if($product->images->count() > 0)
                <div class="grid grid-cols-2 gap-3">
                    @foreach($product->images as $image)
                        <div class="relative">
                            <img src="{{ '/storage/' . ltrim($image->image_path ?? '', '/') }}" alt="{{ $image->alt_text }}" class="w-full h-28 object-cover rounded border {{ $image->is_primary ? 'border-indigo-500' : 'border-gray-600' }}">
                            @if($image->is_primary)
                                <span class="absolute top-2 left-2 bg-indigo-600 text-white text-xs px-2 py-1 rounded">Primary</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No images uploaded.</p>
            @endif
        </div>

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Additional Details</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-400">Weight (kg)</p>
                    <p class="text-white font-medium">{{ $product->weight ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Origin Country</p>
                    <p class="text-white font-medium">{{ $product->origin_country ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Featured</p>
                    <p class="text-white font-medium">{{ $product->is_featured ? 'Yes' : 'No' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

