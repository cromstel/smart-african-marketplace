@extends('vendor.layout')

@section('title', $media->title ?? $media->original_name . ' - ' . config('app.name', 'Smart African Market'))
@section('header', 'Media Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Media
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.media.edit', $media) }}" class="btn btn-primary">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <button type="button" onclick="deleteMedia({{ $media->id }})" class="btn btn-danger">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-gray-800 border-gray-700">
                <div class="card-body">
                    <div class="text-center">
                        @if($media->type === 'image')
                            <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" class="img-fluid rounded" style="max-height: 600px;">
                        @elseif($media->type === 'video')
                            <video controls class="w-100 rounded" style="max-height: 600px;">
                                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <div class="bg-gray-900 rounded p-5">
                                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-400 mt-3">{{ $media->original_name }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($media->description)
            <div class="card bg-gray-800 border-gray-700 mt-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">Description</h5>
                </div>
                <div class="card-body">
                    <p class="text-white">{{ $media->description }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">File Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-dark table-borderless">
                        <tr>
                            <td class="text-gray-400 py-2">File Name</td>
                            <td class="text-white py-2">{{ $media->original_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-400 py-2">Title</td>
                            <td class="text-white py-2">{{ $media->title ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-400 py-2">Type</td>
                            <td class="py-2"><span class="badge bg-primary">{{ ucfirst($media->type) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-400 py-2">Size</td>
                            <td class="text-white py-2">{{ $media->formatted_size }}</td>
                        </tr>
                        @if($media->width && $media->height)
                        <tr>
                            <td class="text-gray-400 py-2">Dimensions</td>
                            <td class="text-white py-2">{{ $media->width }} × {{ $media->height }}</td>
                        </tr>
                        @endif
                        @if($media->mime_type)
                        <tr>
                            <td class="text-gray-400 py-2">MIME Type</td>
                            <td class="text-white py-2"><small>{{ $media->mime_type }}</small></td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-gray-400 py-2">Uploaded</td>
                            <td class="text-white py-2">{{ $media->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($media->alt_text || $media->caption)
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">SEO & Accessibility</h5>
                </div>
                <div class="card-body">
                    @if($media->alt_text)
                    <div class="mb-3">
                        <label class="form-label text-gray-400 small">Alt Text</label>
                        <p class="text-white mb-0">{{ $media->alt_text }}</p>
                    </div>
                    @endif
                    @if($media->caption)
                    <div>
                        <label class="form-label text-gray-400 small">Caption</label>
                        <p class="text-white mb-0">{{ $media->caption }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="card bg-gray-800 border-gray-700">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">URL</h5>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control bg-gray-700 border-gray-600 text-white" value="{{ $media->url }}" readonly id="mediaUrl">
                        <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()" aria-label="Copy URL to clipboard">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                    <small class="text-gray-400 d-block mt-2">Use this URL to embed this file in your content</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyUrl() {
    const urlInput = document.getElementById('mediaUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(urlInput.value).then(() => {
        alert('URL copied to clipboard!');
    });
}

function deleteMedia(id) {
    if (!confirm('Are you sure you want to delete this media? This action cannot be undone.')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/vendor/media/${id}`;
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    
    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';
    
    form.appendChild(csrf);
    form.appendChild(method);
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection

