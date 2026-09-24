@extends('vendor.layout')

@section('title', 'Upload Media - ' . config('app.name', 'Smart African Market'))
@section('header', 'Upload Media')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Media
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-gray-800 border-gray-700">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0 text-white">Upload New Media</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.media.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Folder (optional)</label>
                            <select name="folder_id" class="form-control bg-gray-700 border-gray-600 text-white">
                                <option value="">Default Folder</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-gray-300">Files</label>
                            <input type="file" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" class="form-control bg-gray-700 border-gray-600 text-white" required>
                            <small class="text-gray-400">Images (JPG, PNG, WebP), documents (PDF, DOC, XLS), or videos (MP4, MOV, AVI) up to 10MB each</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Upload Files</button>
                            <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
