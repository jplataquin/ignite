<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessReports
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! in_array(Auth::user()->user_type, ['admin', 'moderator'], true)) {
            abort(403, 'Unauthorized action. Only administrators and moderators are allowed to view reports.');
        }

        return $next($request);
    }
}
