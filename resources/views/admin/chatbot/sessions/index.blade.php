@extends('admin.layout')

@section('title', 'Chatbot Sessions')
@section('header', 'Chatbot Sessions')

@section('content')
<div class="bg-gray-800 border border-gray-700 rounded-lg">
    <div class="p-4 border-b border-gray-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-white">Active Sessions</h3>
        <span class="text-sm text-gray-400">{{ $sessions->total() }} total</span>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-900 text-gray-300">
                <tr>
                    <th class="text-left px-4 py-3">Session</th>
                    <th class="text-left px-4 py-3">User</th>
                    <th class="text-left px-4 py-3">Last Active</th>
                    <th class="text-left px-4 py-3">Handoff</th>
                    <th class="text-left px-4 py-3">Agent</th>
                    <th class="text-right px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($sessions as $session)
                    <tr class="text-gray-200">
                        <td class="px-4 py-3 font-mono text-xs">{{ $session->session_key }}</td>
                        <td class="px-4 py-3">
                            {{ $session->user?->name ?? 'Guest' }}
                        </td>
                        <td class="px-4 py-3 text-gray-400">
                            {{ $session->last_active_at?->diffForHumans() ?? 'n/a' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $session->handoff_active ? 'bg-emerald-900 text-emerald-200' : 'bg-gray-700 text-gray-300' }}">
                                {{ $session->handoff_active ? 'Active' : 'Off' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400">
                            {{ $session->assignedAgent?->name ?? 'Unassigned' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.chatbot.sessions.show', $session) }}" class="text-orange-400 hover:text-orange-300">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">No chatbot sessions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700">
        {{ $sessions->links() }}
    </div>
</div>
@endsection

