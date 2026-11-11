@extends('admin.layout')

@section('title', 'Edit About Section')
@section('header', 'Edit About Section')

@section('content')
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.about.index') }}" class="text-gray-400 hover:text-white inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to About Page
        </a>
    </div>

    <div class="bg-gray-800 rounded-xl border border-gray-700 p-8">
        <form action="{{ route('admin.about.update', $about) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Section Key*</label>
                <input 
                    type="text" 
                    name="section_key" 
                    value="{{ old('section_key', $about->section_key) }}"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    placeholder="e.g., our_story, mission, values"
                    required
                >
                <p class="text-xs text-gray-400 mt-1">Unique identifier for this section (lowercase, underscore separated)</p>
                @error('section_key')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Title*</label>
                <input 
                    type="text" 
                    name="title" 
                    value="{{ old('title', $about->title) }}"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    placeholder="Section Title"
                    required
                >
                @error('title')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Content</label>
                <textarea 
                    name="content" 
                    rows="6"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    placeholder="Main content for this section"
                >{{ old('content', $about->content) }}</textarea>
                @error('content')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Items (JSON)</label>
                <textarea 
                    name="items" 
                    rows="8"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white font-mono text-sm focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    placeholder='[{"icon": "🎯", "title": "Quality", "description": "..."}, ...]'
                >{{ old('items', $about->items ? json_encode($about->items, JSON_PRETTY_PRINT) : '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Optional: JSON array of items for lists or cards</p>
                @error('items')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Section Image</label>
                @if($about->image)
                    <div class="mb-3">
                        <img src="{{ '/storage/' . ltrim($about->image ?? '', '/') }}" alt="{{ $about->title }}" class="h-32 rounded-lg">
                        <p class="text-xs text-gray-400 mt-1">Current image (will be replaced if you upload a new one)</p>
                    </div>
                @endif
                <input 
                    type="file" 
                    name="image" 
                    accept="image/*"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-gray-300 focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                >
                @error('image')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Or Replace With Media Library Image</label>
                    <select 
                        name="image_media_id"
                        class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    >
                        <option value="">Keep current image</option>
                        @foreach($mediaImages as $mediaImage)
                            <option value="{{ $mediaImage->id }}" {{ old('image_media_id') == $mediaImage->id ? 'selected' : '' }}>
                                {{ $mediaImage->title ?: $mediaImage->original_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">If both are provided, uploaded file is used.</p>
                    @error('image_media_id')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                <input 
                    type="number" 
                    name="sort_order" 
                    value="{{ old('sort_order', $about->sort_order) }}"
                    class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:border-brand-from focus:ring-1 focus:ring-brand-from"
                    placeholder="0"
                >
                <p class="text-xs text-gray-400 mt-1">Lower numbers appear first</p>
                @error('sort_order')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        {{ old('is_active', $about->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 text-brand-from bg-gray-900 border-gray-700 rounded focus:ring-brand-from"
                    >
                    <span class="ml-3 text-gray-300">Active (show on About page)</span>
                </label>
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit" 
                    class="bg-brand-from hover:opacity-90 text-white px-8 py-3 rounded-lg font-medium"
                >
                    Update Section
                </button>
                <a 
                    href="{{ route('admin.about.index') }}" 
                    class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

