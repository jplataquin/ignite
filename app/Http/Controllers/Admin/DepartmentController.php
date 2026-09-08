<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index()
    {
        $departments = Department::with('division')->withCount(['users', 'tickets'])->latest()->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        return view('admin.departments.create', compact('divisions'));
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        $divisions = Division::orderBy('name')->get();
        return view('admin.departments.edit', compact('department', 'divisions'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$validated['name']}' updated successfully.");
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return redirect()->route('admin.departments.index')
                ->with('error', "Cannot delete Department '{$department->name}' because it currently has users assigned to it.");
        }

        if ($department->tickets()->exists()) {
            return redirect()->route('admin.departments.index')
                ->with('error', "Cannot delete Department '{$department->name}' because it has active tickets associated with it.");
        }

        $departmentName = $department->name;
        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', "Department '{$departmentName}' deleted successfully.");
    }
}
