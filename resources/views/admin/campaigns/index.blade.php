@extends('admin.layout')

@section('title', 'Campaigns')
@section('header', 'Campaigns')

@section('content')
<div class="space-y-6">
    <div class="flex justify-end">
        <a href="{{ route('admin.campaigns.create') }}" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Create Campaign</a>
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs text-gray-400 uppercase">Recipients</th>
                    <th class="px-6 py-3 text-right text-xs text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($campaigns as $campaign)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-6 py-4 text-white">{{ $campaign->name }}</td>
                        <td class="px-6 py-4 text-gray-300 uppercase">{{ $campaign->status }}</td>
                        <td class="px-6 py-4 text-gray-300">{{ $campaign->recipients_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-400 hover:text-blue-300 mr-3">View</a>
                            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-amber-400 hover:text-amber-300">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No campaigns yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-700">{{ $campaigns->links() }}</div>
    </div>
</div>
@endsection


