<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    /**
     * Display a listing of the ticket types.
     */
    public function index()
    {
        $ticketTypes = TicketType::withCount('tickets')->latest()->paginate(10);
        return view('admin.ticket-types.index', compact('ticketTypes'));
    }

    /**
     * Show the form for creating a new ticket type.
     */
    public function create()
    {
        return view('admin.ticket-types.create');
    }

    /**
     * Store a newly created ticket type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name',
            'description' => 'nullable|string',
            'threshold_days' => 'nullable|integer|min:1',
        ]);

        TicketType::create($validated);

        return redirect()->route('admin.ticket-types.index')
            ->with('success', "Ticket Type '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified ticket type.
     */
    public function edit(TicketType $ticketType)
    {
        return view('admin.ticket-types.edit', compact('ticketType'));
    }

    /**
     * Update the specified ticket type in storage.
     */
    public function update(Request $request, TicketType $ticketType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name,' . $ticketType->id,
            'description' => 'nullable|string',
            'threshold_days' => 'nullable|integer|min:1',
        ]);

        $ticketType->update($validated);

        return redirect()->route('admin.ticket-types.index')
            ->with('success', "Ticket Type '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified ticket type from storage.
     */
    public function destroy(TicketType $ticketType)
    {
        if ($ticketType->tickets()->exists()) {
            return redirect()->route('admin.ticket-types.index')
                ->with('error', "Cannot delete Ticket Type '{$ticketType->name}' because it has active tickets associated with it.");
        }

        $typeName = $ticketType->name;
        $ticketType->delete();

        return redirect()->route('admin.ticket-types.index')
            ->with('success', "Ticket Type '{$typeName}' deleted successfully.");
    }
}
