@extends('admin.layout')

@section('title', 'Edit Hero Section')
@section('header', 'Edit Hero Section')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.hero-sections.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
        ← Back to Hero Sections
    </a>
</div>

<form action="{{ route('admin.hero-sections.update', $heroSection) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Basic Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Page Identifier *</label>
                <select name="page_identifier" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="">Select Page</option>
                    <option value="home" {{ $heroSection->page_identifier == 'home' ? 'selected' : '' }}>Home Page</option>
                    <option value="shop" {{ $heroSection->page_identifier == 'shop' ? 'selected' : '' }}>Shop Page</option>
                    <option value="salon" {{ $heroSection->page_identifier == 'salon' ? 'selected' : '' }}>Salon Page</option>
                    <option value="about" {{ $heroSection->page_identifier == 'about' ? 'selected' : '' }}>About Page</option>
                    <option value="contact" {{ $heroSection->page_identifier == 'contact' ? 'selected' : '' }}>Contact Page</option>
                </select>
                @error('page_identifier') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Order</label>
                <input type="number" name="order" value="{{ old('order', $heroSection->order) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @error('order') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $heroSection->title) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @error('title') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Subtitle</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $heroSection->subtitle) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                @error('subtitle') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('description', $heroSection->description) }}</textarea>
                @error('description') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Background Settings</h3>
        
        <div class="bg-amber-900 border border-amber-700 rounded-lg p-4 mb-6">
            <p class="text-amber-100 text-sm">
                <strong>Brand Colors:</strong> The orange-to-red gradient (#ea580c → #dc2626) is enforced globally to maintain brand consistency.
            </p>
        </div>
        
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Background Type *</label>
                <select name="background_type" id="background_type" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    <option value="gradient" {{ $heroSection->background_type == 'gradient' ? 'selected' : '' }}>Brand Gradient (Orange to Red)</option>
                    <option value="image" {{ $heroSection->background_type == 'image' ? 'selected' : '' }}>Image with Brand Gradient Overlay</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Images will blend with the brand gradient using overlay mode</p>
            </div>

            <input type="hidden" name="gradient_from" value="#ea580c">
            <input type="hidden" name="gradient_to" value="#dc2626">

            <div id="image_field" style="display: {{ $heroSection->background_type == 'image' ? 'block' : 'none' }};">
                @if($heroSection->background_image)
                    <div class="mb-4">
                        <p class="text-sm text-gray-400 mb-2">Current Image:</p>
                        <img src="{{ '/storage/' . ltrim($heroSection->background_image ?? '', '/') }}" alt="Background" class="w-full max-w-md h-32 object-cover rounded-lg border border-gray-600">
                    </div>
                @endif
                <label class="block text-sm font-medium text-gray-300 mb-2">Background Image {{ $heroSection->background_image ? '(Upload new to replace)' : '' }}</label>
                <input type="file" name="background_image" accept="image/*" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <p class="text-xs text-gray-500 mt-1">Max 2MB. Recommended: 1920x600px. Image will overlay with brand gradient.</p>
                @error('background_image') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Or Replace With Media Library Image</label>
                    <select name="background_media_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">Keep current image</option>
                        @foreach($mediaImages as $mediaImage)
                            <option value="{{ $mediaImage->id }}" {{ old('background_media_id') == $mediaImage->id ? 'selected' : '' }}>
                                {{ $mediaImage->title ?: $mediaImage->original_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">If both are provided, uploaded file is used.</p>
                    @error('background_media_id') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Statistics</h3>
        <p class="text-sm text-gray-400 mb-4">Add statistics cards (JSON format). Example: [{"value":"100+","label":"Products"},{"value":"50+","label":"Categories"}]</p>
        <textarea name="statistics" rows="4" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm">{{ old('statistics', json_encode($heroSection->statistics)) }}</textarea>
        @error('statistics') <p class="text-red-400 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Call-to-Action Buttons</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Primary Button Text</label>
                <input type="text" name="primary_button_text" value="{{ old('primary_button_text', $heroSection->primary_button_text) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Primary Button Link</label>
                <input type="text" name="primary_button_link" value="{{ old('primary_button_link', $heroSection->primary_button_link) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Secondary Button Text</label>
                <input type="text" name="secondary_button_text" value="{{ old('secondary_button_text', $heroSection->secondary_button_text) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Secondary Button Link</label>
                <input type="text" name="secondary_button_link" value="{{ old('secondary_button_link', $heroSection->secondary_button_link) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1" {{ $heroSection->is_active ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 rounded">
            <span class="ml-2 text-gray-300">Active (Display this hero section)</span>
        </label>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-lg font-medium">
            Update Hero Section
        </button>
        <a href="{{ route('admin.hero-sections.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-medium">
            Cancel
        </a>
    </div>
</form>

<script>
document.getElementById('background_type').addEventListener('change', function() {
    const imageField = document.getElementById('image_field');
    
    if (this.value === 'image') {
        imageField.style.display = 'block';
    } else {
        imageField.style.display = 'none';
    }
});
</script>
@endsection

