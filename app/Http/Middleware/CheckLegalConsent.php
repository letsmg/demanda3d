<?php

namespace App\Http\Middleware;

use App\Enums\UserAccessLevel;
use App\Services\LegalConsentService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckLegalConsent
{
    public function __construct(
        private LegalConsentService $consentService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        // Determinar o tipo de usuário logado
        $clientId = auth()->guard('clients')->id();
        $userId = auth()->id();

        // Se não está autenticado, segue normalmente
        if (! $clientId && ! $userId) {
            return $next($request);
        }

        // Admin (access_level >= 10) nunca é bloqueado — apenas para guard web
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user && $user->access_level->isAdmin()) {
                return $next($request);
            }
        }

        // Verificar se os termos precisam ser aceitos
        $check = $this->consentService->checkPendingTerms(
            $clientId ? (int) $clientId : null,
            $userId ? (int) $userId : null,
        );

        // Verificar controle de aviso diário (cookie/sessão)
        $dismissedKey = 'consent_banner_dismissed_' . ($clientId ? "client_{$clientId}" : "user_{$userId}");
        $lastDismissed = $request->session()->get($dismissedKey);
        $canShowBanner = ! $lastDismissed || now()->diffInHours($lastDismissed) >= 24;

        if ($check['needs_acceptance']) {
            // Compartilhar dados via Inertia props para o frontend Vue
            Inertia::share('legalConsent', [
                'needs_acceptance' => true,
                'is_grace_expired' => $check['is_grace_expired'],
                'document_title'   => $check['document']?->title,
                'document_type'    => $check['document']?->type,
                'document_version' => $check['document']?->version,
                'grace_days'       => $check['grace_days'],
                'expired_at'       => $check['expired_at'],
                'show_banner'      => $canShowBanner && ! $check['is_grace_expired'],
                'dismissed_key'    => $dismissedKey, // usado pelo frontend para aviso diário
            ]);
        } else {
            // Limpar sessão se já aceitou
            $request->session()->forget($dismissedKey);
            Inertia::share('legalConsent', ['needs_acceptance' => false]);
        }

        return $next($request);
    }
}