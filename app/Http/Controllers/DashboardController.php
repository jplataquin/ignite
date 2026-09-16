<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

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
            $baseQuery->where('division_id', $user->division_id);
            if ($user->department_id) {
                $baseQuery->where('department_id', $user->department_id);
            }
        }

        $openTicketsCount = (clone $baseQuery)->whereHas('status', function ($query) {
            $query->whereIn('slug', ['open', 'assigned', 'review']);
        })->count();

        $unassignedTicketsCount = (clone $baseQuery)->whereNull('assigned_to')->count();

        $criticalTicketsCount = (clone $baseQuery)->whereHas('priorityOption', function ($query) {
            $query->where('level', '>=', 3)
                  ->orWhereIn('name', ['Critical', 'critical']);
        })->count();

        $slaLapsedCount = (clone $baseQuery)->whereHas('status', function ($query) {
            $query->whereNotIn('slug', ['closed', 'canceled']);
        })->where('deadline_date', '<', now())->count();

        return view('dashboard', compact(
            'openTicketsCount',
            'unassignedTicketsCount',
            'criticalTicketsCount',
            'slaLapsedCount'
        ));
    }
}
