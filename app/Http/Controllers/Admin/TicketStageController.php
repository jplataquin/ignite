<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketStage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketStageController extends Controller
{
    /**
     * Display a listing of the ticket stages.
     */
    public function index()
    {
        $stages = TicketStage::withCount(['tickets'])->orderBy('name')->paginate(10);
        return view('admin.ticket-stages.index', compact('stages'));
    }

    /**
     * Show the form for creating a new ticket stage.
     */
    public function create()
    {
        return view('admin.ticket-stages.create');
    }

    /**
     * Store a newly created ticket stage in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_stages,name',
            'color_code' => 'required|string|max:7',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        TicketStage::create($validated);

        return redirect()->route('admin.ticket-stages.index')
            ->with('success', "Ticket Stage '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified ticket stage.
     */
    public function edit(TicketStage $ticketStage)
    {
        return view('admin.ticket-stages.edit', compact('ticketStage'));
    }

    /**
     * Update the specified ticket stage in storage.
     */
    public function update(Request $request, TicketStage $ticketStage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_stages,name,' . $ticketStage->id,
            'color_code' => 'required|string|max:7',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $ticketStage->update($validated);

        return redirect()->route('admin.ticket-stages.index')
            ->with('success', "Ticket Stage '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified ticket stage from storage.
     */
    public function destroy(TicketStage $ticketStage)
    {
        // Core stages cannot be deleted
        $coreSlugs = ['open', 'assigned', 'review', 'closed', 'canceled', 'lapsed'];
        if (in_array($ticketStage->slug, $coreSlugs)) {
            return redirect()->route('admin.ticket-stages.index')
                ->with('error', "Cannot delete Core Ticket Stage '{$ticketStage->name}'.");
        }

        if ($ticketStage->tickets()->exists()) {
            return redirect()->route('admin.ticket-stages.index')
                ->with('error', "Cannot delete Ticket Stage '{$ticketStage->name}' because it has active tickets associated with it.");
        }

        $stageName = $ticketStage->name;
        $ticketStage->delete();

        return redirect()->route('admin.ticket-stages.index')
            ->with('success', "Ticket Stage '{$stageName}' deleted successfully.");
    }
}
