@extends('admin.layout')

@section('title', 'View Announcement')
@section('header', 'View Announcement')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.announcements.index') }}" class="inline-flex items-center text-gray-400 hover:text-white">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Announcements
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-start space-x-3">
                    <span class="text-3xl">{{ $announcement->type_icon }}</span>
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $announcement->title }}</h2>
                        <div class="flex items-center space-x-3 mt-2">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $announcement->type === 'info' ? 'bg-blue-900 text-blue-300' : '' }}
                                {{ $announcement->type === 'warning' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                {{ $announcement->type === 'success' ? 'bg-green-900 text-green-300' : '' }}
                                {{ $announcement->type === 'urgent' ? 'bg-red-900 text-red-300' : '' }}">
                                {{ ucfirst($announcement->type) }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $announcement->is_active ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm">
                        Edit
                    </a>
                </div>
            </div>

            <div class="prose prose-invert max-w-none">
                <p class="text-gray-300 whitespace-pre-wrap">{{ $announcement->content }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Details</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-400">Target Audience</p>
                    <p class="text-white font-medium">{{ ucfirst($announcement->target_audience) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Created By</p>
                    <p class="text-white font-medium">{{ $announcement->creator->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Created</p>
                    <p class="text-white font-medium">{{ $announcement->created_at->format('M d, Y g:i A') }}</p>
                </div>
                @if($announcement->expires_at)
                    <div>
                        <p class="text-sm text-gray-400">Expires</p>
                        <p class="text-white font-medium">
                            {{ $announcement->expires_at->format('M d, Y g:i A') }}
                            @if($announcement->isExpired())
                                <span class="text-red-400 text-xs">(Expired)</span>
                            @endif
                        </p>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-gray-400">Times Read</p>
                    <p class="text-white font-medium">{{ $readCount }}</p>
                </div>
            </div>
        </div>

        @if($announcement->reads->count() > 0)
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h3 class="text-lg font-bold text-white mb-4">Read By ({{ $readCount }})</h3>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($announcement->reads as $read)
                        <div class="flex items-center justify-between p-2 bg-gray-900 rounded">
                            <div>
                                <p class="text-white text-sm">{{ $read->user->name }}</p>
                                <p class="text-gray-400 text-xs">{{ $read->user->email }}</p>
                            </div>
                            <p class="text-gray-400 text-xs">{{ $read->read_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

