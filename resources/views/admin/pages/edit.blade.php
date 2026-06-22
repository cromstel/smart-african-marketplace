@extends('admin.layout')

@section('title', 'Edit Page')
@section('header', 'Edit Page')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.pages.update', $page) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @error('title')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @error('slug')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('excerpt', $page->excerpt) }}</textarea>
                @error('excerpt')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Content *</label>
                <textarea name="content" rows="10" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('content', $page->content) }}</textarea>
                @error('content')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('meta_title')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" min="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('sort_order')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('meta_description', $page->meta_description) }}</textarea>
                @error('meta_description')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-indigo-500">
                <span class="ml-2 text-sm text-gray-300">Published</span>
            </label>
        </div>

        <div class="flex justify-end gap-4">
            <a href="{{ route('admin.pages.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">Save Changes</button>
        </div>
    </form>
</div>
@endsection

