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
     * Handle the Ticket "updating" event.
     */
    public function updating(Ticket $ticket): void
    {
        $dirty = $ticket->getDirty();
        unset($dirty['updated_at']);

        if (empty($dirty)) {
            return;
        }

        $changeLog = [];

        foreach ($dirty as $field => $newValue) {
            $oldValue = $ticket->getOriginal($field);

            switch ($field) {
                case 'title':
                    $changeLog[] = "Title updated from '{$oldValue}' to '{$newValue}'";
                    break;
                case 'description':
                    $changeLog[] = "Description updated";
                    break;
                case 'ticket_type_id':
                    $oldName = \App\Models\TicketType::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\TicketType::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Ticket Type updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'priority_option_id':
                    $oldName = \App\Models\Priority::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\Priority::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Priority updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'status_id':
                    $oldName = \App\Models\TicketStatus::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\TicketStatus::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Status updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'division_id':
                    $oldName = \App\Models\Division::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\Division::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Division updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'department_id':
                    $oldName = \App\Models\Department::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\Department::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Department updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'category_1_id':
                case 'category_2_id':
                case 'category_3_id':
                    $num = substr($field, -4, 1);
                    $oldName = \App\Models\Category::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\Category::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Category {$num} updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'to_user_id':
                    $oldName = \App\Models\User::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\User::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Intended User updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'assigned_to':
                    $oldName = \App\Models\User::find($oldValue)?->name ?? 'None';
                    $newName = \App\Models\User::find($newValue)?->name ?? 'None';
                    $changeLog[] = "Assignee updated from '{$oldName}' to '{$newName}'";
                    break;
                case 'deadline_date':
                    $oldDate = $oldValue ? \Carbon\Carbon::parse($oldValue)->format('M d, Y H:i') : 'None';
                    $newDate = $newValue ? \Carbon\Carbon::parse($newValue)->format('M d, Y H:i') : 'None';
                    $changeLog[] = "Deadline SLA updated from '{$oldDate}' to '{$newDate}'";
                    break;
            }
        }

        if (!empty($changeLog)) {
            $ticket->temp_system_comment = "Ticket details updated:\n" . implode("\n", array_map(fn($log) => "- " . $log, $changeLog));
        }
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

        if (!empty($ticket->temp_system_comment)) {
            \App\Models\TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => null, // Logged by system
                'type' => 'system_event',
                'content' => $ticket->temp_system_comment,
            ]);
            unset($ticket->temp_system_comment);
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
        
        $usersToNotify = $usersToNotify->merge($subscribers);

        // 4. Intended User (If ticket is in open status)
        if ($ticket->to_user_id && $ticket->to_user_id !== $actorId) {
            // Load status if not loaded, then verify slug is 'open'
            $status = $ticket->relationLoaded('status') ? $ticket->status : $ticket->status()->first();
            if ($status && $status->slug === 'open') {
                $usersToNotify->push($ticket->toUser);
            }
        }
        
        $usersToNotify = $usersToNotify->unique('id')->filter();

        // Dispatch
        foreach ($usersToNotify as $user) {
            $user->notifyNow(new TicketUpdatedNotification($ticket, $message));
        }
    }
}
