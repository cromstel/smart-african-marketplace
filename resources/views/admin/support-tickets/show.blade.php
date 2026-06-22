@extends('admin.layout')

@section('title', 'Support Ticket')
@section('header', 'Support Ticket')

@section('content')
<div class="space-y-6">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
            <div class="text-sm text-gray-400">Ticket #{{ $supportTicket->id }}</div>
            <div class="text-sm text-gray-400">User: {{ $supportTicket->user?->name ?? 'Guest' }}</div>
            <div class="text-sm text-gray-500">Chatbot Session: {{ $supportTicket->chatbotSession?->session_key ?? 'n/a' }}</div>
            <div class="text-sm text-gray-500">Last active: {{ $supportTicket->last_message_at?->diffForHumans() ?? $supportTicket->created_at->diffForHumans() }}</div>
        </div>
        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <span class="px-2 py-1 rounded text-xs {{ $supportTicket->status === 'open' ? 'bg-emerald-900 text-emerald-200' : ($supportTicket->status === 'pending' ? 'bg-yellow-900 text-yellow-200' : 'bg-gray-700 text-gray-300') }}">
                    {{ ucfirst($supportTicket->status) }}
                </span>
                <span class="text-sm text-gray-400">Agent: {{ $supportTicket->assignedAgent?->name ?? 'Unassigned' }}</span>
            </div>
            <form method="POST" action="{{ route('admin.support-tickets.status.update', $supportTicket) }}" class="flex items-center gap-2">
                @csrf
                @method('PUT')
                <select name="status" class="bg-gray-900 border border-gray-700 text-gray-200 text-xs rounded px-2 py-1">
                    @foreach(['open', 'pending', 'closed'] as $option)
                        <option value="{{ $option }}" {{ $supportTicket->status === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-xs">Update</button>
            </form>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 space-y-3 max-h-[60vh] overflow-y-auto">
        @forelse($supportTicket->messages as $message)
            <div class="flex {{ $message->author_role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] px-3 py-2 rounded text-sm {{ $message->author_role === 'user' ? 'bg-gray-800 text-white' : 'bg-emerald-900 text-emerald-100' }}">
                    <div class="text-[10px] uppercase tracking-wide mb-1 {{ $message->author_role === 'user' ? 'text-gray-400' : 'text-gray-500' }}">
                        {{ ucfirst($message->author_role) }}
                    </div>
                    <div class="whitespace-pre-line">{{ $message->content }}</div>
                </div>
            </div>
        @empty
            <div class="text-gray-400 text-sm">No messages yet.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.support-tickets.messages.store', $supportTicket) }}" class="bg-gray-800 border border-gray-700 rounded-lg p-4 space-y-3">
        @csrf
        <label class="block text-sm text-gray-300">Send Reply</label>
        <textarea name="message" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" placeholder="Type your reply..."></textarea>
        @error('message')
            <p class="text-sm text-red-300">{{ $message }}</p>
        @enderror
        <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded text-sm">Send</button>
    </form>
</div>
@endsection

