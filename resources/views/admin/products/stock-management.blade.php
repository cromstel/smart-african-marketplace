@extends('admin.layout')

@section('title', 'Stock Management')
@section('header', 'Stock Management')

@section('content')
<div class="mb-6">
    <h3 class="text-2xl font-bold text-white">Stock Management</h3>
    <p class="text-gray-400 mt-1">Monitor and manage product inventory across all vendors</p>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('admin.products.stock-management', ['filter' => 'all']) }}" 
       class="p-4 rounded-lg border {{ $filter === 'all' ? 'bg-indigo-600 border-indigo-500' : 'bg-gray-800 border-gray-700 hover:border-gray-600' }}">
        <div class="text-2xl font-bold text-white">{{ $summary['all'] }}</div>
        <div class="text-sm text-gray-400">Total Products</div>
    </a>
    <a href="{{ route('admin.products.stock-management', ['filter' => 'in_stock']) }}" 
       class="p-4 rounded-lg border {{ $filter === 'in_stock' ? 'bg-green-600 border-green-500' : 'bg-gray-800 border-gray-700 hover:border-gray-600' }}">
        <div class="text-2xl font-bold text-white">{{ $summary['in_stock'] }}</div>
        <div class="text-sm text-gray-400">In Stock</div>
    </a>
    <a href="{{ route('admin.products.stock-management', ['filter' => 'low_stock']) }}" 
       class="p-4 rounded-lg border {{ $filter === 'low_stock' ? 'bg-yellow-600 border-yellow-500' : 'bg-gray-800 border-gray-700 hover:border-gray-600' }}">
        <div class="text-2xl font-bold text-white">{{ $summary['low_stock'] }}</div>
        <div class="text-sm text-gray-400">Low Stock</div>
    </a>
    <a href="{{ route('admin.products.stock-management', ['filter' => 'out_of_stock']) }}" 
       class="p-4 rounded-lg border {{ $filter === 'out_of_stock' ? 'bg-red-600 border-red-500' : 'bg-gray-800 border-gray-700 hover:border-gray-600' }}">
        <div class="text-2xl font-bold text-white">{{ $summary['out_of_stock'] }}</div>
        <div class="text-sm text-gray-400">Out of Stock</div>
    </a>
</div>

<!-- Products Table -->
<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vendor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Threshold</th>
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
                        {{ $product->vendor?->name ?? 'No Vendor' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $product->category?->name ?? 'Uncategorized' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                        @if($product->stock_quantity <= 0)
                            <span class="text-red-400">{{ $product->stock_quantity }}</span>
                        @elseif($product->isLowStock())
                            <span class="text-yellow-400">{{ $product->stock_quantity }}</span>
                        @else
                            <span class="text-green-400">{{ $product->stock_quantity }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                        {{ $product->low_stock_threshold }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($product->stock_quantity <= 0)
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Out of Stock</span>
                        @elseif($product->isLowStock())
                            <span class="px-2 py-1 text-xs rounded bg-yellow-900 text-yellow-300">Low Stock</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">In Stock</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-400 hover:text-blue-300">Update Stock</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No products found in this category.
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

