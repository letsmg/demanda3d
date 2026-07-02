<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica se o usuário autenticado ainda existe no banco de dados.
 *
 * Impede que sessões órfãs (após migrate:fresh ou exclusão do usuário)
 * continuem acessando rotas protegidas como se o usuário ainda existisse.
 */
class VerifyUserExists
{
    /**
     * TTL do cache de verificação (segundos).
     * Evita consulta ao banco a cada request — verifica a cada 60 segundos.
     */
    private const CACHE_TTL = 60;

    public function handle(Request $request, Closure $next): Response
    {
        // Apenas verifica se há usuário autenticado
        $user = Auth::user();

        if ($user) {
            $cacheKey = "user_exists:{$user->id}";

            $exists = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
                return \App\Models\User::where('id', $user->id)->exists();
            });

            if (!$exists) {
                Auth::logout();
                Cache::forget($cacheKey);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'Sua sessão expirou. Faça login novamente.');
            }
        }

        // Verifica também o guard de clientes
        $clientUser = Auth::guard('clients')->user();

        if ($clientUser) {
            $cacheKey = "client_exists:{$clientUser->id}";

            $exists = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($clientUser) {
                return \App\Models\Client::where('id', $clientUser->id)->exists();
            });

            if (!$exists) {
                Auth::guard('clients')->logout();
                Cache::forget($cacheKey);
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login_cli')->with('error', 'Sua sessão expirou. Faça login novamente.');
            }
        }

        return $next($request);
    }
}