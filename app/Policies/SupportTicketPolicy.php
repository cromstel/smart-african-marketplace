<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    /**
     * Determine whether the user can view any support tickets.
     * Admin and support agents can view all tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('support_agent');
    }

    /**
     * Determine whether the user can view the support ticket.
     */
    public function view(User $user, SupportTicket $supportTicket): bool
    {
        // Admins can view any ticket
        if ($user->isAdmin()) {
            return true;
        }

        // Support agents can view tickets assigned to them or unassigned
        if ($user->hasRole('support_agent')) {
            return $supportTicket->assigned_agent_id === null
                || $supportTicket->assigned_agent_id === $user->id;
        }

        // Users can only view their own tickets
        return $supportTicket->user_id === $user->id;
    }

    /**
     * Determine whether the user can reply to the support ticket.
     */
    public function reply(User $user, SupportTicket $supportTicket): bool
    {
        // Admins can reply to any ticket
        if ($user->isAdmin()) {
            return true;
        }

        // Support agents can reply to tickets assigned to them or unassigned
        if ($user->hasRole('support_agent')) {
            return $supportTicket->assigned_agent_id === null
                || $supportTicket->assigned_agent_id === $user->id;
        }

        // Users can only reply to their own open tickets
        return $supportTicket->user_id === $user->id
            && $supportTicket->status !== 'closed';
    }

    /**
     * Determine whether the user can update the support ticket status.
     */
    public function updateStatus(User $user, SupportTicket $supportTicket): bool
    {
        // Admins can update any ticket status
        if ($user->isAdmin()) {
            return true;
        }

        // Support agents can update status of tickets assigned to them
        if ($user->hasRole('support_agent')) {
            return $supportTicket->assigned_agent_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can assign the support ticket.
     */
    public function assign(User $user, SupportTicket $supportTicket): bool
    {
        // Admins can assign any ticket
        if ($user->isAdmin()) {
            return true;
        }

        // Support agents can assign tickets to themselves
        if ($user->hasRole('support_agent')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the support ticket.
     * Only admins can delete tickets.
     */
    public function delete(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isAdmin();
    }
}
