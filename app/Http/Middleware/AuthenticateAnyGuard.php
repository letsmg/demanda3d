<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware que permite acesso se o usuário estiver autenticado
 * em QUALQUER UM dos guards: web, clients ou carriers.
 *
 * Diferente de ['auth', 'auth:clients'] que exige AMBOS simultaneamente,
 * este middleware aceita OU um OU outro.
 */
class AuthenticateAnyGuard
{
    public function handle(Request $request, Closure $next)
    {
        $isAuthenticated = auth()->check()
            || auth()->guard('clients')->check()
            || auth()->guard('carriers')->check();

        if (! $isAuthenticated) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}