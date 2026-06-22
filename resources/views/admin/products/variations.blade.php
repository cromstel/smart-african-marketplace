@extends('admin.layout')

@section('title', 'Product Variations')
@section('header', 'Product Variations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-white">Variations</h3>
            <p class="text-gray-400 mt-1">Global overview of all product variations.</p>
        </div>
        <a href="{{ route('admin.products.variations.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            Add Variation
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($variations as $variation)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-white">{{ $variation->product->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-300 font-mono">{{ $variation->sku }}</td>
                        <td class="px-6 py-4 text-gray-300">${{ number_format($variation->price, 2) }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $variation->stock_quantity }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ $variation->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-300' }}">
                                {{ $variation->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($variation->product)
                                <a href="{{ route('admin.products.variants.index', $variation->product) }}" class="text-gray-300 hover:text-white text-sm mr-3">Manage</a>
                                <a href="{{ route('admin.products.variants.edit', [$variation->product, $variation]) }}" class="text-indigo-400 hover:text-indigo-300 text-sm mr-3">Edit</a>
                                <form action="{{ route('admin.products.variants.destroy', [$variation->product, $variation]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this variation?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No variations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($variations->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $variations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

