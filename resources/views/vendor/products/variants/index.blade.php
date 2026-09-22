@extends('vendor.layout')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white mb-2">Product Variants: {{ $product->name }}</h2>
                <nav class="flex text-sm text-gray-400">
                    <a href="{{ route('vendor.products.index') }}" class="hover:text-white">Products</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Variants</span>
                </nav>
            </div>
            <a href="{{ route('vendor.products.variants.create', $product) }}" 
               class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Variant
            </a>
        </div>

        @if($variants->count() > 0)
            <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">SKU</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Attributes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($variants as $variant)
                                <tr class="hover:bg-gray-700 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($variant->image)
                                            <img src="{{ '/storage/' . ltrim($variant->image ?? '', '/') }}" 
                                                 alt="Variant" 
                                                 class="h-12 w-12 rounded-lg object-cover">
                                        @else
                                            <div class="h-12 w-12 bg-gray-700 rounded-lg flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono text-gray-300">{{ $variant->sku }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($variant->attributes as $name => $value)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-200">
                                                    {{ ucfirst($name) }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white font-semibold">${{ number_format($variant->price, 2) }}</div>
                                        @if($variant->compare_at_price)
                                            <div class="text-xs text-gray-400 line-through">${{ number_format($variant->compare_at_price, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-white">{{ $variant->stock_quantity }}</div>
                                        @if($variant->isLowStock())
                                            <span class="text-xs text-yellow-400">Low Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($variant->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900 text-green-200">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-700 text-gray-300">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('vendor.products.variants.edit', [$product, $variant]) }}"
                                               class="text-blue-400 hover:text-blue-300 transition" aria-label="Edit {{ $variant->name }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('vendor.products.variants.destroy', [$product, $variant]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this variant?');"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 transition" aria-label="Delete {{ $variant->name }}">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($variants->hasPages())
                    <div class="px-6 py-4 bg-gray-900">
                        {{ $variants->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-lg font-medium text-white mb-2">No variants yet</h3>
                <p class="text-gray-400 mb-6">Create variations of this product with different attributes like size, color, or material.</p>
                <a href="{{ route('vendor.products.variants.create', $product) }}" 
                   class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create First Variant
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

