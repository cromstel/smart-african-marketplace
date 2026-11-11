@extends('vendor.layout')

@section('title', 'Inventory')
@section('header', 'Inventory')

@section('content')
<div class="space-y-6">
    <div>
        <h3 class="text-2xl font-bold text-white">Inventory Management</h3>
        <p class="text-gray-400 mt-1">Track stock levels, low-stock alerts, and product availability.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-400 uppercase">All Products</p>
            <p class="text-2xl font-bold text-white mt-2">{{ $summary['all'] }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-400 uppercase">In Stock</p>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ $summary['in_stock'] }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-400 uppercase">Out of Stock</p>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ $summary['out_of_stock'] }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-400 uppercase">Low Stock</p>
            <p class="text-2xl font-bold text-yellow-400 mt-2">{{ $summary['low_stock'] }}</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
            <p class="text-xs text-gray-400 uppercase">Total Variations</p>
            <p class="text-2xl font-bold text-indigo-400 mt-2">{{ $summary['total_variants'] }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @php
            $tabs = ['all' => 'All', 'in_stock' => 'In Stock', 'low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock'];
        @endphp
        @foreach($tabs as $tabKey => $tabLabel)
            <a href="{{ route('vendor.inventory.index', ['filter' => $tabKey]) }}"
               class="px-4 py-2 rounded-lg text-sm {{ $filter === $tabKey ? 'bg-indigo-600 text-white' : 'bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700' }}">
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-white font-medium">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $product->stock_quantity }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $product->low_stock_threshold }}</td>
                        <td class="px-6 py-4">
                            @if($product->stock_quantity <= 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-red-900 text-red-200">Out of Stock</span>
                            @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-900 text-yellow-200">Low Stock</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-green-900 text-green-200">In Stock</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <a href="{{ route('vendor.products.edit', $product) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Edit</a>
                            <a href="{{ route('vendor.products.variants.index', $product) }}" class="text-blue-400 hover:text-blue-300 text-sm">Variations</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No products found for this inventory filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


