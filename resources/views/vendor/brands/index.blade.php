@extends('vendor.layout')

@section('title', 'Brands')
@section('header', 'Manage Brands')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <p class="text-gray-400">Manage all your product brands here. Brands can be assigned to products.</p>
        <a href="{{ route('vendor.brands.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            Add New Brand
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Logo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Products</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
                @forelse($brands as $brand)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($brand->logo)
                                <img src="{{ '/storage/' . ltrim($brand->logo ?? '', '/') }}" alt="{{ $brand->name }}" class="h-10 w-10 rounded object-cover">
                            @else
                                <div class="h-10 w-10 rounded bg-gray-700 flex items-center justify-center">
                                    <span class="text-gray-400 text-xs">N/A</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">{{ $brand->name }}</div>
                            @if($brand->website)
                                <div class="text-sm text-gray-400">{{ $brand->website }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                            {{ $brand->products()->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($brand->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900 text-green-200">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900 text-red-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <span class="text-gray-400 text-xs">Create-only</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="mt-4">No brands found. Create your first brand to get started.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($brands->hasPages())
        <div class="mt-4">
            {{ $brands->links() }}
        </div>
    @endif
</div>
@endsection

