@extends('admin.layout')

@section('title', 'Product Brands')
@section('header', 'Product Brands')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
        <h3 class="text-2xl font-bold text-white">Brands</h3>
            <p class="text-gray-400 mt-1">Global overview of product brands used in catalog.</p>
        </div>
        <a href="{{ route('admin.products.brands.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            Add Brand
        </a>
    </div>

    <form method="GET" action="{{ route('admin.products.brands.index') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-4 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Scope</label>
            <select name="vendor_scope" class="bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                <option value="">All</option>
                <option value="global" {{ request('vendor_scope') === 'global' ? 'selected' : '' }}>Global</option>
                <option value="vendor" {{ request('vendor_scope') === 'vendor' ? 'selected' : '' }}>Vendor</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Vendor</label>
            <select name="vendor_id" class="bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
                <option value="">All Vendors</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                        {{ $vendor->business_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="ml-auto">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.products.brands.index') }}" class="ml-2 text-sm text-gray-400 hover:text-gray-200">Reset</a>
        </div>
    </form>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Website</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($brands as $brand)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4">
                            @if($brand->logo)
                                <img src="{{ '/storage/' . ltrim($brand->logo ?? '', '/') }}" alt="{{ $brand->name }}" class="h-10 w-10 rounded object-cover">
                            @else
                                <div class="h-10 w-10 rounded bg-gray-700"></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-white">{{ $brand->name }}</td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ $brand->vendor?->business_name ?? 'Global' }}
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $brand->products_count }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs {{ $brand->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-300' }}">
                                {{ $brand->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $brand->website ?: 'N/A' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.products.index', ['brand_id' => $brand->id]) }}" class="text-indigo-400 hover:text-indigo-300 mr-3">View Products</a>
                            <a href="{{ route('admin.products.brands.edit', $brand) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                            <form action="{{ route('admin.products.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Delete this brand?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No brands found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($brands->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $brands->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

