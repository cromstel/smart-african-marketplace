@extends('admin.layout')

@section('title', 'Products')
@section('header', 'Products Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">All Products</h3>
        <p class="text-gray-400 mt-1">Manage your product inventory</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Product
    </a>
</div>

<div class="mb-6 bg-gray-800 rounded-lg border border-gray-700 p-4">
    <form method="GET" action="{{ route('admin.products.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search products..." 
                    class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
            </div>
            
            <!-- Category -->
            <div>
                <select name="category_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Brand -->
            <div>
                <select name="brand_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                    <option value="">All Brands</option>
                    @foreach($brands ?? [] as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Stock Status -->
            <div>
                <select name="stock_status" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                </select>
            </div>
            
            <!-- Sort -->
            <div>
                <select name="sort" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                    <option value="date_desc" {{ request('sort') == 'date_desc' ? 'selected' : '' }}>Newest</option>
                    <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Oldest</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Popularity</option>
                    <option value="stock_asc" {{ request('sort') == 'stock_asc' ? 'selected' : '' }}>Stock: Low to High</option>
                    <option value="stock_desc" {{ request('sort') == 'stock_desc' ? 'selected' : '' }}>Stock: High to Low</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Min Price -->
            <div>
                <input type="number" name="min_price" value="{{ request('min_price') }}" 
                    placeholder="Min Price" min="0" step="0.01"
                    class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
            </div>
            
            <!-- Max Price -->
            <div>
                <input type="number" name="max_price" value="{{ request('max_price') }}" 
                    placeholder="Max Price" min="0" step="0.01"
                    class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
            </div>
            
            <!-- Status -->
            <div>
                <select name="is_active" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    Filter
                </button>
                <a href="{{ route('admin.products.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Reset
                </a>
            </div>
        </div>
    </form>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Brand</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($products as $product)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div>
                                <div class="text-sm font-medium text-white">{{ $product->name }}</div>
                                <div class="text-sm text-gray-400">SKU: {{ $product->sku }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $product->category->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $product->brand?->name ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white font-semibold">
                        ${{ $product->price ? number_format((float) $product->price, 2) : '0.00' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="{{ $product->isLowStock() ? 'text-red-400' : 'text-gray-300' }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">View</a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No products found. <a href="{{ route('admin.products.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $products->links() }}
</div>
@endsection

