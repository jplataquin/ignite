<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use Illuminate\Http\Request;

class TicketPriorityController extends Controller
{
    /**
     * Display a listing of the ticket priorities.
     */
    public function index()
    {
        $priorities = TicketPriority::withCount('tickets')->orderBy('level')->paginate(10);
        return view('admin.ticket-priorities.index', compact('priorities'));
    }

    /**
     * Show the form for creating a new ticket priority.
     */
    public function create()
    {
        return view('admin.ticket-priorities.create');
    }

    /**
     * Store a newly created ticket priority in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_priorities,name',
            'level' => 'required|integer|min:1|unique:ticket_priorities,level',
        ]);

        TicketPriority::create($validated);

        return redirect()->route('admin.ticket-priorities.index')
            ->with('success', "Ticket Priority '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified ticket priority.
     */
    public function edit(TicketPriority $ticketPriority)
    {
        return view('admin.ticket-priorities.edit', compact('ticketPriority'));
    }

    /**
     * Update the specified ticket priority in storage.
     */
    public function update(Request $request, TicketPriority $ticketPriority)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_priorities,name,' . $ticketPriority->id,
            'level' => 'required|integer|min:1|unique:ticket_priorities,level,' . $ticketPriority->id,
        ]);

        $ticketPriority->update($validated);

        return redirect()->route('admin.ticket-priorities.index')
            ->with('success', "Ticket Priority '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified ticket priority from storage.
     */
    public function destroy(TicketPriority $ticketPriority)
    {
        if ($ticketPriority->tickets()->exists()) {
            return redirect()->route('admin.ticket-priorities.index')
                ->with('error', "Cannot delete Ticket Priority '{$ticketPriority->name}' because it has active tickets associated with it.");
        }

        $priorityName = $ticketPriority->name;
        $ticketPriority->delete();

        return redirect()->route('admin.ticket-priorities.index')
            ->with('success', "Ticket Priority '{$priorityName}' deleted successfully.");
    }
}
