<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    /**
     * Handle an incoming request.
     * Blocks client (customer) users from accessing staff routes.
     * Redirects them to the store page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If authenticated via clients guard, deny access to staff area
        if (Auth::guard('clients')->check()) {
            return redirect('/store');
        }

        return $next($request);
    }
}