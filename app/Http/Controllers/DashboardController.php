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
        $openTicketsCount = Ticket::whereHas('status', function ($query) {
            $query->whereIn('slug', ['open', 'accepted', 'review']);
        })->count();

        $unassignedTicketsCount = Ticket::whereNull('assigned_to')->count();

        $criticalTicketsCount = Ticket::whereHas('priority', function ($query) {
            $query->where('level', '>=', 2)
                  ->orWhereIn('name', ['Major', 'major']);
        })->count();

        $slaLapsedCount = Ticket::whereHas('status', function ($query) {
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
