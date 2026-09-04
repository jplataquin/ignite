<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfPasswordNeedsReset
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_reset_password) {
            if (!$request->is('reset-password') && !$request->routeIs('logout')) {
                return redirect()->route('password.reset.temp');
            }
        }

        return $next($request);
    }
}
