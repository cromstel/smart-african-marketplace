@extends('vendor.layout')

@extends('vendor.layout')

@section('title', 'Media Library - ' . config('app.name', 'Smart African Market'))
@section('header', 'Media Library')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-gray-400">Manage your images and files</p>
        <div class="d-flex gap-2">
            <button onclick="openModal('uploadModal')" class="btn btn-primary">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload Files
            </button>
            <button onclick="openModal('folderModal')" class="btn btn-secondary">
                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h5l2 2h11v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                New Folder
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-gray-800 border-gray-700">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-gray-400 small mb-0">Total Files</p>
                            <h4 class="text-white mb-0">{{ number_format($stats['total']) }}</h4>
                        </div>
                        <div class="bg-blue-900 p-3 rounded">
                            <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gray-800 border-gray-700">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-gray-400 small mb-0">Images</p>
                            <h4 class="text-white mb-0">{{ number_format($stats['images']) }}</h4>
                        </div>
                        <div class="bg-green-900 p-3 rounded">
                            <svg class="w-5 h-5 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-gray-800 border-gray-700">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-gray-400 small mb-0">Storage Used</p>
                            <h4 class="text-white mb-0">{{ number_format($stats['total_size'] / 1048576, 2) }} MB</h4>
                        </div>
                        <div class="bg-purple-900 p-3 rounded">
                            <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('vendor.media.index') }}" class="card bg-gray-800 border-gray-700 p-3 mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-gray-400 small">Type</label>
                <select name="type" class="form-control bg-gray-700 border-gray-600 text-white">
                    <option value="">All Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Videos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-gray-400 small">Folder</label>
                <select name="folder_id" class="form-control bg-gray-700 border-gray-600 text-white">
                    <option value="">All Folders</option>
                    @foreach($folders as $folder)
                        <option value="{{ $folder->id }}" {{ request('folder_id') == $folder->id ? 'selected' : '' }}>
                            {{ $folder->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-gray-400 small">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or alt text..." class="form-control bg-gray-700 border-gray-600 text-white">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    @if(request()->hasAny(['type', 'folder_id', 'search']))
                        <a href="{{ route('vendor.media.index') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <!-- Bulk Actions -->
    <form id="bulkActionForm" method="POST">
        @csrf
        <div class="card bg-gray-800 border-gray-700 p-3 mb-4 {{ count($media) > 0 ? '' : 'd-none' }}" id="bulkActions">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-white"><span id="selectedCount">0</span> items selected</span>
                <div class="d-flex gap-2">
                    <button type="button" onclick="bulkAction('delete')" class="btn btn-danger btn-sm">
                        Delete Selected
                    </button>
                </div>
            </div>
        </div>

        <!-- Media Table -->
        <div class="card bg-gray-800 border-gray-700">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3" style="width: 40px;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="form-check-input">
                            </th>
                            <th class="px-3">Preview</th>
                            <th>Name</th>
                            <th>Alt Text</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Dimensions</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($media as $item)
                            <tr>
                                <td class="px-3">
                                    <input type="checkbox" name="media_ids[]" value="{{ $item->id }}" class="form-check-input media-checkbox" onchange="updateBulkActions()">
                                </td>
                                <td class="px-3">
                                    <div class="bg-gray-900 rounded d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        @if($item->type === 'image')
                                            <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="rounded" style="width: 100%; height: 100%; object-fit: cover;">
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
                                <td>
                                    <span class="text-white fw-medium">{{ $item->title ?? $item->original_name }}</span>
                                </td>
                                <td class="text-gray-400">{{ $item->alt_text ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->type === 'image' ? 'primary' : ($item->type === 'document' ? 'warning' : ($item->type === 'video' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </td>
                                <td class="text-gray-400">{{ $item->formatted_size }}</td>
                                <td class="text-gray-400">
                                    @if($item->width && $item->height)
                                        {{ $item->width }} &times; {{ $item->height }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('vendor.media.show', $item) }}" class="btn btn-sm btn-outline-light">View</a>
                                        <a href="{{ route('vendor.media.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <button type="button" onclick="deleteMedia({{ $item->id }})" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <svg class="w-16 h-16 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-gray-400 mb-3">No media files found.</p>
                                    <button type="button" onclick="openModal('uploadModal')" class="btn btn-primary">Upload your first file</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <div class="mt-4">
        {{ $media->links() }}
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-gray-800 border-gray-700">
            <div class="modal-header border-gray-700">
                <h5 class="modal-title text-white">Upload Files</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.media.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-gray-300">Folder (optional)</label>
                        <select name="folder_id" class="form-control bg-gray-700 border-gray-600 text-white">
                            <option value="">No Folder</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="border-2 border-dashed border-gray-600 rounded p-5 text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-gray-300 mb-2">Drag and drop files here or click to browse</p>
                        <p class="text-xs text-gray-400 mb-3">Supported: Images (JPG, PNG, SVG, WebP), Documents (PDF, DOC, XLS), Videos (MP4, MOV, AVI)</p>
                        <input type="file" name="files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-gray-700">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Files</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Folder Modal -->
<div class="modal fade" id="folderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-gray-800 border-gray-700">
            <div class="modal-header border-gray-700">
                <h5 class="modal-title text-white">Create Folder</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('vendor.media.folders.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-gray-300">Folder Name</label>
                        <input type="text" name="name" required class="form-control bg-gray-700 border-gray-600 text-white">
                    </div>
                    <div>
                        <label class="form-label text-gray-300">Parent Folder (optional)</label>
                        <select name="parent_id" class="form-control bg-gray-700 border-gray-600 text-white">
                            <option value="">No Parent</option>
                            @foreach($folders as $folder)
                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-gray-700">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.media-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    selectedCount.textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        bulkActions.classList.remove('d-none');
    } else {
        bulkActions.classList.add('d-none');
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
        form.action = '{{ route("vendor.media.bulk-delete") }}';
    }
    
    form.submit();
}

function deleteMedia(id) {
    if (!confirm('Are you sure you want to delete this media?')) {
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

