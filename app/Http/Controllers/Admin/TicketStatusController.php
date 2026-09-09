<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketStatusController extends Controller
{
    /**
     * Display a listing of the ticket statuses.
     */
    public function index()
    {
        $statuses = TicketStatus::withCount('tickets')->latest()->paginate(10);
        return view('admin.ticket-statuses.index', compact('statuses'));
    }

    /**
     * Show the form for creating a new ticket status.
     */
    public function create()
    {
        return view('admin.ticket-statuses.create');
    }

    /**
     * Store a newly created ticket status in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_statuses,name',
            'color_code' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Check if slug is unique
        if (TicketStatus::where('slug', $validated['slug'])->exists()) {
            return back()->withErrors(['name' => 'A status with a similar slug already exists.'])->withInput();
        }

        TicketStatus::create($validated);

        return redirect()->route('admin.ticket-statuses.index')
            ->with('success', "Ticket Status '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified ticket status.
     */
    public function edit(TicketStatus $ticketStatus)
    {
        return view('admin.ticket-statuses.edit', compact('ticketStatus'));
    }

    /**
     * Update the specified ticket status in storage.
     */
    public function update(Request $request, TicketStatus $ticketStatus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_statuses,name,' . $ticketStatus->id,
            'color_code' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Check if slug is unique (excluding current status)
        if (TicketStatus::where('slug', $validated['slug'])->where('id', '!=', $ticketStatus->id)->exists()) {
            return back()->withErrors(['name' => 'A status with a similar slug already exists.'])->withInput();
        }

        $ticketStatus->update($validated);

        return redirect()->route('admin.ticket-statuses.index')
            ->with('success', "Ticket Status '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified ticket status from storage.
     */
    public function destroy(TicketStatus $ticketStatus)
    {
        if ($ticketStatus->tickets()->exists()) {
            return redirect()->route('admin.ticket-statuses.index')
                ->with('error', "Cannot delete Ticket Status '{$ticketStatus->name}' because it has active tickets associated with it.");
        }

        // Prevent deleting core system statuses that might break system assumptions
        $coreStatuses = ['open', 'accepted', 'review', 'closed', 'canceled'];
        if (in_array($ticketStatus->slug, $coreStatuses)) {
            return redirect()->route('admin.ticket-statuses.index')
                ->with('error', "Cannot delete core system status '{$ticketStatus->name}'.");
        }

        $statusName = $ticketStatus->name;
        $ticketStatus->delete();

        return redirect()->route('admin.ticket-statuses.index')
            ->with('success', "Ticket Status '{$statusName}' deleted successfully.");
    }
}
