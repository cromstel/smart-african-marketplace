@extends('admin.layout')

@section('title', 'Media Library')
@section('header', 'Media Library')

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-white">Media Library</h3>
            <p class="text-gray-400 mt-1">Manage all uploaded images and files</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="openModal('uploadModal')" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload Files
            </button>
            <button onclick="openModal('folderModal')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h5l2 2h11v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                New Folder
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Files</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-blue-900 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Images</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['images']) }}</p>
                </div>
                <div class="bg-green-900 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Documents</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['documents']) }}</p>
                </div>
                <div class="bg-yellow-900 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Size</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_size'] / 1048576, 2) }} MB</p>
                </div>
                <div class="bg-purple-900 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.media.index') }}" class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-300 mb-2">Type</label>
                <select name="type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from">
                    <option value="">All Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Videos</option>
                </select>
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-300 mb-2">Folder</label>
                <select name="folder_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from">
                    <option value="">All Folders</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                            {{ $folder->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-300 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or alt text..." class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from">
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-300 mb-2">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from">
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-sm font-medium text-gray-300 mb-2">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-brand-from focus:border-brand-from">
            </div>
            <div class="flex-shrink-0">
                <span class="block text-sm font-medium text-transparent mb-2 select-none" aria-hidden="true">Actions</span>
                <div class="flex gap-2">
                    <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg whitespace-nowrap">Filter</button>
                    @if(request()->hasAny(['type', 'folder_id', 'search', 'date_from', 'date_to']))
                        <a href="{{ route('admin.media.index') }}" class="inline-flex items-center justify-center bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg whitespace-nowrap">Reset</a>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

<form id="bulkActionForm" method="POST">
    @csrf
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-4 hidden" id="bulkActions">
        <div class="flex items-center justify-between">
            <p class="text-white"><span id="selectedCount">0</span> items selected</p>
            <div class="space-x-2">
                <button type="button" onclick="bulkAction('regenerate-alt')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Regenerate Alt Text
                </button>
                <button type="button" onclick="bulkAction('delete')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                    Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Inline Layout for Media Items -->
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded">
                    </th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Preview</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Alt Text</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Type</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Size</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-400">Dimensions</th>
                    <th class="px-4 py-3 text-right text-sm font-medium text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($media as $item)
                    <tr class="hover:bg-gray-750 transition-colors">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="media_ids[]" value="{{ $item->id }}" class="media-checkbox rounded" onchange="updateBulkActions()">
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-12 h-12 bg-gray-900 rounded overflow-hidden flex items-center justify-center">
                                @if($item->type === 'image')
                                    <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="w-full h-full object-cover">
                                @elseif($item->type === 'document')
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @elseif($item->type === 'video')
                                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-white font-medium truncate max-w-xs" title="{{ $item->title ?? $item->original_name }}">
                                {{ $item->title ?? $item->original_name }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-400 truncate max-w-xs" title="{{ $item->alt_text }}">
                                {{ $item->alt_text ?? '-' }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->type === 'image' ? 'bg-blue-900 text-blue-300' : ($item->type === 'document' ? 'bg-yellow-900 text-yellow-300' : ($item->type === 'video' ? 'bg-purple-900 text-purple-300' : 'bg-gray-700 text-gray-300')) }}">
                                {{ ucfirst($item->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-400">{{ $item->formatted_size }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm text-gray-400">
                                @if($item->width && $item->height)
                                    {{ $item->width }} × {{ $item->height }}
                                @else
                                    -
                                @endif
                            </p>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('admin.media.show', $item) }}" class="text-xs text-blue-400 hover:text-blue-300">View</a>
                                <a href="{{ route('admin.media.edit', $item) }}" class="text-xs text-green-400 hover:text-green-300">Edit</a>
                                <button type="button" onclick="deleteMedia({{ $item->id }})" class="text-xs text-red-400 hover:text-red-300">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-gray-400">No media files found.</p>
                            <button type="button" onclick="openModal('uploadModal')" class="mt-4 text-brand-from hover:opacity-90">Upload your first file</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<div class="mt-6">
    {{ $media->links() }}
</div>

<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 max-w-2xl w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Upload Files</h3>
            <button onclick="closeModal('uploadModal')" class="text-gray-400 hover:text-white" aria-label="Close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
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
                <p class="text-xs text-gray-400">Supported: Images (JPG, PNG, SVG, WebP), Documents (PDF, DOC, XLS), Videos (MP4, MOV, AVI)</p>
                <input type="file" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" class="mt-4" required>
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button type="button" onclick="closeModal('uploadModal')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">
                    Upload Files
                </button>
            </div>
        </form>
    </div>
</div>

<div id="folderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6 max-w-xl w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-white">Create Folder</h3>
            <button onclick="closeModal('folderModal')" class="text-gray-400 hover:text-white" aria-label="Close">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('admin.media.folders.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Folder Name</label>
                    <input type="text" name="name" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Parent Folder (optional)</label>
                    <select name="parent_id" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="">No Parent</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-2 mt-6">
                <button type="button" onclick="closeModal('folderModal')" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Cancel
                </button>
                <button type="submit" class="bg-brand-from hover:opacity-90 text-white px-4 py-2 rounded-lg">
                    Create Folder
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    ['uploadModal', 'folderModal'].forEach((modalId) => {
        const modal = document.getElementById(modalId);
        if (modal && !modal.classList.contains('hidden')) {
            closeModal(modalId);
        }
    });
});

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.media-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        bulkActions.classList.remove('hidden');
    } else {
        bulkActions.classList.add('hidden');
    }
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const mediaCheckboxes = document.querySelectorAll('.media-checkbox');
    
    mediaCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function bulkAction(action) {
    const form = document.getElementById('bulkActionForm');
    const checkboxes = document.querySelectorAll('.media-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one item');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${checkboxes.length} items?`)) {
            return;
        }
        form.action = '{{ route("admin.media.bulk-delete") }}';
    } else if (action === 'regenerate-alt') {
        form.action = '{{ route("admin.media.bulk-regenerate-alt") }}';
    }
    
    form.submit();
}

function deleteMedia(id) {
    if (!confirm('Are you sure you want to delete this media?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/media/${id}`;
    
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


