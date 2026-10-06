<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStage;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with actual counts.
     */
    public function index()
    {
        $user = auth()->user();
        $baseQuery = Ticket::query();

        // Non-admin users are restricted to their division and department (if assigned)
        if ($user && $user->user_type !== 'admin') {
            if ($user->department_id) {
                $baseQuery->where('department_id', $user->department_id);
            } elseif ($user->division_id) {
                $baseQuery->where('division_id', $user->division_id);
            } else {
                $baseQuery->whereRaw('1 = 0');
            }
        }

        $openStage = TicketStage::where('slug', 'open')->first();
        $openStageId = $openStage ? $openStage->id : null;

        $openTicketsCount = $openStageId
            ? (clone $baseQuery)->where('stage_id', $openStageId)->count()
            : 0;

        $assignedTicketsCount = $user ? Ticket::where('assigned_id', $user->id)->count() : 0;

        $criticalTicketsCount = (clone $baseQuery)->where('status', 'Valid')
            ->whereHas('priorityOption', function ($query) {
                $query->where('level', '>=', 3)
                    ->orWhereIn('name', ['Critical', 'critical']);
            })->count();

        $slaLapsedCount = (clone $baseQuery)->where('status', 'Lapsed')
            ->whereDoesntHave('stage', function ($query) {
                $query->where('slug', 'canceled');
            })->count();

        return view('dashboard', compact(
            'openTicketsCount',
            'openStageId',
            'assignedTicketsCount',
            'criticalTicketsCount',
            'slaLapsedCount'
        ));
    }
}
