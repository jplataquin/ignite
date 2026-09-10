<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return view('auth.login');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        $divisions = \App\Models\Division::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('auth.register', compact('divisions', 'departments'));
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'division_id' => 'required_with:department_id|nullable|exists:divisions,id',
            'department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->input('division_id')) {
                        $exists = \App\Models\Department::where('id', $value)
                            ->where('division_id', $request->input('division_id'))
                            ->exists();
                        if (!$exists) {
                            $fail('The selected department must belong to the selected division.');
                        }
                    }
                }
            ],
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'user_type' => 'regular',
            'division_id' => $validated['division_id'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'is_approved' => false,
            'must_reset_password' => false,
        ]);

        // Notify all admin users
        $admins = \App\Models\User::where('user_type', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\PendingUserRegisteredNotification($user));
        }

        return redirect()->route('login')->with('success', 'Your registration was successful! Your account is currently pending administrator approval before you can sign in.');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            if (!Auth::user()->is_approved) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account is pending administrator approval before you can sign in.',
                ]);
            }

            $request->session()->regenerate();
            
            // Redirect based on whether they need to reset their password
            if (Auth::user()->must_reset_password) {
                return redirect()->route('password.reset.temp');
            }

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show the temporary password reset form.
     */
    public function showResetTemp()
    {
        if (!Auth::check() || !Auth::user()->must_reset_password) {
            return redirect('/');
        }

        return view('auth.reset-temp');
    }

    /**
     * Handle the temporary password reset.
     */
    public function resetTemp(Request $request)
    {
        if (!Auth::check() || !Auth::user()->must_reset_password) {
            return redirect('/');
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validated['password']);
        $user->must_reset_password = false;
        $user->save();

        return redirect('/')->with('success', 'Your password has been reset successfully.');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
