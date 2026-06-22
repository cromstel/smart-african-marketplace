<?php

namespace App\Http\Controllers\Admin;

use App\Events\ChatbotMessageCreated;
use App\Http\Controllers\Controller;
use App\Models\ChatbotMessage;
use App\Models\ChatbotSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotSessionsController extends Controller
{
    public function index(): View
    {
        $sessions = ChatbotSession::query()
            ->with(['user', 'assignedAgent'])
            ->orderByDesc('last_active_at')
            ->paginate(30);

        return view('admin.chatbot.sessions.index', compact('sessions'));
    }

    public function show(ChatbotSession $session): View
    {
        $session->load(['user', 'assignedAgent', 'messages' => function ($query) {
            $query->orderBy('id');
        }]);

        return view('admin.chatbot.sessions.show', compact('session'));
    }

    public function claim(ChatbotSession $session, Request $request): RedirectResponse
    {
        $session->update([
            'handoff_active' => true,
            'assigned_agent_id' => $request->user()->id,
        ]);

        $message = ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'system',
            'content' => 'A human agent has joined the chat.',
        ]);
        event(new ChatbotMessageCreated($message));

        return redirect()
            ->route('admin.chatbot.sessions.show', $session)
            ->with('success', 'You are now assigned to this chat.');
    }

    public function store(ChatbotSession $session, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        if (! $session->handoff_active) {
            $session->update([
                'handoff_active' => true,
                'assigned_agent_id' => $request->user()->id,
            ]);
        } elseif (! $session->assigned_agent_id) {
            $session->update([
                'assigned_agent_id' => $request->user()->id,
            ]);
        }

        $message = ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'role' => 'agent',
            'content' => $validated['message'],
        ]);
        event(new ChatbotMessageCreated($message));

        return redirect()
            ->route('admin.chatbot.sessions.show', $session)
            ->with('success', 'Message sent.');
    }
}
