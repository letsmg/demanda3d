<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Enums;

/**
 * Categorias de risco para moderação de conteúdo.
 *
 * Diferencia conteúdo adulto permitido (compliance) de conteúdo ilegal proibido.
 */
enum ModerationRiskCategory: string
{
    case SAFE = 'safe';
    case ADULT = 'adult';
    case ILLEGAL = 'illegal';

    /**
     * Lista de categorias que representam conteúdo ilegal/proibido.
     * Estas devem bloquear o upload imediatamente.
     */
    public static function illegalCategories(): array
    {
        return [
            'violence',
            'racy',
            'medical',
            'spoof',
            'violence_graphic',
            'drugs',
            'weapons',
            'child_exploitation',
        ];
    }

    /**
     * Lista de categorias que representam conteúdo adulto permitido.
     * Estas automaticamente vinculam o produto à categoria 'adulto'.
     */
    public static function adultCategories(): array
    {
        return [
            'adult',
            'adult_spoof',
        ];
    }

    /**
     * Classifica o resultado de moderação em uma categoria de risco.
     *
     * @param array<string, string> $safeSearchResults Resultados da API SafeSearch [category => likelihood]
     * @return array{category: self, details: string}
     */
    public static function classify(array $safeSearchResults): array
    {
        // Verifica categorias ilegais primeiro (prioridade máxima)
        foreach (self::illegalCategories() as $category) {
            $likelihood = $safeSearchResults[$category] ?? 'UNKNOWN';
            if (in_array($likelihood, ['LIKELY', 'VERY_LIKELY'], true)) {
                return [
                    'category' => self::ILLEGAL,
                    'details' => "Conteúdo ilegal detectado: {$category} ({$likelihood})",
                ];
            }
        }

        // Verifica categorias adultas
        foreach (self::adultCategories() as $category) {
            $likelihood = $safeSearchResults[$category] ?? 'UNKNOWN';
            if (in_array($likelihood, ['LIKELY', 'VERY_LIKELY'], true)) {
                return [
                    'category' => self::ADULT,
                    'details' => "Conteúdo adulto detectado: {$category} ({$likelihood})",
                ];
            }
        }

        // Resultado incerto (POSSIBLE em qualquer categoria de risco)
        foreach (array_merge(self::illegalCategories(), self::adultCategories()) as $category) {
            $likelihood = $safeSearchResults[$category] ?? 'UNKNOWN';
            if ($likelihood === 'POSSIBLE') {
                return [
                    'category' => self::SAFE,
                    'details' => "Classificação incerta para: {$category} ({$likelihood}). Marcado como pendente de revisão.",
                ];
            }
        }

        return [
            'category' => self::SAFE,
            'details' => 'Nenhum conteúdo sensível detectado.',
        ];
    }
}