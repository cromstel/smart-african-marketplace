@extends('admin.layout')

@section('title', 'Add New Media')
@section('header', 'Add New Media')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-white">Upload New Media</h3>
            <p class="text-gray-400 mt-1">Upload images, documents, and other files</p>
        </div>
        <a href="{{ route('admin.media.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Library
        </a>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
    <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-300 mb-2">Folder (optional)</label>
            <select name="folder_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">No Folder</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center mb-4">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <p class="text-gray-300 mb-2">Drag and drop files here or click to browse</p>
            <p class="text-xs text-gray-500">Supported: Images (JPG, PNG, GIF, SVG, WebP), Documents (PDF, DOC, XLS), Videos (MP4, MOV, AVI)</p>
            <input type="file" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-4" required>
        </div>
        <div class="flex items-center justify-end space-x-2">
            <a href="{{ route('admin.media.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Cancel
            </a>
            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">
                Upload Files
            </button>
        </div>
    </form>
</div>
@endsection

