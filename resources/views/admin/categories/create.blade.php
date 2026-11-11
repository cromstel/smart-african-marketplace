@extends('admin.layout')

@section('title', 'Add Category')
@section('header', 'Add New Category')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Category Information</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                           placeholder="e.g., African Foods, Clothing, Salon Services">
                    @error('name') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Parent Category</label>
                    <select name="parent_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">None (Top Level Category)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('parent_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->fullPath() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Select a parent category to create a subcategory</p>
                    @error('parent_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                              placeholder="Brief description of this category">{{ old('description') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">This description will be displayed on category pages</p>
                    @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Category Hero Banner</label>
                    <input type="file" name="banner_image" accept="image/png,image/jpeg,image/webp,image/avif"
                           class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-400 mt-1">Used as the hero banner when this product category is selected in the shop.</p>
                    @error('banner_image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-blue-900 border border-blue-700 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm text-blue-200 font-medium">Category Usage</p>
                            <p class="text-xs text-blue-300 mt-1">Once created, this category will be available for vendors to assign to their products. Vendors cannot create or modify categories.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">
                Create Category
            </button>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

