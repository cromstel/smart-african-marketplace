@extends('vendor.layout')

@section('title', $announcement->title)
@section('header', 'Announcement')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('vendor.announcements.index') }}" class="text-orange-600 hover:text-orange-700 inline-flex items-center font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Announcements
        </a>
    </div>

    @php
        $typeStyles = [
            'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-900', 'badge' => 'bg-blue-100 text-blue-800'],
            'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-900', 'badge' => 'bg-amber-100 text-amber-800'],
            'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-900', 'badge' => 'bg-green-100 text-green-800'],
            'danger' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-900', 'badge' => 'bg-red-100 text-red-800'],
        ];
        $styles = $typeStyles[$announcement->type];
    @endphp

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-2 {{ $styles['border'] }}">
        <div class="{{ $styles['bg'] }} p-6 border-b {{ $styles['border'] }}">
            <div class="flex items-start justify-between mb-2">
                <h2 class="text-3xl font-bold {{ $styles['text'] }} flex-1">{{ $announcement->title }}</h2>
                
                @if($announcement->is_pinned)
                    <svg class="w-6 h-6 text-amber-500 flex-shrink-0 ml-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                    </svg>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm {{ $styles['text'] }}">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $styles['badge'] }}">
                    {{ ucfirst($announcement->type) }}
                </span>

                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Published {{ $announcement->published_at ? $announcement->published_at->format('F j, Y g:i A') : $announcement->created_at->format('F j, Y g:i A') }}
                </div>

                @if($announcement->expires_at)
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Expires {{ $announcement->expires_at->format('F j, Y') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="p-8">
            <div class="prose max-w-none">
                <p class="text-gray-700 text-lg leading-relaxed whitespace-pre-wrap">{{ $announcement->content }}</p>
            </div>
        </div>

        @if($announcement->target === 'specific_vendors')
            <div class="bg-gray-50 border-t border-gray-200 px-8 py-4">
                <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    This announcement was sent to specific vendors only
                </div>
            </div>
        @endif
    </div>

    @if($announcement->isReadBy(auth()->id()))
        <div class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center text-green-700">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium">You've read this announcement</span>
        </div>
    @endif
</div>
@endsection

