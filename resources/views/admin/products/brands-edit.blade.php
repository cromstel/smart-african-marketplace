@extends('admin.layout')

@section('title', 'Edit Brand')
@section('header', 'Edit Brand')

@section('content')
<div class="max-w-3xl">
    <form action="{{ route('admin.products.brands.update', $brand) }}" method="POST" enctype="multipart/form-data" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
            <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('slug')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Website</label>
            <input type="url" name="website" value="{{ old('website', $brand->website) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('website')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $brand->description) }}</textarea>
            @error('description')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Brand Image</label>
            <input type="file" name="logo" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @if($brand->logo)
                <img src="{{ '/storage/' . ltrim($brand->logo ?? '', '/') }}" alt="{{ $brand->name }}" class="h-16 w-16 rounded object-cover mt-3">
            @endif
            @error('logo')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order) }}" min="0" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            @error('sort_order')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $brand->is_active) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-indigo-500">
            <span class="ml-2 text-sm text-gray-300">Active</span>
        </label>
        <div class="flex justify-end gap-4 pt-2">
            <a href="{{ route('admin.products.brands.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium">Save Changes</button>
        </div>
    </form>
</div>
@endsection

