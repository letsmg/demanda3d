<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

class MessageSanitizer
{
    /**
     * Padrões bloqueados em mensagens entre cliente e vendedor.
     *
     * Inclui variantes com espaçamento, hífens e caracteres separados
     * para detectar tentativas de burlar o filtro de PII.
     */
    private static array $blockedPatterns = [
        // Telefone brasileiro com DDD
        '/\(?\s*\d\s*\d\s*\)?\s*\d\s*\d\s*\d\s*\d\s*\d?\s*-?\s*\d\s*\d\s*\d\s*\d/',
        // Telefone internacional
        '/\+\s*\d{1,3}\s*\d{2,3}\s*\d{3,4}\s*-?\s*\d{3,4}/',
        // E-mail (detecta partes separadas)
        '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/',
        // E-mail com espaços entre partes (ex: nome @ dominio . com)
        '/[a-zA-Z0-9._%+\-]+\s*@\s*[a-zA-Z0-9.\-]+\s*\.\s*[a-zA-Z]{2,}/',
        // CPF formatado
        '/\d{3}\.\d{3}\.\d{3}-\d{2}/',
        // CPF com espaçamento (ex: 1 2 3 . 4 5 6 . 7 8 9 - 0 1)
        '/\d\s*\d\s*\d\s*\.\s*\d\s*\d\s*\d\s*\.\s*\d\s*\d\s*\d\s*-\s*\d\s*\d/',
        // CNPJ formatado
        '/\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}/',
        // Sequência de 11 dígitos (CPF não formatado)
        '/\b\d{3}\s*\d{3}\s*\d{3}\s*\d{2}\b/',
        // Sequência de 14 dígitos (CNPJ não formatado)
        '/\b\d{2}\s*\d{3}\s*\d{3}\s*\d{4}\s*\d{2}\b/',
        // Endereço físico (Rua/Av + número)
        '/\b(rua|avenida|av\.?|travessa|praça|praca|alameda|rodovia|estrada)\s+[a-zA-ZÀ-ú\s]+[\s,]+(nº?\s*)?\d{1,6}/iu',
        // CEP
        '/\b\d{2}\s*\.?\s*\d{3}\s*-?\s*\d{3}\b/',
        // CEP com espaçamento
        '/\b\d\s*\d\s*\.\s*\d\s*\d\s*\d\s*-\s*\d\s*\d\s*\d\b/',
    ];

    private static string $replacement = '[DADO PESSOAL REMOVIDO]';

    /**
     * Sanitiza o conteúdo removendo dados pessoais.
     *
     * Pipeline de sanitização:
     *  1. Remove espaçamento entre dígitos de documentos/telefones
     *  2. Aplica os padrões de regex para detectar PII
     *  3. Substitui matches pelo placeholder [DADO PESSOAL REMOVIDO]
     */
    public static function sanitize(string $content): string
    {
        // Passo 1: Normaliza espaçamento para detectar números mascarados
        $normalized = self::normalizeSpacing($content);

        // Passo 2: Aplica padrões de substituição
        return preg_replace(self::$blockedPatterns, self::$replacement, $normalized);
    }

    /**
     * Verifica se o conteúdo contém dados pessoais bloqueados.
     *
     * Primeiro normaliza o texto (remove espaços entre dígitos) para detectar
     * PII mascarado, depois aplica os padrões de regex.
     */
    public static function containsSensitiveData(string $content): bool
    {
        $normalized = self::normalizeSpacing($content);

        foreach (self::$blockedPatterns as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Valida e sanitiza o conteúdo, retornando erro se detectar PII.
     *
     * @param string $content Conteúdo original da mensagem
     * @return array{valid: bool, sanitized: string, error: string|null}
     */
    public static function validate(string $content): array
    {
        if (self::containsSensitiveData($content)) {
            return [
                'valid'     => false,
                'sanitized' => '',
                'error'     => 'Dados pessoais detectados na mensagem. '
                    . 'Por favor, não compartilhe telefones, e-mails, documentos ou endereços. '
                    . 'Esses dados foram bloqueados para sua segurança.',
            ];
        }

        return [
            'valid'     => true,
            'sanitized' => self::sanitize($content),
            'error'     => null,
        ];
    }

    /**
     * Normaliza espaçamento entre dígitos para detectar PII mascarado.
     *
     * Remove espaços, hífens e pontos EXCESSIVOS entre dígitos consecutivos,
     * colapsando sequências como "1 2 3 . 4 5 6 . 7 8 9 - 0 1" em "123.456.789-01".
     *
     * IMPORTANTE: Não remove pontuação legítima de texto normal.
     * Apenas colapsa espaços entre caracteres que fazem parte de sequências
     * numéricas suspeitas.
     */
    protected static function normalizeSpacing(string $content): string
    {
        // Colapsa espaços entre dígitos: "1 2 3" → "123"
        $normalized = preg_replace('/(\d)\s+(?=\d)/', '$1', $content);

        // Colapsa espaços entre dígito e pontuação de documento: "123 . 456" → "123.456"
        $normalized = preg_replace('/(\d)\s*([.\-\/])\s*(?=\d)/', '$1$2', $normalized);

        // Remove espaços antes/depois de @ em e-mails mascarados: "nome @ dominio" → "nome@dominio"
        $normalized = preg_replace('/([a-zA-Z0-9._%+\-])\s*@\s*([a-zA-Z0-9.\-])/', '$1@$2', $normalized);

        return $normalized;
    }

    /**
     * Retorna os padrões bloqueados (para uso em outras camadas como NoContactDataRule).
     */
    public static function patterns(): array
    {
        return self::$blockedPatterns;
    }
}