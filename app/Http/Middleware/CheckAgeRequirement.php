<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckAgeRequirement
{
    /**
     * Valida se o usuário tem idade suficiente para acessar o conteúdo.
     *
     * - Usuários não autenticados recebem 403 se o produto for adulto.
     * - Usuários menores de 18 anos recebem 403 se o produto for adulto.
     * - Staff sempre tem acesso.
     *
     * O parâmetro 'product' deve estar vinculado via Route Model Binding.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tenta obter o produto via route model binding (parâmetro 'product')
        // ou via slug (parâmetro 'slug' nas rotas de API e web pública)
        $product = $request->route('product');

        if (!$product) {
            $slug = $request->route('slug');
            if ($slug) {
                $product = Product::withoutGlobalScopes()
                    ->where('slug', $slug)
                    ->with('categorias')
                    ->first();
            }
        }

        if (!$product) {
            // Se não há produto na rota, permite seguir (pode estar em listagem genérica)
            return $next($request);
        }

        if (!$product->hasAdultContent()) {
            // Produto não tem conteúdo adulto, permite acesso
            return $next($request);
        }

        // Produto adulto: verificar autenticação e idade
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            Log::warning('Acesso negado a conteúdo adulto: usuário não autenticado.', [
                'product_id' => $product->id,
                'ip' => $request->ip(),
            ]);

            return $this->denyAccess($request, 'Acesso restrito a maiores de 18 anos.');
        }

        if (!$user->canAccessAdultContent()) {
            Log::warning('Acesso negado a conteúdo adulto: usuário menor de idade.', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'data_nascimento' => $user->data_nascimento?->toDateString(),
            ]);

            return $this->denyAccess($request, 'Acesso restrito a maiores de 18 anos.');
        }

        return $next($request);
    }

    /**
     * Retorna resposta 403 apropriada: JSON para API, abort para Inertia/Web.
     */
    private function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $message], 403);
        }

        abort(403, $message);
    }
}
