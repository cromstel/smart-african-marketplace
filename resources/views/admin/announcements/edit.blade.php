@extends('admin.layout')

@section('title', 'Edit Announcement')
@section('header', 'Edit Announcement')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.announcements.index') }}" class="text-blue-400 hover:text-blue-300 inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Announcements
        </a>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required
                       class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-300 mb-2">Content *</label>
                <textarea name="content" id="content" rows="6" required
                          class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-300 mb-2">Type *</label>
                    <select name="type" id="type" required
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="success" {{ old('type', $announcement->type) === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="danger" {{ old('type', $announcement->type) === 'danger' ? 'selected' : '' }}>Danger</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target" class="block text-sm font-medium text-gray-300 mb-2">Target *</label>
                    <select name="target" id="target" required
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onchange="toggleVendorSelection()">
                        <option value="all_vendors" {{ old('target', $announcement->target) === 'all_vendors' ? 'selected' : '' }}>All Vendors</option>
                        <option value="specific_vendors" {{ old('target', $announcement->target) === 'specific_vendors' ? 'selected' : '' }}>Specific Vendors</option>
                    </select>
                    @error('target')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="vendor-selection" class="mb-6" style="display: {{ old('target', $announcement->target) === 'specific_vendors' ? 'block' : 'none' }}">
                <label class="block text-sm font-medium text-gray-300 mb-2">Select Vendors</label>
                <div class="bg-gray-700 border border-gray-600 rounded-lg p-4 max-h-60 overflow-y-auto">
                    @foreach($vendors as $vendor)
                        <label class="flex items-center py-2 hover:bg-gray-600 rounded px-2 cursor-pointer">
                            <input type="checkbox" name="vendor_ids[]" value="{{ $vendor->id }}"
                                   {{ (is_array(old('vendor_ids')) ? in_array($vendor->id, old('vendor_ids')) : in_array($vendor->id, $announcement->vendor_ids ?? [])) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 focus:ring-2 bg-gray-600 border-gray-500">
                            <span class="ml-3 text-sm text-gray-300">{{ $vendor->name }} ({{ $vendor->email }})</span>
                        </label>
                    @endforeach
                </div>
                @error('vendor_ids')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-300 mb-2">Publish Date (Optional)</label>
                    <input type="datetime-local" name="published_at" id="published_at" 
                           value="{{ old('published_at', $announcement->published_at ? $announcement->published_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Leave empty to publish immediately</p>
                    @error('published_at')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-300 mb-2">Expiry Date (Optional)</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" 
                           value="{{ old('expires_at', $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}"
                           class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-400">Leave empty for no expiry</p>
                    @error('expires_at')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-6 mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 focus:ring-2 bg-gray-600 border-gray-500">
                    <span class="ml-2 text-sm text-gray-300">Active</span>
                </label>

                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}
                           class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 focus:ring-2 bg-gray-600 border-gray-500">
                    <span class="ml-2 text-sm text-gray-300">Pin to Top</span>
                </label>
            </div>

            <div class="bg-gray-700 border border-gray-600 rounded-lg p-4 mb-6">
                <p class="text-sm text-gray-300"><strong>Reads:</strong> {{ $announcement->reads()->count() }} vendors have read this announcement</p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">
                    Update Announcement
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleVendorSelection() {
    const target = document.getElementById('target').value;
    const vendorSelection = document.getElementById('vendor-selection');
    vendorSelection.style.display = target === 'specific_vendors' ? 'block' : 'none';
}
</script>
@endsection

