@extends('vendor.layout')

@section('title', 'Categories')
@section('header', 'Categories')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-gray-400">Browse available categories for your products.</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Parent</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Products</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Subcategories</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-white font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $category->parent?->name ?? 'Top level' }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $category->products_count }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $category->children_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


