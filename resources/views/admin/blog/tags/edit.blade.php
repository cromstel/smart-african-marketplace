@extends('admin.layout')

@section('title', 'Edit Blog Tag')
@section('header', 'Edit Blog Tag')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.blog.tags.index') }}" class="text-brand-from hover:text-orange-300 inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Tags
    </a>
</div>

<div class="max-w-2xl">
    <form action="{{ route('admin.blog.tags.update', $blogTag) }}" method="POST" class="bg-gray-800 rounded-xl border border-gray-700 p-6">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Tag Name *</label>
                <input type="text" name="name" value="{{ old('name', $blogTag->name) }}" required
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-brand-from focus:border-brand-from"
                       placeholder="e.g., Laravel, React, Tutorial">
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-3 focus:ring-brand-from focus:border-brand-from"
                          placeholder="Brief description of this tag (optional)">{{ old('description', $blogTag->description) }}</textarea>
                @error('description')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Max 500 characters</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Tag Color</label>
                <div class="flex items-center gap-4">
                    <input type="color" name="color" value="{{ old('color', $blogTag->color) }}" id="color-picker"
                           class="w-16 h-12 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer">
                    <input type="text" id="color-hex" value="{{ old('color', $blogTag->color) }}" readonly
                           class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono">
                </div>
                @error('color')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Choose a color for this tag</p>
            </div>

            <div class="bg-gray-900 rounded-lg p-4 border border-gray-700">
                <h3 class="text-white font-semibold mb-3">Tag Info</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Current Slug:</span>
                        <code class="text-purple-400 bg-gray-800 px-2 py-1 rounded text-sm">{{ $blogTag->slug }}</code>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Total Posts:</span>
                        <span class="text-white font-medium">{{ $blogTag->posts()->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 text-sm">Created:</span>
                        <span class="text-white font-medium text-sm">{{ $blogTag->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-6 pt-6 border-t border-gray-700">
            <a href="{{ route('admin.blog.tags.index') }}" class="px-6 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition">
                Cancel
            </a>
            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                Update Tag
            </button>
        </div>
    </form>
</div>

<script>
const colorPicker = document.getElementById('color-picker');
const colorHex = document.getElementById('color-hex');

colorPicker.addEventListener('input', function() {
    colorHex.value = this.value;
});
</script>
@endsection

