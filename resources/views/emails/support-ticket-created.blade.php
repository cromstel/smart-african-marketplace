@php
    $sessionKey = $ticket->chatbotSession?->session_key;
    $userName = $ticket->user?->name ?? 'Guest';
@endphp

<p>A new support ticket has been created.</p>
<p><strong>Ticket ID:</strong> #{{ $ticket->id }}</p>
<p><strong>User:</strong> {{ $userName }}</p>
@if($sessionKey)
    <p><strong>Chatbot Session:</strong> {{ $sessionKey }}</p>
@endif
<p><strong>Status:</strong> {{ ucfirst($ticket->status) }}</p>
<p>View in admin: {{ route('admin.support-tickets.show', $ticket) }}</p>

