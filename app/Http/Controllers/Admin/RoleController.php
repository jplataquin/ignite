<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $ticketTypes = TicketType::orderBy('name')->get();
        return view('admin.roles.create', compact('ticketTypes'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'ticket_types' => 'nullable|array',
            'ticket_types.*' => 'exists:ticket_types,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role = Role::create($validated);
        $role->ticketTypes()->sync($request->input('ticket_types', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $role->load('ticketTypes');
        $ticketTypes = TicketType::orderBy('name')->get();
        return view('admin.roles.edit', compact('role', 'ticketTypes'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'ticket_types' => 'nullable|array',
            'ticket_types.*' => 'exists:ticket_types,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $role->update($validated);
        $role->ticketTypes()->sync($request->input('ticket_types', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete Role '{$role->name}' because it currently has users assigned to it.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
