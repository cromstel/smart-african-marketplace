@extends('vendor.layout')

@section('title', 'Announcements')
@section('header', 'Announcements')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-gray-900">Announcements</h3>
        <p class="text-gray-600 mt-1">Stay updated with important messages from the admin</p>
    </div>
    @if($unreadCount > 0)
        <form action="{{ route('vendor.announcements.markAllAsRead') }}" method="POST">
            @csrf
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mark All as Read ({{ $unreadCount }})
            </button>
        </form>
    @endif
</div>

<div class="space-y-4">
    @forelse($announcements as $announcement)
        @php
            $isRead = $announcement->isReadBy(auth()->id());
            $typeStyles = [
                'info' => 'border-blue-200 bg-blue-50',
                'warning' => 'border-amber-200 bg-amber-50',
                'success' => 'border-green-200 bg-green-50',
                'danger' => 'border-red-200 bg-red-50',
            ];
            $typeIcons = [
                'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                'danger' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ];
            $typeTextColors = [
                'info' => 'text-blue-600',
                'warning' => 'text-amber-600',
                'success' => 'text-green-600',
                'danger' => 'text-red-600',
            ];
        @endphp

        <div class="bg-white rounded-lg border-2 {{ $typeStyles[$announcement->type] }} {{ $isRead ? 'opacity-75' : '' }} overflow-hidden transition-opacity hover:opacity-100">
            <a href="{{ route('vendor.announcements.show', $announcement) }}" class="block p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1">
                        <svg class="w-6 h-6 {{ $typeTextColors[$announcement->type] }} mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $typeIcons[$announcement->type] !!}
                        </svg>

                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="text-lg font-bold text-gray-900">{{ $announcement->title }}</h4>
                                
                                @if($announcement->is_pinned)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                        </svg>
                                        Pinned
                                    </span>
                                @endif

                                @if(!$isRead)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                        New
                                    </span>
                                @endif
                            </div>

                            <p class="text-gray-700 mb-3 line-clamp-2">{{ Str::limit($announcement->content, 200) }}</p>

                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $announcement->published_at ? $announcement->published_at->format('M j, Y g:i A') : $announcement->created_at->format('M j, Y g:i A') }}
                                
                                @if($announcement->expires_at)
                                    <span class="mx-2">•</span>
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Expires {{ $announcement->expires_at->format('M j, Y') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <svg class="w-5 h-5 text-gray-400 ml-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>
    @empty
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Announcements</h3>
            <p class="text-gray-600">You don't have any announcements at the moment.</p>
        </div>
    @endforelse
</div>

@if($announcements->hasPages())
    <div class="mt-8">
        {{ $announcements->links() }}
    </div>
@endif
@endsection

