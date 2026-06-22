@extends('admin.layout')

@section('title', 'Edit Media')
@section('header', 'Edit Media')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-white">Edit Media</h3>
            <p class="text-gray-400 mt-1">Update media title and alt text for SEO</p>
        </div>
        <a href="{{ route('admin.media.show', $media) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h4 class="text-lg font-semibold text-white mb-4">Preview</h4>
            <div class="bg-gray-900 rounded-lg p-8 flex items-center justify-center" style="min-height: 300px;">
                @if($media->type === 'image')
                    <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" class="max-w-full max-h-72 rounded-lg shadow-lg">
                @elseif($media->type === 'document')
                    <div class="text-center">
                        <svg class="w-24 h-24 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-400">{{ $media->original_name }}</p>
                    </div>
                @elseif($media->type === 'video')
                    <video controls class="max-w-full max-h-72 rounded-lg">
                        <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                    </video>
                @else
                    <div class="text-center">
                        <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-gray-400">{{ $media->original_name }}</p>
                    </div>
                @endif
            </div>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">File Type:</span>
                    <span class="text-white capitalize">{{ $media->type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">File Size:</span>
                    <span class="text-white">{{ $media->formatted_size }}</span>
                </div>
                @if($media->width && $media->height)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Dimensions:</span>
                        <span class="text-white">{{ $media->width }} × {{ $media->height }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <form action="{{ route('admin.media.update', $media) }}" method="POST" class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                            Title
                            <span class="text-gray-500">(Optional)</span>
                        </label>
                        <input 
                            type="text" 
                            name="title" 
                            id="title" 
                            value="{{ old('title', $media->title) }}" 
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from @error('title') border-red-500 @enderror"
                            placeholder="Enter a descriptive title"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">A descriptive title helps with organization and SEO</p>
                    </div>

                    <div>
                        <label for="alt_text" class="block text-sm font-medium text-gray-300 mb-2">
                            Alt Text
                            <span class="text-red-400">*</span>
                        </label>
                        <textarea 
                            name="alt_text" 
                            id="alt_text" 
                            rows="3"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from @error('alt_text') border-red-500 @enderror"
                            placeholder="Describe what this image shows"
                        >{{ old('alt_text', $media->alt_text) }}</textarea>
                        @error('alt_text')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Alt text is crucial for accessibility and SEO. Describe the image content clearly.</p>
                    </div>

                    <div class="bg-blue-900 bg-opacity-30 border border-blue-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-200">
                                <p class="font-medium mb-1">Alt Text Best Practices:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Be specific and descriptive</li>
                                    <li>Keep it under 125 characters when possible</li>
                                    <li>Don't start with "image of" or "picture of"</li>
                                    <li>Include important text visible in the image</li>
                                    <li>Describe the context and purpose</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-700 flex items-center justify-between">
                        <a href="{{ route('admin.media.show', $media) }}" class="text-gray-400 hover:text-white">
                            Cancel
                        </a>
                        <div class="flex items-center space-x-2">
                            <button 
                                type="button" 
                                onclick="autoGenerateAlt()" 
                                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm"
                            >
                                Auto-Generate
                            </button>
                            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">
                                Update Media
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-6 bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">File Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Original Name:</span>
                        <span class="text-white">{{ $media->original_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">File Name:</span>
                        <span class="text-white">{{ $media->file_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Upload Date:</span>
                        <span class="text-white">{{ $media->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Last Modified:</span>
                        <span class="text-white">{{ $media->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function autoGenerateAlt() {
    const filename = '{{ $media->original_name }}';
    const title = document.getElementById('title').value;
    
    let altText = title || filename.replace(/\.[^/.]+$/, '');
    altText = altText.replace(/[-_]/g, ' ');
    altText = altText.replace(/\b\w/g, l => l.toUpperCase());
    
    document.getElementById('alt_text').value = altText;
}
</script>
@endsection

