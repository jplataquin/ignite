<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('department')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.users.create', compact('departments'));
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
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
            'department_id' => $validated['department_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'must_reset_password' => true, // Force reset upon first login!
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$validated['name']}' created successfully with temporary password. They will be prompted to reset it on their first login.");
    }
}
