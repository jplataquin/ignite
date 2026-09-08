<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    /**
     * Display a listing of the divisions.
     */
    public function index()
    {
        $divisions = Division::withCount(['departments', 'tickets'])->latest()->paginate(10);
        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Show the form for creating a new division.
     */
    public function create()
    {
        return view('admin.divisions.create');
    }

    /**
     * Store a newly created division in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
        ]);

        Division::create($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', "Division '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified division.
     */
    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    /**
     * Update the specified division in storage.
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
        ]);

        $division->update($validated);

        return redirect()->route('admin.divisions.index')
            ->with('success', "Division '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified division from storage.
     */
    public function destroy(Division $division)
    {
        if ($division->departments()->exists()) {
            return redirect()->route('admin.divisions.index')
                ->with('error', "Cannot delete Division '{$division->name}' because it currently has departments associated with it.");
        }

        if ($division->tickets()->exists()) {
            return redirect()->route('admin.divisions.index')
                ->with('error', "Cannot delete Division '{$division->name}' because it has active tickets associated with it.");
        }

        $divisionName = $division->name;
        $division->delete();

        return redirect()->route('admin.divisions.index')
            ->with('success', "Division '{$divisionName}' deleted successfully.");
    }
}
