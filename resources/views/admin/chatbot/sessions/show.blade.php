@extends('admin.layout')

@section('title', 'Chatbot Session')
@section('header', 'Chatbot Session')

@section('content')
<div class="space-y-6">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
            <div class="text-sm text-gray-400">Session</div>
            <div class="font-mono text-xs text-gray-200">{{ $session->session_key }}</div>
            <div class="text-sm text-gray-400">User: {{ $session->user?->name ?? 'Guest' }}</div>
            <div class="text-sm text-gray-400">Last active: {{ $session->last_active_at?->diffForHumans() ?? 'n/a' }}</div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2 py-1 rounded text-xs {{ $session->handoff_active ? 'bg-emerald-900 text-emerald-200' : 'bg-gray-700 text-gray-300' }}">
                {{ $session->handoff_active ? 'Handoff Active' : 'Handoff Off' }}
            </span>
            <span class="text-sm text-gray-400">Agent: {{ $session->assignedAgent?->name ?? 'Unassigned' }}</span>
            <form method="POST" action="{{ route('admin.chatbot.sessions.claim', $session) }}">
                @csrf
                <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded text-xs">Claim Chat</button>
            </form>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 space-y-3 max-h-[60vh] overflow-y-auto">
        @forelse($session->messages as $message)
            <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[75%] px-3 py-2 rounded text-sm {{ $message->role === 'user' ? 'bg-gray-800 text-white' : ($message->role === 'agent' ? 'bg-emerald-900 text-emerald-100' : 'bg-white text-gray-800') }}">
                    <div class="text-[10px] uppercase tracking-wide mb-1 {{ $message->role === 'user' ? 'text-gray-400' : ($message->role === 'agent' ? 'text-emerald-300' : 'text-gray-600') }}">
                        {{ ucfirst($message->role) }}
                    </div>
                    <div class="whitespace-pre-line">{{ $message->content }}</div>
                </div>
            </div>
        @empty
            <div class="text-gray-400 text-sm">No messages yet.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('admin.chatbot.sessions.messages.store', $session) }}" class="bg-gray-800 border border-gray-700 rounded-lg p-4 space-y-3">
        @csrf
        <label class="block text-sm text-gray-300">Send Message as Agent</label>
        <textarea name="message" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded px-3 py-2" placeholder="Type your reply..."></textarea>
        @error('message')
            <p class="text-sm text-red-300">{{ $message }}</p>
        @enderror
        <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded text-sm">Send</button>
    </form>
</div>
@endsection

