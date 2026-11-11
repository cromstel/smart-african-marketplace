@extends('admin.layout')

@section('title', 'Announcements')
@section('header', 'Vendor Announcements')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">Announcements</h3>
        <p class="text-gray-400 mt-1">Communicate with vendors through announcements</p>
    </div>
    <a href="{{ route('admin.announcements.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        New Announcement
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Total Announcements</span>
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $stats['total'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Active</span>
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $stats['active'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Pinned</span>
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $stats['pinned'] }}</p>
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-sm">Expired</span>
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-3xl font-bold text-white">{{ $stats['expired'] }}</p>
    </div>
</div>

<div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="bg-gray-900">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Target</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Reads</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Published</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            @forelse($announcements as $announcement)
                <tr class="hover:bg-gray-750">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($announcement->is_pinned)
                                <svg class="w-4 h-4 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                </svg>
                            @endif
                            <span class="text-sm font-medium text-white">{{ $announcement->title }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $typeColors = [
                                'info' => 'bg-blue-900 text-blue-300',
                                'warning' => 'bg-amber-900 text-amber-300',
                                'success' => 'bg-green-900 text-green-300',
                                'danger' => 'bg-red-900 text-red-300',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded {{ $typeColors[$announcement->type] }}">{{ ucfirst($announcement->type) }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $announcement->target === 'all_vendors' ? 'All Vendors' : count($announcement->vendor_ids ?? []) . ' Vendors' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        {{ $announcement->reads_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center">
                                @if($announcement->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-900 text-green-300 cursor-pointer hover:bg-green-800">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-700 text-gray-400 cursor-pointer hover:bg-gray-600">Inactive</span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                        @if($announcement->published_at)
                            @if($announcement->published_at->isFuture())
                                Scheduled {{ $announcement->published_at->format('M j, Y') }}
                            @else
                                {{ $announcement->published_at->format('M j, Y') }}
                            @endif
                        @else
                            Immediately
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <form action="{{ route('admin.announcements.togglePin', $announcement) }}" method="POST" class="inline mr-2">
                            @csrf
                            <button type="submit" class="text-amber-400 hover:text-amber-300" title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}">
                                <svg class="w-4 h-4" fill="{{ $announcement->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 20 20">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                            </button>
                        </form>
                        <a href="{{ route('admin.announcements.edit', $announcement) }}" class="text-blue-400 hover:text-blue-300 mr-3">Edit</a>
                        <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                        No announcements found. <a href="{{ route('admin.announcements.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($announcements->hasPages())
    <div class="mt-6">
        {{ $announcements->links() }}
    </div>
@endif
@endsection

