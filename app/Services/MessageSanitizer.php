<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

class MessageSanitizer
{
    /**
     * Padrões bloqueados em mensagens entre cliente e vendedor.
     */
    private static array $blockedPatterns = [
        '/\(?\d{2}\)?\s?\d{4,5}-?\d{4}/',            // telefone brasileiro
        '/\+\d{1,3}\s?\d{2,3}\s?\d{3,4}-?\d{3,4}/', // telefone internacional
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', // email
        '/\d{3}\.\d{3}\.\d{3}-\d{2}/',               // CPF
        '/\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}/',        // CNPJ
    ];

    private static string $replacement = '[DADO PESSOAL REMOVIDO]';

    /**
     * Sanitiza o conteúdo removendo dados pessoais.
     */
    public static function sanitize(string $content): string
    {
        return preg_replace(self::$blockedPatterns, self::$replacement, $content);
    }

    /**
     * Verifica se o conteúdo contém dados pessoais bloqueados.
     */
    public static function containsSensitiveData(string $content): bool
    {
        foreach (self::$blockedPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}