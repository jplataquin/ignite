<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketUpdatedNotification;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        $this->dispatchNotifications($ticket, "Ticket {$ticket->ticket_number} was created.");
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        $changes = $ticket->getChanges();
        unset($changes['updated_at']);
        
        if (count($changes) > 0) {
            $this->dispatchNotifications($ticket, "Ticket {$ticket->ticket_number} was updated.");
        }
    }

    /**
     * Centralized notification dispatcher respecting exclusion rules.
     */
    protected function dispatchNotifications(Ticket $ticket, string $message): void
    {
        $actorId = Auth::id(); // Get current user (null if system/CLI)

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
        $subscribers = $ticket->subscriptions()->with('user')->get()->pluck('user')->filter(function ($user) use ($actorId) {
            return $user->id !== $actorId;
        });
        
        $usersToNotify = $usersToNotify->merge($subscribers)->unique('id')->filter();

        // Dispatch
        foreach ($usersToNotify as $user) {
            $user->notify(new TicketUpdatedNotification($ticket, $message));
        }
    }
}
