@extends('vendor.layout')

@section('title', 'Followers')
@section('header', 'Followers')

@section('content')
<div class="space-y-6">
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Followers</p>
                <p class="text-2xl font-bold text-white">{{ $followers->total() }}</p>
            </div>
            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-700">
            @forelse($followers as $follower)
                <div class="p-6 flex items-center justify-between hover:bg-gray-750 transition">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 font-semibold">
                            {{ strtoupper(substr($follower->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ $follower->user->name ?? 'Deleted user' }}</p>
                            @if($follower->user)
                                <p class="text-sm text-gray-400">{{ $follower->user->email }}</p>
                            @endif
                        </div>
                    </div>
                    <span class="text-sm text-gray-400">Following since {{ $follower->created_at->format('M j, Y') }}</span>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="mt-4">No followers yet.</p>
                    <p class="text-sm text-gray-400 mt-1">Customers who follow your store will show up here.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($followers->hasPages())
        <div class="mt-4">
            {{ $followers->links() }}
        </div>
    @endif
</div>
@endsection
