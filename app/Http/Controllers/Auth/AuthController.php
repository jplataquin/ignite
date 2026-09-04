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
