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
        if ($ticket->assigned_to && $ticket->assigned_to !== $actorId) {
            $usersToNotify->push($ticket->assignee);
        }

        // 3. Subscribed Users
        if (method_exists($ticket, 'subscriptions')) {
            $subscribers = $ticket->subscriptions()->with('user')->get()->pluck('user')->filter(function ($user) use ($actorId) {
                return $user && $user->id !== $actorId;
            });
            $usersToNotify = $usersToNotify->merge($subscribers);
        }

        // 4. Intended User (If ticket is in open status)
        if ($ticket->to_user_id && $ticket->to_user_id !== $actorId) {
            $status = $ticket->relationLoaded('status') ? $ticket->status : $ticket->status()->first();
            if ($status && $status->slug === 'open') {
                $usersToNotify->push($ticket->toUser);
            }
        }

        $usersToNotify = $usersToNotify->unique('id')->filter();

        // Dispatch notifications
        foreach ($usersToNotify as $user) {
            $user->notifyNow(new TicketUpdatedNotification($ticket, $message));
        }
    }
}
