@extends('vendor.layout')

@section('title', 'Edit Media - ' . config('app.name', 'Smart African Market'))
@section('header', 'Edit Media')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Media
        </a>
        <button type="button" onclick="deleteMedia({{ $media->id }})" class="btn btn-danger">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">Preview</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        @if($media->type === 'image')
                            <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" class="img-fluid rounded" style="max-height: 500px;">
                        @elseif($media->type === 'video')
                            <video controls class="w-100 rounded" style="max-height: 500px;">
                                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <div class="bg-gray-900 rounded p-5 d-inline-block">
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">File Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-dark table-borderless">
                        <tr>
                            <td class="text-gray-400">File Name</td>
                            <td class="text-white">{{ $media->original_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-400">Type</td>
                            <td><span class="badge bg-primary">{{ ucfirst($media->type) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-gray-400">Size</td>
                            <td class="text-white">{{ $media->formatted_size }}</td>
                        </tr>
                        @if($media->width && $media->height)
                        <tr>
                            <td class="text-gray-400">Dimensions</td>
                            <td class="text-white">{{ $media->width }} × {{ $media->height }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-gray-400">Uploaded</td>
                            <td class="text-white">{{ $media->created_at->format('M j, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card bg-gray-800 border-gray-700">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">Edit Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.media.update', $media) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Title</label>
                            <input type="text" name="title" value="{{ old('title', $media->title) }}" class="form-control bg-gray-700 border-gray-600 text-white">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Alt Text</label>
                            <input type="text" name="alt_text" value="{{ old('alt_text', $media->alt_text) }}" class="form-control bg-gray-700 border-gray-600 text-white">
                            <small class="text-gray-400">Used for SEO and accessibility</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Caption</label>
                            <textarea name="caption" rows="3" class="form-control bg-gray-700 border-gray-600 text-white">{{ old('caption', $media->caption) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Description</label>
                            <textarea name="description" rows="3" class="form-control bg-gray-700 border-gray-600 text-white">{{ old('description', $media->description) }}</textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Replace File -->
            <div class="card bg-gray-800 border-gray-700 mt-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">Replace File</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.media.replace', $media) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="file" accept="{{ $media->type === 'image' ? 'image/*' : ($media->type === 'video' ? 'video/*' : '*') }}" class="form-control bg-gray-700 border-gray-600 text-white" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Replace File</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

