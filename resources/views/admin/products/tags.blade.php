@extends('admin.layout')

@section('title', 'Product Tags')
@section('header', 'Product Tags')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
        <h3 class="text-2xl font-bold text-white">Tags</h3>
            <p class="text-gray-400 mt-1">Global overview of product tags across vendor catalogs.</p>
        </div>
        <a href="{{ route('admin.products.tags.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
            Add Tag
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Color</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Usage Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Owner</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-700 transition">
                        <td class="px-6 py-4 text-white">{{ $tag->name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                {{ $tag->color }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-300">{{ $tag->usage_count }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $tag->products_count }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $tag->vendor_id ? 'Vendor' : 'Global' }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.products.tags.edit', $tag) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                            <form action="{{ route('admin.products.tags.destroy', $tag) }}" method="POST" class="inline" onsubmit="return confirm('Delete this tag?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No tags found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($tags->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $tags->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

