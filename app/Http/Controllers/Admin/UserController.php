<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with(['division', 'department'])->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $divisions = Division::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('divisions', 'departments', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'user_type' => 'required|string|in:admin,moderator,regular',
            'password' => 'required|string|min:8',
            'division_id' => 'required_with:department_id|required_if:user_type,regular|nullable|exists:divisions,id',
            'department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->input('division_id')) {
                        $exists = Department::where('id', $value)
                            ->where('division_id', $request->input('division_id'))
                            ->exists();
                        if (!$exists) {
                            $fail('The selected department must belong to the selected division.');
                        }
                    }
                }
            ],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
            'division_id' => $validated['division_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'must_reset_password' => true, // Force reset upon first login!
        ]);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' created successfully with temporary password. They will be prompted to reset it on their first login.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $divisions = Division::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        
        return view('admin.users.edit', compact('user', 'divisions', 'departments', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'user_type' => 'required|string|in:admin,moderator,regular',
            'division_id' => 'required_with:department_id|required_if:user_type,regular|nullable|exists:divisions,id',
            'department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->input('division_id')) {
                        $exists = Department::where('id', $value)
                            ->where('division_id', $request->input('division_id'))
                            ->exists();
                        if (!$exists) {
                            $fail('The selected department must belong to the selected division.');
                        }
                    }
                }
            ],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
            'division_id' => $validated['division_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' updated successfully.");
    }

    /**
     * Approve the specified user account.
     */
    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' has been approved and can now log in.");
    }

    /**
     * Reject and delete the specified user account.
     */
    public function reject(User $user)
    {
        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$name}' has been rejected and their account deleted.");
    }
}
