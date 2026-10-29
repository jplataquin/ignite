<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketComment;
use App\Models\TicketStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketApprovalController extends Controller
{
    /**
     * Approve the ticket sign-off for the authenticated user.
     */
    public function approve(Request $request, Ticket $ticket, TicketApproval $approval): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // Verify this approval belongs to the ticket and to this user (or user is admin)
        if ($approval->ticket_id !== $ticket->id) {
            abort(404);
        }

        if ($approval->user_id !== $user->id && $user->user_type !== 'admin') {
            abort(403, 'You are not authorized to sign off on this approval.');
        }

        if ($approval->status !== 'pending') {
            return redirect()->back()->with('error', 'This approval has already been actioned.');
        }

        // If sequential mode, ensure all previous sequence approvals are approved
        if ($ticket->approval_mode === 'sequential') {
            $hasUnapprovedPrevious = TicketApproval::where('ticket_id', $ticket->id)
                ->where('sequence_order', '<', $approval->sequence_order)
                ->where('status', '!=', 'approved')
                ->exists();

            if ($hasUnapprovedPrevious) {
                return redirect()->back()->with('error', 'Previous approvers must sign off before your approval.');
            }
        }

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($ticket, $approval, $user, $validated) {
            $approval->update([
                'status' => 'approved',
                'comment' => $validated['comment'] ?? null,
                'actioned_at' => now(),
            ]);

            // Add system comment log
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'system_event',
                'content' => "Approval signed off by {$user->name}.".(! empty($validated['comment']) ? " Note: {$validated['comment']}" : ''),
            ]);

            // Check if all approvals for this ticket are now approved
            $remainingPending = TicketApproval::where('ticket_id', $ticket->id)
                ->where('status', '!=', 'approved')
                ->count();

            if ($remainingPending === 0) {
                $approvedStage = TicketStage::where('slug', 'approved')->first();
                if ($approvedStage) {
                    $ticket->update([
                        'stage_id' => $approvedStage->id,
                        'status' => 'Done',
                    ]);

                    TicketComment::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $user->id,
                        'type' => 'system_event',
                        'content' => 'All required approvals have been completed. Ticket marked as Approved.',
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Approval sign-off recorded successfully.');
    }

    /**
     * Reject the ticket sign-off for the authenticated user.
     */
    public function reject(Request $request, Ticket $ticket, TicketApproval $approval): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($approval->ticket_id !== $ticket->id) {
            abort(404);
        }

        if ($approval->user_id !== $user->id && $user->user_type !== 'admin') {
            abort(403, 'You are not authorized to reject this approval.');
        }

        if ($approval->status !== 'pending') {
            return redirect()->back()->with('error', 'This approval has already been actioned.');
        }

        // If sequential, verify previous sequence
        if ($ticket->approval_mode === 'sequential') {
            $hasUnapprovedPrevious = TicketApproval::where('ticket_id', $ticket->id)
                ->where('sequence_order', '<', $approval->sequence_order)
                ->where('status', '!=', 'approved')
                ->exists();

            if ($hasUnapprovedPrevious) {
                return redirect()->back()->with('error', 'Previous approvers must sign off before your turn.');
            }
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($ticket, $approval, $user, $validated) {
            $approval->update([
                'status' => 'rejected',
                'comment' => $validated['comment'],
                'actioned_at' => now(),
            ]);

            $rejectedStage = TicketStage::where('slug', 'rejected')->first();
            if ($rejectedStage) {
                $ticket->update([
                    'stage_id' => $rejectedStage->id,
                    'status' => 'Done',
                ]);
            }

            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'type' => 'system_event',
                'content' => "Approval rejected by {$user->name}. Reason: {$validated['comment']}",
            ]);
        });

        return redirect()->back()->with('success', 'Ticket approval rejected.');
    }
}
