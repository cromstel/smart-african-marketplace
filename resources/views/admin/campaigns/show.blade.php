@extends('admin.layout')

@section('title', 'Campaign Details')
@section('header', 'Campaign Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-white">{{ $campaign->name }}</h3>
            <p class="text-gray-400 mt-1">{{ $campaign->subject }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Edit</a>
            <form method="POST" action="{{ route('admin.campaigns.send', $campaign) }}">
                @csrf
                <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Send Campaign</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6"><p class="text-sm text-gray-400">Sent</p><p class="text-2xl text-white font-bold mt-1">{{ $stats['sent'] }}</p></div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6"><p class="text-sm text-gray-400">Opened</p><p class="text-2xl text-white font-bold mt-1">{{ $stats['opened'] }}</p></div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6"><p class="text-sm text-gray-400">Clicked</p><p class="text-2xl text-white font-bold mt-1">{{ $stats['clicked'] }}</p></div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6"><p class="text-sm text-gray-400">Converted</p><p class="text-2xl text-white font-bold mt-1">{{ $stats['converted'] }}</p></div>
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6"><p class="text-sm text-gray-400">Revenue</p><p class="text-2xl text-white font-bold mt-1">${{ number_format($stats['revenue'], 2) }}</p></div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-400">Type</p>
                <p class="text-white font-semibold uppercase mt-1">{{ $campaign->campaign_type ?? 'standard' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Audience Segment</p>
                <p class="text-white font-semibold uppercase mt-1">{{ $campaign->audience_segment ?? 'all' }}</p>
            </div>
            <div>
                <p class="text-gray-400">Flash Sale Ends</p>
                <p class="text-white font-semibold mt-1">{{ optional($campaign->flash_sale_ends_at)->format('M d, Y H:i') ?? '-' }}</p>
            </div>
        </div>
        @if($campaign->banner_image_url)
            <div class="mt-4">
                <p class="text-gray-400 text-sm mb-2">Promotional Banner</p>
                <img src="{{ $campaign->banner_image_url }}" alt="Campaign Banner" class="rounded-lg max-h-64 w-full object-cover">
            </div>
        @endif
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h4 class="text-lg font-semibold text-white mb-3">Content</h4>
        <div class="text-gray-300 whitespace-pre-wrap">{{ $campaign->content }}</div>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700"><h4 class="text-lg font-semibold text-white">Recipients</h4></div>
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Sent</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($campaign->recipients as $recipient)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4 text-gray-300">{{ $recipient->email }}</td>
                        <td class="px-6 py-4 text-gray-300 uppercase">{{ $recipient->status }}</td>
                        <td class="px-6 py-4 text-gray-400">{{ optional($recipient->sent_at)->format('M d, Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">No recipients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

