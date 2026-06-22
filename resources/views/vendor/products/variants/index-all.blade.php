@extends('vendor.layout')

@section('title', 'All Variations')
@section('header', 'All Product Variations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-white">All Variations</h3>
            <p class="text-gray-400 mt-1">WooCommerce-style variation overview across your full product catalog.</p>
        </div>
        <a href="{{ route('vendor.products.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">
            Back to Products
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Attributes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($variants as $variant)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-white">{{ $variant->product->name ?? 'Product deleted' }}</td>
                        <td class="px-6 py-4 text-gray-300 font-mono">{{ $variant->sku }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @foreach(($variant->attributes ?? []) as $name => $value)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs bg-blue-900 text-blue-200">
                                        {{ ucfirst($name) }}: {{ $value }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-300">${{ number_format($variant->price, 2) }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $variant->stock_quantity }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($variant->product)
                                <a href="{{ route('vendor.products.variants.edit', [$variant->product, $variant]) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                                    Edit
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No variations created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($variants->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $variants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


