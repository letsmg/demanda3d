<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Services\LegalConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalConsentController extends Controller
{
    public function __construct(
        private LegalConsentService $consentService,
    ) {}

    /**
     * Exibe a página de documentos legais (política de privacidade ou termos de uso).
     */
    public function show(string $type): Response
    {
        $documents = $this->consentService->getActiveDocuments();

        $document = $type === 'terms' ? $documents['terms'] : $documents['privacy'];

        if (!$document) {
            abort(404, 'Documento não encontrado.');
        }

        return Inertia::render('Legal/Show', [
            'document' => [
                'type' => $document->type,
                'title' => $document->title,
                'content' => $document->content_html,
                'version' => $document->version,
                'published_at' => $document->published_at?->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Registra aceite do visitante via POST (para o banner de consentimento).
     */
    public function accept(Request $request): RedirectResponse
    {
        $documentType = $request->input('document_type');

        if (!in_array($documentType, ['terms_of_service', 'privacy_policy'])) {
            abort(400, 'Tipo de documento inválido.');
        }

        $this->consentService->record(
            $documentType,
            'accepted',
            $request,
            auth()->guard('clients')->id(),
            auth()->id(),
        );

        return back()->with('success', 'Consentimento registrado com sucesso.');
    }

    /**
     * Registra recusa do visitante (pode continuar navegando, mas sem cadastro).
     */
    public function decline(Request $request): RedirectResponse
    {
        $documentType = $request->input('document_type');

        if (!in_array($documentType, ['terms_of_service', 'privacy_policy'])) {
            abort(400, 'Tipo de documento inválido.');
        }

        $this->consentService->recordDeclined($documentType, $request);

        return back()->with('info', 'Você optou por não aceitar. A navegação continua permitida.');
    }

    /**
     * Endpoint para aceitar ambos os documentos de uma vez (usado no fluxo de cadastro).
     * Retorna JSON para o frontend processar antes de submeter o formulário de registro.
     */
    public function acceptBoth(Request $request): RedirectResponse
    {
        $this->consentService->recordBothAccepted($request);

        return back()->with('success', 'Consentimentos registrados com sucesso.');
    }
}