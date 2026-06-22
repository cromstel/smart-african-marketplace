<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of all support tickets.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $status = $request->get('status');
        $query = SupportTicket::query()
            ->with(['user', 'assignedAgent', 'chatbotSession'])
            ->orderByDesc('last_message_at');

        if ($status) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(30)->appends($request->query());

        $counts = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'pending' => SupportTicket::where('status', 'pending')->count(),
            'closed' => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support-tickets.index', compact('tickets', 'counts', 'status'));
    }

    /**
     * Display the specified support ticket.
     */
    public function show(SupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load(['user', 'assignedAgent', 'chatbotSession', 'messages' => function ($query) {
            $query->orderBy('id');
        }]);

        return view('admin.support-tickets.show', compact('supportTicket'));
    }

    /**
     * Store a reply to the support ticket.
     */
    public function store(SupportTicket $supportTicket, Request $request): RedirectResponse
    {
        $this->authorize('reply', $supportTicket);

        $validated = $request->validate([
            'message' => 'required|string|max:4000',
        ]);

        if (! $supportTicket->assigned_agent_id) {
            $supportTicket->assigned_agent_id = $request->user()->id;
        }

        if ($supportTicket->status === 'closed') {
            $supportTicket->status = 'open';
            $supportTicket->closed_at = null;
        }

        $supportTicket->last_message_at = now();
        $supportTicket->save();

        SupportMessage::create([
            'support_ticket_id' => $supportTicket->id,
            'author_role' => 'agent',
            'author_id' => $request->user()->id,
            'content' => $validated['message'],
        ]);

        return redirect()
            ->route('admin.support-tickets.show', $supportTicket)
            ->with('success', 'Reply sent.');
    }

    /**
     * Update the status of the support ticket.
     */
    public function updateStatus(SupportTicket $supportTicket, Request $request): RedirectResponse
    {
        $this->authorize('updateStatus', $supportTicket);

        $validated = $request->validate([
            'status' => 'required|in:open,pending,closed',
        ]);

        $supportTicket->status = $validated['status'];
        $supportTicket->closed_at = $validated['status'] === 'closed' ? now() : null;
        if (! $supportTicket->assigned_agent_id) {
            $supportTicket->assigned_agent_id = $request->user()->id;
        }
        $supportTicket->save();

        return redirect()
            ->route('admin.support-tickets.show', $supportTicket)
            ->with('success', 'Ticket status updated.');
    }
}
