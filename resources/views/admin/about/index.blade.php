@extends('admin.layout')

@section('title', 'About Page Management')
@section('header', 'About Page Management')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-white">Manage About Page Content</h2>
        <p class="text-gray-400 mt-1">Edit and organize sections on the About page</p>
    </div>
    <a href="{{ route('admin.about.create') }}" class="bg-brand-from hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Section
    </a>
</div>

<div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-900 border-b border-gray-700">
                <tr>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Section Key</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Title</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Order</th>
                    <th class="text-left px-6 py-4 text-sm font-semibold text-gray-300">Status</th>
                    <th class="text-right px-6 py-4 text-sm font-semibold text-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($contents as $content)
                    <tr class="hover:bg-gray-750 transition">
                        <td class="px-6 py-4">
                            <span class="text-gray-300 font-mono text-sm">{{ $content->section_key }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-white font-medium">{{ $content->title }}</div>
                            @if($content->content)
                                <div class="text-sm text-gray-400 mt-1">{{ Str::limit($content->content, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-300">{{ $content->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <button 
                                onclick="toggleStatus({{ $content->id }}, this)"
                                class="px-3 py-1 rounded-full text-xs font-medium {{ $content->is_active ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' }}"
                            >
                                {{ $content->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.about.edit', $content) }}" class="text-blue-400 hover:text-blue-300" aria-label="Edit {{ $content->title }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('admin.about.destroy', $content) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this section?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300" aria-label="Delete {{ $content->title }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg">No sections yet</p>
                                <p class="text-sm mt-1">Add your first section to get started</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleStatus(id, button) {
    fetch(`/admin/about/${id}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_active) {
                button.className = 'px-3 py-1 rounded-full text-xs font-medium bg-green-900 text-green-300';
                button.textContent = 'Active';
            } else {
                button.className = 'px-3 py-1 rounded-full text-xs font-medium bg-gray-700 text-gray-400';
                button.textContent = 'Inactive';
            }
        }
    });
}
</script>
@endsection

