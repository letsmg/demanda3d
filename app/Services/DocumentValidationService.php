<?php

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Enums\DocumentType;

class DocumentValidationService
{
    /**
     * Validate a Brazilian document (CPF or CNPJ).
     *
     * Para CNPJ, tenta primeiro a validação numérica tradicional (dígitos verificadores
     * por peso fixo). Se falhar, tenta a validação alfanumérica (novo formato da Receita
     * Federal com conversão ASCII).
     */
    public static function validate(string $doc, ?DocumentType $type = null): bool
    {
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $doc);

        if ($type === null) {
            $type = DocumentType::detect($clean);
        }

        return match ($type) {
            DocumentType::CPF => self::validateCpf(preg_replace('/\D/', '', $doc)),
            DocumentType::CNPJ => self::validateCnpjNumeric($clean)
                || self::validateCnpjAlfanumeric($clean),
        };
    }

    /**
     * Validate CPF (11 digits).
     */
    public static function validateCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Reject known invalid sequences (all same digit)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Validate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $digit1) {
            return false;
        }

        // Validate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cpf[10] === $digit2;
    }

    /**
     * Validate CNPJ numérico tradicional (14 dígitos).
     */
    public static function validateCnpjNumeric(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Se contém letras, não é CNPJ numérico puro
        if (preg_match('/[A-Za-z]/', $cnpj)) {
            return false;
        }

        // Reject known invalid sequences (all same digit)
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Validate first check digit
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        // Validate second check digit
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digit2;
    }

    /**
     * Validate CNPJ alfanumérico (novo formato da Receita Federal).
     *
     * Utiliza conversão ASCII para calcular os dígitos verificadores:
     *   - Caracteres alfabéticos (A-Z): valor = ord(caractere) - 48
     *   - Caracteres numéricos (0-9): valor = (int) caractere
     *
     * Algoritmo baseado na Nota Técnica da RFB para CNPJ alfanumérico.
     */
    public static function validateCnpjAlfanumeric(string $cnpj): bool
    {
        $cnpj = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $cnpj));

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Se for 100% numérico, delega para o validador tradicional
        if (preg_match('/^\d{14}$/', $cnpj)) {
            return false; // deixa o validateCnpjNumeric cuidar
        }

        // Valida primeiro dígito verificador (posição 12)
        for ($t = 12; $t < 14; $t++) {
            $soma = 0;
            $pos = $t - 7;
            for ($i = 0; $i < $t; $i++) {
                $caractere = $cnpj[$i];
                $valor = ctype_alpha($caractere) ? (ord($caractere) - 48) : (int) $caractere;
                $soma += $valor * $pos;
                $pos--;
                if ($pos < 2) {
                    $pos = 9;
                }
            }
            $r = $soma % 11;
            $dv = ($r < 2) ? 0 : 11 - $r;
            if ((int) $cnpj[$t] !== $dv) {
                return false;
            }
        }

        return true;
    }

    /**
     * Método legado mantido para compatibilidade (alias para validateCnpjNumeric).
     *
     * @deprecated Use validateCnpjNumeric() ou validateCnpjAlfanumeric() diretamente.
     */
    public static function validateCnpj(string $cnpj): bool
    {
        return self::validateCnpjNumeric($cnpj);
    }

    /**
     * Extract only digits from a document string (para CPF).
     * Para CNPJ alfanumérico, use cleanDocument().
     */
    public static function digitsOnly(string $doc): string
    {
        return preg_replace('/\D/', '', $doc);
    }

    /**
     * Remove caracteres de formatação mantendo letras e números (para CNPJ alfanumérico).
     */
    public static function cleanDocument(string $doc): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $doc));
    }

    /**
     * Format a document with the appropriate mask.
     */
    public static function format(string $doc, ?DocumentType $type = null): string
    {
        $clean = self::cleanDocument($doc);

        if ($type === null) {
            $type = DocumentType::detect($clean);
        }

        return match ($type) {
            DocumentType::CPF => preg_replace(
                '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                '$1.$2.$3-$4',
                str_pad(preg_replace('/[^0-9]/', '', $clean), 11, '0', STR_PAD_LEFT)
            ),
            DocumentType::CNPJ => preg_replace(
                '/([A-Z0-9]{2})([A-Z0-9]{3})([A-Z0-9]{3})([A-Z0-9]{4})([A-Z0-9]{2})/',
                '$1.$2.$3/$4-$5',
                str_pad($clean, 14, '0', STR_PAD_LEFT)
            ),
        };
    }
}