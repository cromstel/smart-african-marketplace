@extends('admin.layout')

@section('title', 'Blog Categories')
@section('header', 'Blog Categories')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Blog Categories</h2>
        <p class="text-gray-400 mt-1">Organize your blog posts with categories</p>
    </div>
    <a href="{{ route('admin.blog.categories.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Category
    </a>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-900 border border-green-700 text-green-300 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
@endif

<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Name</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Slug</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Description</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Posts</th>
                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <p class="text-white font-medium">{{ $category->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <code class="text-purple-400 bg-gray-900 px-2 py-1 rounded text-sm">{{ $category->slug }}</code>
                        </td>
                        <td class="px-6 py-4 text-gray-300">
                            {{ Str::limit($category->description, 60) ?: '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs rounded-full bg-blue-900 text-blue-300 font-semibold">
                                {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.blog.categories.edit', $category) }}" class="text-brand-from hover:text-orange-300 p-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.blog.categories.destroy', $category) }}" method="POST" class="inline" 
                                      onsubmit="return confirm('Are you sure? This will not delete posts in this category.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <p class="text-gray-400 text-lg">No categories yet</p>
                            <p class="text-gray-500 mt-2">Create your first category to organize blog posts</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="px-6 py-4 border-t border-gray-700">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection

