@extends('admin.layout')

@section('title', 'Edit Category')
@section('header', 'Edit Category: ' . $category->name)

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Category Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category Name *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Parent Category</label>
                    <select name="parent_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">None (Top Level Category)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->fullPath() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select a parent category to create a subcategory</p>
                    @error('parent_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $category->description) }}</textarea>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category Hero Banner</label>
                    <input type="file" name="banner_image" accept="image/png,image/jpeg,image/webp,image/avif,image/gif"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Used as the hero banner when this product category is selected in the shop.</p>
                    @error('banner_image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                    @if($category->banner_image)
                        <div class="mt-3">
                            <img src="{{ '/storage/' . ltrim($category->banner_image ?? '', '/') }}" alt="Category banner" class="w-full max-w-md h-28 object-cover rounded border border-gray-700">
                        </div>
                    @endif
                </div>

                @if($category->products_count > 0 || $category->children_count > 0)
                    <div class="bg-gray-900 rounded p-4">
                        <p class="text-sm text-gray-400">
                            <strong class="text-white">Usage:</strong><br>
                            @if($category->products_count > 0)
                                {{ $category->products_count }} product(s) in this category<br>
                            @endif
                            @if($category->children_count > 0)
                                {{ $category->children_count }} subcategory(ies)
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Update Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

