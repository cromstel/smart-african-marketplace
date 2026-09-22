@extends('admin.layout')

@section('title', 'Media Settings')
@section('header', 'Media Settings')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-white">Media Settings</h3>
            <p class="text-gray-400 mt-1">Configure media library settings</p>
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
    <form method="POST" action="{{ route('admin.media.settings.update') }}">
        @csrf
        @method('PUT')
        
        <div class="space-y-6">
            <div>
                <h4 class="text-lg font-medium text-white mb-4">Image Optimization</h4>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="auto_optimize" name="auto_optimize" class="rounded bg-gray-700 border-gray-600 text-brand-from focus:ring-brand-from" checked>
                        <label for="auto_optimize" class="ml-2 text-sm text-gray-300">Auto-optimize images on upload</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="generate_webp" name="generate_webp" class="rounded bg-gray-700 border-gray-600 text-brand-from focus:ring-brand-from" checked>
                        <label for="generate_webp" class="ml-2 text-sm text-gray-300">Automatically convert images to AVIF (best performance)</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="auto_delete_originals" name="auto_delete_originals" class="rounded bg-gray-700 border-gray-600 text-brand-from focus:ring-brand-from">
                        <label for="auto_delete_originals" class="ml-2 text-sm text-gray-300">Auto-delete original images after optimization</label>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-medium text-white mb-4">Image Dimensions</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Max Width (px)</label>
                        <input type="number" name="max_width" value="1200" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Max Height (px)</label>
                        <input type="number" name="max_height" value="1200" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-medium text-white mb-4">File Upload</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Max File Size (MB)</label>
                        <input type="number" name="max_file_size" value="10" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Allowed File Types</label>
                        <input type="text" name="allowed_types" value="jpeg,jpg,png,webp,svg,pdf,doc,docx,xls,xlsx,mp4,mov,avi" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <p class="text-xs text-gray-400 mt-1">Comma-separated list of allowed extensions</p>
                    </div>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-medium text-white mb-4">Alt Text</h4>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="auto_alt" name="auto_alt" class="rounded bg-gray-700 border-gray-600 text-brand-from focus:ring-brand-from">
                        <label for="auto_alt" class="ml-2 text-sm text-gray-300">Auto-generate alt text using AI</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-700 flex justify-end">
            <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-6 py-2 rounded-lg">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

