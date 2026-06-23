<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * Redirects authenticated users away from login/register pages.
     * Allows logout routes to pass through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow logout routes to always pass through
        if (in_array($request->route()?->getName(), ['logout', 'logout.client'])) {
            return $next($request);
        }

        // Check staff guard
        if (Auth::guard('web')->check()) {
            return redirect('/dashboard');
        }

        // Check client guard
        if (Auth::guard('clients')->check()) {
            return redirect('/store');
        }

        return $next($request);
    }
}