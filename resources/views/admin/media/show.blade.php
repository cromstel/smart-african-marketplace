@extends('admin.layout')

@section('title', 'Media Details')
@section('header', 'Media Details')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-white">{{ $media->title ?? $media->original_name }}</h3>
            <p class="text-gray-400 mt-1">View media details and metadata</p>
        </div>
        <div class="flex items-center space-x-2">
            @if($media->type === 'image')
                <button type="button" onclick="showReplaceModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Replace
                </button>
            @endif
            <a href="{{ route('admin.media.edit', $media) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('admin.media.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Back
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <div class="bg-gray-900 rounded-lg p-8 flex items-center justify-center mb-6" style="min-height: 400px;">
                    @if($media->type === 'image')
                        <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" class="max-w-full max-h-96 rounded-lg shadow-lg">
                    @elseif($media->type === 'document')
                        <div class="text-center">
                            <svg class="w-32 h-32 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-400">{{ $media->original_name }}</p>
                        </div>
                    @elseif($media->type === 'video')
                        <video controls class="max-w-full max-h-96 rounded-lg">
                            <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                        </video>
                    @else
                        <div class="text-center">
                            <svg class="w-32 h-32 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400">{{ $media->original_name }}</p>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-700 pt-6">
                    <h4 class="text-lg font-semibold text-white mb-4">File URLs</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">File URL</label>
                            <div class="flex items-center space-x-2">
                                <input type="text" value="{{ $media->url }}" readonly class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" id="fileUrl">
                                <button onclick="copyToClipboard('fileUrl')" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">Copy</button>
                            </div>
                        </div>
                        @if($media->webp_url)
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">WebP URL</label>
                                <div class="flex items-center space-x-2">
                                    <input type="text" value="{{ $media->webp_url }}" readonly class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" id="webpUrl">
                                    <button onclick="copyToClipboard('webpUrl')" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">Copy</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">File Details</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">File Name</p>
                        <p class="text-white font-medium">{{ $media->file_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Original Name</p>
                        <p class="text-white font-medium">{{ $media->original_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">File Type</p>
                        <p class="text-white font-medium capitalize">{{ $media->type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">MIME Type</p>
                        <p class="text-white font-medium">{{ $media->mime_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">File Size</p>
                        <p class="text-white font-medium">{{ $media->formatted_size }}</p>
                    </div>
                    @if($media->width && $media->height)
                        <div>
                            <p class="text-sm text-gray-400">Dimensions</p>
                            <p class="text-white font-medium">{{ $media->width }} × {{ $media->height }}</p>
                        </div>
                    @endif
                    @if($media->type === 'image')
                        <div>
                            <p class="text-sm text-gray-400">Optimization Status</p>
                            <p class="text-white font-medium">
                                @if($media->is_optimized)
                                    <span class="text-green-400">Optimized</span>
                                @else
                                    <span class="text-yellow-400">Not Optimized</span>
                                @endif
                            </p>
                        </div>
                        @if($media->original_file_path)
                            <div>
                                <p class="text-sm text-gray-400">Original File</p>
                                <p class="text-white font-medium text-xs">{{ $media->original_file_path }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">SEO Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Title</p>
                        <p class="text-white font-medium">{{ $media->title ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Alt Text</p>
                        <p class="text-white font-medium">{{ $media->alt_text ?? 'Not set' }}</p>
                    </div>
                    <form action="{{ route('admin.media.regenerate-alt', $media) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Regenerate Alt Text
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h4 class="text-lg font-semibold text-white mb-4">Upload Information</h4>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Uploaded By</p>
                        <p class="text-white font-medium">
                            @if($media->uploadedBy)
                                {{ $media->uploadedBy->name }}
                            @else
                                System
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Upload Date</p>
                        <p class="text-white font-medium">{{ $media->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Last Modified</p>
                        <p class="text-white font-medium">{{ $media->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                    @if($media->context)
                        <div>
                            <p class="text-sm text-gray-400">Context</p>
                            <p class="text-white font-medium capitalize">{{ str_replace('_', ' ', $media->context) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('admin.media.destroy', $media) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this media? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    Delete Media
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    document.execCommand('copy');
    
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    
    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}

function showReplaceModal() {
    const modal = document.getElementById('replaceModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function hideReplaceModal() {
    const modal = document.getElementById('replaceModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<!-- Replace Modal -->
<div id="replaceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-white mb-4">Replace Image</h3>
        <p class="text-gray-400 mb-4">Upload a new image to replace the current one. The new image will be optimized according to your settings.</p>
        
        <form action="{{ route('admin.media.replace', $media) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Image</label>
                <input type="file" name="file" accept="image/jpeg,image/jpg,image/png,image/webp" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <p class="text-xs text-gray-400 mt-1">Supported formats: JPEG, JPG, PNG, WebP (max 10MB)</p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideReplaceModal()" 
                    class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    Replace
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

