<?php

namespace App\Http\Middleware;

use App\Enums\UserAccessLevel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int|string, mixed>  $accessLevels
     */
    public function handle(Request $request, Closure $next, string ...$accessLevels): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $allowedLevels = array_map(function ($level) {
            return UserAccessLevel::from((int) $level);
        }, $accessLevels);

        if (! in_array($user->access_level, $allowedLevels)) {
            return response()->json(['message' => 'Forbidden: Insufficient access level'], 403);
        }

        return $next($request);
    }
}
