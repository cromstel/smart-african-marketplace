@extends('admin.layout')

@section('title', 'Support Tickets')
@section('header', 'Support Tickets')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.support-tickets.index') }}" class="px-3 py-2 rounded text-sm {{ $status ? 'bg-gray-800 text-gray-300' : 'bg-brand-gradient text-white' }}">All</a>
        <a href="{{ route('admin.support-tickets.index', ['status' => 'open']) }}" class="px-3 py-2 rounded text-sm {{ $status === 'open' ? 'bg-brand-gradient text-white' : 'bg-gray-800 text-gray-300' }}">Open ({{ $counts['open'] }})</a>
        <a href="{{ route('admin.support-tickets.index', ['status' => 'pending']) }}" class="px-3 py-2 rounded text-sm {{ $status === 'pending' ? 'bg-brand-gradient text-white' : 'bg-gray-800 text-gray-300' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('admin.support-tickets.index', ['status' => 'closed']) }}" class="px-3 py-2 rounded text-sm {{ $status === 'closed' ? 'bg-brand-gradient text-white' : 'bg-gray-800 text-gray-300' }}">Closed ({{ $counts['closed'] }})</a>
    </div>

    <div class="bg-gray-900 border border-gray-700 rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-800 text-gray-400">
                <tr>
                    <th class="px-4 py-3 text-left">Ticket</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Agent</th>
                    <th class="px-4 py-3 text-left">Last Activity</th>
                    <th class="px-4 py-3 text-left"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr class="border-t border-gray-800 hover:bg-gray-800/40">
                        <td class="px-4 py-3 text-gray-200">
                            #{{ $ticket->id }}
                            <div class="text-xs text-gray-400">{{ $ticket->subject ?? 'Chatbot Handoff' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $ticket->user?->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $ticket->status === 'open' ? 'bg-emerald-900 text-emerald-200' : ($ticket->status === 'pending' ? 'bg-yellow-900 text-yellow-200' : 'bg-gray-700 text-gray-300') }}">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-300">{{ $ticket->assignedAgent?->name ?? 'Unassigned' }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $ticket->last_message_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="text-orange-400 hover:text-orange-300">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">No support tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tickets->links() }}
</div>
@endsection

