<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica se o usuário autenticado ainda existe no banco de dados.
 *
 * Impede que sessões órfãs (após migrate:fresh ou exclusão do usuário)
 * continuem acessando rotas protegidas como se o usuário ainda existisse.
 *
 * Executa SEM cache — uma consulta WHERE id = ? no índice primário custa
 * microssegundos e garante que usuários deletados sejam expulsos imediatamente,
 * não após 60 segundos.
 */
class VerifyUserExists
{
    public function handle(Request $request, Closure $next): Response
    {
        // Detecta qual guard está ativo nesta requisição
        $guards = ['web', 'clients', 'carriers'];

        foreach ($guards as $guard) {
            $user = Auth::guard($guard)->user();

            if (! $user) {
                continue;
            }

            $modelClass = $guard === 'clients' ? \App\Models\Client::class : \App\Models\User::class;

            if (! $modelClass::where('id', $user->id)->exists()) {
                Auth::guard($guard)->logout();

                $redirects = [
                    'web'      => '/login',
                    'clients'  => '/login_cli',
                    'carriers' => '/login_carrier',
                ];

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect($redirects[$guard] ?? '/login')
                    ->with('error', 'Sua sessão expirou. Faça login novamente.');
            }
        }

        return $next($request);
    }
}