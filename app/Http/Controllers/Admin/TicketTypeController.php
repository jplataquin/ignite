<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Division;
use App\Models\Location;
use App\Models\Priority;
use App\Models\TicketStage;
use App\Models\TicketType;
use App\Models\User;
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
        $priorities = Priority::orderBy('level')->get();
        $divisions = Division::with('departments')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $categories = collect();
        $users = User::orderBy('name')->get();
        $ticketStages = TicketStage::orderBy('name')->get();

        return view('admin.ticket-types.create', compact('priorities', 'divisions', 'departments', 'locations', 'categories', 'users', 'ticketStages'));
    }

    /**
     * Store a newly created ticket type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name',
            'code' => 'required|string|size:3|regex:/^[A-Za-z]{3}$/',
            'prototype' => 'nullable|string|in:request_response,approval_clearance',
            'description' => 'nullable|string',
            'threshold_days' => 'nullable|integer|min:1',
            'template_json' => 'nullable',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['prototype'] = $validated['prototype'] ?? 'request_response';

        if ($request->filled('template_json')) {
            $val = $request->input('template_json');
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                $validated['template_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            } elseif (is_array($val)) {
                $validated['template_json'] = $val;
            }
        } else {
            $validated['template_json'] = null;
        }

        if (($validated['prototype'] ?? null) === 'approval_clearance' && is_array($validated['template_json']) && ! empty($validated['template_json']['steps'])) {
            $firstStep = $validated['template_json']['steps'][0] ?? null;
            if ($firstStep && ! empty($firstStep['stages'])) {
                $firstStage = $firstStep['stages'][0];
                $validated['template_json']['steps'] = [
                    [
                        'step_number' => 1,
                        'stages' => [
                            [
                                'id' => (int) $firstStage['id'],
                                'name' => $firstStage['name'],
                                'color_code' => $firstStage['color_code'] ?? '#6b7280',
                            ],
                        ],
                    ],
                ];
            } else {
                unset($validated['template_json']['steps']);
            }
        }

        TicketType::create($validated);

        return redirect()->route('admin.ticket-types.index')
            ->with('success', "Ticket Type '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified ticket type.
     */
    public function edit(TicketType $ticketType)
    {
        $priorities = Priority::orderBy('level')->get();
        $divisions = Division::with('departments')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $categories = $ticketType->categories()->get();
        $users = User::orderBy('name')->get();
        $ticketStages = TicketStage::orderBy('name')->get();

        return view('admin.ticket-types.edit', compact('ticketType', 'priorities', 'divisions', 'departments', 'locations', 'categories', 'users', 'ticketStages'));
    }

    /**
     * Update the specified ticket type in storage.
     */
    public function update(Request $request, TicketType $ticketType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ticket_types,name,'.$ticketType->id,
            'code' => 'required|string|size:3|regex:/^[A-Za-z]{3}$/',
            'prototype' => 'nullable|string|in:request_response,approval_clearance',
            'description' => 'nullable|string',
            'threshold_days' => 'nullable|integer|min:1',
            'template_json' => 'nullable',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        if (isset($validated['prototype'])) {
            $ticketType->prototype = $validated['prototype'];
        }

        if ($request->filled('template_json')) {
            $val = $request->input('template_json');
            if (is_string($val)) {
                $decoded = json_decode($val, true);
                $validated['template_json'] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
            } elseif (is_array($val)) {
                $validated['template_json'] = $val;
            }
        } else {
            $validated['template_json'] = null;
        }

        $effectivePrototype = $validated['prototype'] ?? $ticketType->prototype ?? 'request_response';
        if ($effectivePrototype === 'approval_clearance' && is_array($validated['template_json']) && ! empty($validated['template_json']['steps'])) {
            $firstStep = $validated['template_json']['steps'][0] ?? null;
            if ($firstStep && ! empty($firstStep['stages'])) {
                $firstStage = $firstStep['stages'][0];
                $validated['template_json']['steps'] = [
                    [
                        'step_number' => 1,
                        'stages' => [
                            [
                                'id' => (int) $firstStage['id'],
                                'name' => $firstStage['name'],
                                'color_code' => $firstStage['color_code'] ?? '#6b7280',
                            ],
                        ],
                    ],
                ];
            } else {
                unset($validated['template_json']['steps']);
            }
        }

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
