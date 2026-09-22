@extends('admin.layout')

@section('title', 'Edit Blog Category')
@section('header', 'Edit Blog Category')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.blog.categories.index') }}" class="text-brand-from hover:text-orange-300 inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Categories
    </a>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.blog.categories.update', $blogCategory) }}" method="POST" class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $blogCategory->name) }}" required
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-brand-from focus:border-brand-from"
                       placeholder="e.g., Technology, Fashion, Travel">
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-400 text-xs mt-1">A unique name for this category</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Parent Category</label>
                <select name="parent_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-brand-from focus:border-brand-from">
                    <option value="">None (Top Level)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('parent_id', $blogCategory->parent_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->fullPath() }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" rows="4"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-brand-from focus:border-brand-from"
                          placeholder="Describe what this category is about (optional)">{{ old('description', $blogCategory->description) }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-400 text-xs mt-1">Max 1000 characters</p>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <h3 class="text-white font-semibold mb-3">Category Info</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Current Slug:</span>
                        <code class="text-purple-400 bg-gray-800 px-2 py-1 rounded text-sm">{{ $blogCategory->slug }}</code>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Total Posts:</span>
                        <span class="text-white font-medium">{{ $blogCategory->posts()->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Created:</span>
                        <span class="text-white font-medium text-sm">{{ $blogCategory->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-900 bg-opacity-20 border border-blue-700 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-blue-300 text-sm">
                            Changing the category name will automatically update its URL slug.
                            Deleting this category will not delete posts assigned to it.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-700">
            <a href="{{ route('admin.blog.categories.index') }}" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                Update Category
            </button>
        </div>
    </form>
</div>
@endsection

