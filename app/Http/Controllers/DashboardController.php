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

        $myTicketsCount = Ticket::where(function ($q) use ($user) {
            $q->orWhere('assigned_id', $user->id);
            $q->orWhere(function ($sub) use ($user) {
                $sub->where('created_by', $user->id)
                    ->whereHas('stage', function ($sq) {
                        $sq->where('slug', 'review');
                    });
            });
        })->count();

        $criticalTicketsCount = (clone $baseQuery)->where('status', 'Valid')
            ->whereHas('priorityOption', function ($query) {
                $query->where('level', '>=', 3)
                      ->orWhereIn('name', ['Critical', 'critical']);
            })->count();

        $slaLapsedCount = (clone $baseQuery)->where('status', 'Lapsed')->count();

        return view('dashboard', compact(
            'openTicketsCount',
            'myTicketsCount',
            'criticalTicketsCount',
            'slaLapsedCount'
        ));
    }
}
