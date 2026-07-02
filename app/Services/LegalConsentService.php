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
     *
     * @param string $documentType 'terms_of_service' ou 'privacy_policy'
     * @param string $status 'accepted' ou 'declined'
     * @param Request $request Para extrair IP e user agent
     * @param int|null $clientId Cliente autenticado (opcional)
     * @param int|null $userId Usuário staff autenticado (opcional)
     */
    public function record(
        string $documentType,
        string $status,
        Request $request,
        ?int $clientId = null,
        ?int $userId = null,
    ): VisitorLegalConsent {
        $document = LegalDocument::getActive($documentType);

        // Se não há documento ativo, não há consentimento a registrar
        if (!$document) {
            throw new \RuntimeException("Nenhum documento ativo encontrado para o tipo: {$documentType}");
        }

        $ip = $request->ip();
        $ipData = EncryptionService::encryptWithHash($ip);

        return DB::transaction(function () use ($document, $status, $ipData, $request, $clientId, $userId) {
            return VisitorLegalConsent::create([
                'legal_document_id' => $document->id,
                'status' => $status,
                'ip_hash' => $ipData['hash'],
                'ip_encrypted' => $ipData['encrypted'],
                'user_agent' => $request->userAgent(),
                'geolocation' => null, // Pode ser preenchido futuramente via API de geolocalização
                'client_id' => $clientId,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Registra aceite para ambos os documentos legais (para cadastro obrigatório).
     *
     * @return VisitorLegalConsent[] Array com os dois registros de consentimento
     */
    public function recordBothAccepted(Request $request, ?int $clientId = null, ?int $userId = null): array
    {
        return DB::transaction(function () use ($request, $clientId, $userId) {
            $consents = [];

            $consents[] = $this->record(
                LegalDocument::TYPE_TERMS_OF_SERVICE,
                VisitorLegalConsent::STATUS_ACCEPTED,
                $request,
                $clientId,
                $userId,
            );

            $consents[] = $this->record(
                LegalDocument::TYPE_PRIVACY_POLICY,
                VisitorLegalConsent::STATUS_ACCEPTED,
                $request,
                $clientId,
                $userId,
            );

            return $consents;
        });
    }

    /**
     * Registra recusa explícita (visitante pode recusar e continuar navegando).
     */
    public function recordDeclined(string $documentType, Request $request): VisitorLegalConsent
    {
        return $this->record(
            $documentType,
            VisitorLegalConsent::STATUS_DECLINED,
            $request,
        );
    }

    /**
     * Verifica se um visitante já aceitou a versão ativa de ambos os documentos.
     *
     * @return bool True se aceitou ambos os documentos na versão ativa
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
     * Verifica se um consentimento aceito existe para um documento específico.
     */
    private function hasAcceptedDocument(
        ?LegalDocument $document,
        ?string $ipHash,
        ?int $clientId,
        ?int $userId,
    ): bool {
        if (!$document) {
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
     * Retorna os documentos ativos para exibição no banner de consentimento.
     *
     * @return array{terms: LegalDocument|null, privacy: LegalDocument|null}
     */
    public function getActiveDocuments(): array
    {
        return [
            'terms' => LegalDocument::getActive(LegalDocument::TYPE_TERMS_OF_SERVICE),
            'privacy' => LegalDocument::getActive(LegalDocument::TYPE_PRIVACY_POLICY),
        ];
    }
}