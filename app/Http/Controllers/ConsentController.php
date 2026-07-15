<?php

namespace App\Http\Controllers;

use App\Services\LegalConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function __construct(
        private LegalConsentService $consentService,
    ) {}

    /**
     * Aceita os termos de uso vigentes.
     * Rota: POST /consent/accept
     */
    public function accept(Request $request): RedirectResponse
    {
        $clientId = auth()->guard('clients')->id();
        $userId = auth()->id();

        if (! $clientId && ! $userId) {
            return back()->with('error', 'Você precisa estar logado para aceitar os termos.');
        }

        $this->consentService->acceptLatestTerms(
            $request,
            $clientId ? (int) $clientId : null,
            $userId ? (int) $userId : null,
        );

        // Limpa a sessão de aviso diário para remover o banner imediatamente
        $dismissedKey = 'consent_banner_dismissed_' . ($clientId ? "client_{$clientId}" : "user_{$userId}");
        $request->session()->forget($dismissedKey);

        return back()->with('success', 'Termos de Uso aceitos com sucesso!');
    }

    /**
     * Dispensa o banner de aviso por 24 horas.
     * Rota: POST /consent/dismiss
     */
    public function dismiss(Request $request): RedirectResponse
    {
        $clientId = auth()->guard('clients')->id();
        $userId = auth()->id();

        $dismissedKey = 'consent_banner_dismissed_' . ($clientId ? "client_{$clientId}" : "user_{$userId}");
        $request->session()->put($dismissedKey, now());

        return back();
    }
}