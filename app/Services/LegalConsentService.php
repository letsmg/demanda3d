<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\LegalDocument;
use App\Models\VisitorLegalConsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LegalConsentService
{
    /**
     * Registra um aceite ou recusa de documento legal para um visitante.
     */
    public function record(
        string $documentType,
        string $status,
        Request $request,
        ?int $clientId = null,
        ?int $userId = null,
    ): VisitorLegalConsent {
        $document = LegalDocument::getActive($documentType);

        if (! $document) {
            throw new \RuntimeException("Nenhum documento ativo encontrado para o tipo: {$documentType}");
        }

        $ip = $request->ip();
        $ipData = EncryptionService::encryptWithHash($ip);

        return DB::transaction(function () use ($document, $status, $ipData, $request, $clientId, $userId) {
            return VisitorLegalConsent::create([
                'legal_document_id' => $document->id,
                'status'            => $status,
                'ip_hash'           => $ipData['hash'],
                'ip_encrypted'      => $ipData['encrypted'],
                'user_agent'        => $request->userAgent(),
                'geolocation'       => null,
                'client_id'         => $clientId,
                'user_id'           => $userId,
            ]);
        });
    }

    /**
     * Registra aceite para ambos os documentos legais.
     */
    public function recordBothAccepted(Request $request, ?int $clientId = null, ?int $userId = null): array
    {
        return DB::transaction(function () use ($request, $clientId, $userId) {
            return [
                $this->record(LegalDocument::TYPE_TERMS_OF_SERVICE, VisitorLegalConsent::STATUS_ACCEPTED, $request, $clientId, $userId),
                $this->record(LegalDocument::TYPE_PRIVACY_POLICY, VisitorLegalConsent::STATUS_ACCEPTED, $request, $clientId, $userId),
            ];
        });
    }

    /**
     * Registra recusa explícita.
     */
    public function recordDeclined(string $documentType, Request $request): VisitorLegalConsent
    {
        return $this->record($documentType, VisitorLegalConsent::STATUS_DECLINED, $request);
    }

    /**
     * Verifica se um visitante já aceitou a versão ativa de ambos os documentos.
     */
    public function hasAcceptedBoth(Request $request, ?int $clientId = null, ?int $userId = null): bool
    {
        $termsDoc = LegalDocument::getActive(LegalDocument::TYPE_TERMS_OF_SERVICE);
        $privacyDoc = LegalDocument::getActive(LegalDocument::TYPE_PRIVACY_POLICY);

        $ipHash = EncryptionService::hash($request->ip());

        $hasTerms = $this->hasAcceptedDocument($termsDoc, $ipHash, $clientId, $userId);
        $hasPrivacy = $this->hasAcceptedDocument($privacyDoc, $ipHash, $clientId, $userId);

        return $hasTerms && $hasPrivacy;
    }

    /**
     * Verifica se o usuário/cliente aceitou a versão MAIS RECENTE dos termos.
     * Se não aceitou, retorna o documento pendente + se está dentro do prazo de carência.
     *
     * @return array{needs_acceptance: bool, document: ?LegalDocument, is_grace_expired: bool, expired_at: ?string}
     */
    public function checkPendingTerms(?int $clientId = null, ?int $userId = null): array
    {
        $doc = LegalDocument::getActive(LegalDocument::TYPE_TERMS_OF_SERVICE);

        if (! $doc) {
            return ['needs_acceptance' => false, 'document' => null, 'is_grace_expired' => false, 'expired_at' => null];
        }

        $hasAccepted = $this->hasAcceptedSpecificDocument($doc, $clientId, $userId);

        if ($hasAccepted) {
            return ['needs_acceptance' => false, 'document' => null, 'is_grace_expired' => false, 'expired_at' => null];
        }

        // Não aceitou — calcular prazo de carência
        $graceDays = $doc->grace_period_days ?? 7;
        $publishedAt = $doc->published_at;
        $deadline = $publishedAt ? $publishedAt->copy()->addDays($graceDays) : null;
        $isGraceExpired = $deadline ? now()->greaterThan($deadline) : false;

        return [
            'needs_acceptance' => true,
            'document'         => $doc,
            'is_grace_expired' => $isGraceExpired,
            'expired_at'       => $deadline?->toIso8601String(),
            'grace_days'       => $graceDays,
        ];
    }

    /**
     * Verifica se um consentimento aceito existe para um documento específico.
     */
    private function hasAcceptedDocument(
        ?LegalDocument $document,
        ?string $ipHash,
        ?int $clientId,
        ?int $userId,
    ): bool {
        if (! $document) {
            return false;
        }

        $query = VisitorLegalConsent::where('legal_document_id', $document->id)
            ->accepted();

        if ($clientId) {
            $query->where('client_id', $clientId);
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } elseif ($ipHash) {
            $query->where('ip_hash', $ipHash);
        } else {
            return false;
        }

        return $query->exists();
    }

    /**
     * Verifica se o usuário/cliente aceitou um documento específico (versão ativa).
     */
    public function hasAcceptedSpecificDocument(LegalDocument $document, ?int $clientId = null, ?int $userId = null): bool
    {
        $query = VisitorLegalConsent::where('legal_document_id', $document->id)
            ->accepted();

        if ($clientId) {
            $query->where('client_id', $clientId);
        } elseif ($userId) {
            $query->where('user_id', $userId);
        } else {
            return false;
        }

        return $query->exists();
    }

    /**
     * Retorna os documentos ativos para exibição no banner de consentimento.
     */
    public function getActiveDocuments(): array
    {
        return [
            'terms'   => LegalDocument::getActive(LegalDocument::TYPE_TERMS_OF_SERVICE),
            'privacy' => LegalDocument::getActive(LegalDocument::TYPE_PRIVACY_POLICY),
        ];
    }

    /**
     * Aceita os termos vigentes para o usuário/cliente autenticado.
     * Registra o consentimento e retorna true.
     */
    public function acceptLatestTerms(Request $request, ?int $clientId = null, ?int $userId = null): bool
    {
        $doc = LegalDocument::getActive(LegalDocument::TYPE_TERMS_OF_SERVICE);

        if (! $doc) {
            return false;
        }

        if ($this->hasAcceptedSpecificDocument($doc, $clientId, $userId)) {
            return true; // já aceitou
        }

        $this->record(LegalDocument::TYPE_TERMS_OF_SERVICE, VisitorLegalConsent::STATUS_ACCEPTED, $request, $clientId, $userId);

        return true;
    }

    /**
     * Verifica se um tenant (vendedor) está com os termos pendentes e com prazo expirado,
     * o que impede a exibição de seus produtos na vitrine pública.
     */
    public function isTenantBlockedFromStore(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        $check = $this->checkPendingTerms(null, $userId);

        return $check['needs_acceptance'] && $check['is_grace_expired'];
    }
}