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

        $openTicketsCount = (clone $baseQuery)->where('status', 'Valid')->count();

        $unassignedTicketsCount = (clone $baseQuery)->where('status', 'Valid')->whereNull('assigned_to')->count();

        $criticalTicketsCount = (clone $baseQuery)->where('status', 'Valid')
            ->whereHas('priorityOption', function ($query) {
                $query->where('level', '>=', 3)
                      ->orWhereIn('name', ['Critical', 'critical']);
            })->count();

        $slaLapsedCount = (clone $baseQuery)->where('status', 'Lapsed')->count();

        return view('dashboard', compact(
            'openTicketsCount',
            'unassignedTicketsCount',
            'criticalTicketsCount',
            'slaLapsedCount'
        ));
    }
}
