@extends('layouts.shop')

@section('title', 'Notifications')

@section('content')
<x-hero-section
    pageIdentifier="notifications"
    fallbackTitle="Notifications"
    fallbackSubtitle="Stay on top of booking updates and marketplace alerts"
    fallbackImage="/images/hero-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
            <form action="{{ route('account.notifications.preferences') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @foreach([
                        'in_app_enabled' => 'In-app notifications',
                        'push_enabled' => 'Push notifications (FCM)',
                        'email_enabled' => 'Email notifications',
                        'sms_enabled' => 'SMS notifications',
                        'booking_enabled' => 'Booking updates',
                        'order_enabled' => 'Order updates',
                        'marketing_enabled' => 'Marketing updates',
                    ] as $key => $label)
                        <label class="flex items-center gap-2 text-gray-700">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ $preferences[$key] ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="mt-3 px-4 py-2 rounded-full bg-orange-600 text-white text-sm font-semibold hover:bg-orange-500">Save Preferences</button>
            </form>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Push Device Token</h3>
            <p class="text-xs text-gray-500 mb-3">Paste your FCM device token to receive push notifications.</p>
            <form action="{{ route('account.notifications.push-token') }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="fcm_token" rows="4" placeholder="FCM token..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-orange-500 focus:outline-none">{{ old('fcm_token', auth()->user()->fcm_token) }}</textarea>
                <button type="submit" class="px-4 py-2 rounded-full border border-gray-300 text-sm font-semibold text-gray-700 hover:border-gray-400">Save Push Token</button>
            </form>
        </div>
    </div>

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Inbox</h2>
            <p class="text-sm text-gray-500">Unread: {{ $unreadCount }}</p>
        </div>
        <div class="flex flex-wrap gap-3 items-center">
            <form method="GET" class="flex items-center gap-2 bg-white rounded-full border border-gray-200 px-4 py-2 shadow-sm">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Search notifications"
                       class="text-sm text-gray-700 placeholder-gray-400 bg-transparent outline-none">
                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-orange-600">Filter</button>
            </form>

            <form method="GET" class="flex items-center gap-2">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Show</label>
                <select name="filter" onchange="this.form.submit()" class="bg-gray-900 text-white rounded-full px-3 py-1 text-sm">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="unread" {{ $filter === 'unread' ? 'selected' : '' }}>Unread</option>
                </select>
                <input type="hidden" name="q" value="{{ $searchTerm }}">
            </form>

            <form action="{{ route('account.notifications.markAll') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-semibold text-gray-700 hover:text-gray-900">Mark all read</button>
            </form>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($notifications as $notification)
            <div class="rounded-2xl border bg-white p-5 shadow-sm transition hover:shadow-lg {{ $notification->is_read ? 'border-gray-100' : 'border-orange-200 bg-orange-50/30' }}">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-lg font-semibold text-gray-900">{{ $notification->title }}</p>
                        <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                        @if($notification->message)
                            <p class="mt-3 text-gray-600">{{ $notification->message }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-start gap-2 text-sm text-gray-600 md:items-end">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="px-4 py-2 rounded-full border border-gray-200 text-gray-700 hover:border-gray-300 hover:text-gray-900">
                                View details
                            </a>
                        @endif
                        @if(! $notification->is_read)
                            <form action="{{ route('account.notifications.markRead', $notification) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-orange-600 hover:text-orange-500">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center rounded-2xl border border-dashed border-gray-300 bg-white/80 p-12">
                <p class="text-lg font-semibold text-gray-900">You are all caught up</p>
                <p class="text-sm text-gray-500 mt-2">We will notify you when there is something new.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $notifications->links() }}
    </div>
</div>
@endsection

