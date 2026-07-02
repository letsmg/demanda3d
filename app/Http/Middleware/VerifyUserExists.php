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
        // Verifica guard web (staff)
        $user = Auth::user();

        if ($user && !\App\Models\User::where('id', $user->id)->exists()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Sua sessão expirou. Faça login novamente.');
        }

        // Verifica guard clients (clientes)
        $clientUser = Auth::guard('clients')->user();

        if ($clientUser && !\App\Models\Client::where('id', $clientUser->id)->exists()) {
            Auth::guard('clients')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login_cli')->with('error', 'Sua sessão expirou. Faça login novamente.');
        }

        return $next($request);
    }
}