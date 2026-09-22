<?php

namespace App\Observers;

use App\Models\TicketComment;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Support\Facades\Auth;

class TicketCommentObserver
{
    /**
     * Handle the TicketComment "created" event.
     */
    public function created(TicketComment $comment): void
    {
        // Don't notify if it's a system event
        if ($comment->type === 'system_event') {
            return;
        }

        $ticket = $comment->ticket;
        if (!$ticket) {
            return;
        }

        $actorId = Auth::id(); // Get current user (null if system/CLI)
        $commenterName = $comment->user ? $comment->user->name : 'System';
        $message = "New comment on Ticket {$ticket->ticket_number} by {$commenterName}.";

        $usersToNotify = collect();

        // 1. Ticket Creator
        if ($ticket->created_by && $ticket->created_by !== $actorId) {
            $usersToNotify->push($ticket->creator);
        }

        // 2. Assigned User
        if ($ticket->assigned_id && $ticket->assigned_id !== $actorId) {
            $usersToNotify->push($ticket->assignee);
        }

        // 3. Subscribed Users
        if (method_exists($ticket, 'subscriptions')) {
            $subscribers = $ticket->subscriptions()->with('user')->get()->pluck('user')->filter(function ($user) use ($actorId) {
                return $user && $user->id !== $actorId;
            });
            $usersToNotify = $usersToNotify->merge($subscribers);
        }

        $usersToNotify = $usersToNotify->unique('id')->filter();

        // Dispatch notifications
        foreach ($usersToNotify as $user) {
            $user->notifyNow(new TicketUpdatedNotification($ticket, $message));
        }
    }
}
