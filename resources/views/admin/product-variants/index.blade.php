@extends('admin.layout')

@section('title', 'Product Variants')
@section('header', 'Manage Variants: ' . $product->name)

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="{{ route('admin.products.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm mb-2 inline-block">
            ← Back to Products
        </a>
        <h3 class="text-2xl font-bold text-white">{{ $product->name }} - Variants</h3>
        <p class="text-gray-400 mt-1">Manage product variations (size, color, etc.)</p>
    </div>
    <a href="{{ route('admin.products.variants.create', $product) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Add Variant
    </a>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Compare At</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Attributes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($variants as $variant)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-300 font-mono">{{ $variant->sku }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-white">
                        ${{ number_format($variant->price, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $variant->compare_at_price ? '$'.number_format($variant->compare_at_price, 2) : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $variant->stock_quantity }}
                    </td>
                    <td class="px-6 py-4">
                        @if($variant->attributes && is_array($variant->attributes))
                            <div class="flex flex-wrap gap-1">
                                @foreach($variant->attributes as $key => $value)
                                    <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-300">
                                        {{ ucfirst($key) }}: {{ $value }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400 text-sm">No attributes</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($variant->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300">Active</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-900 text-red-300">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">Edit</a>
                        <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this variant?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        No variants found. Create your first variant for this product.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

