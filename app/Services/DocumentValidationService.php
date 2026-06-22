<?php

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Enums\DocumentType;

class DocumentValidationService
{
    /**
     * Validate a Brazilian document (CPF or CNPJ).
     */
    public static function validate(string $doc, ?DocumentType $type = null): bool
    {
        $digits = preg_replace('/\D/', '', $doc);

        if ($type === null) {
            $type = DocumentType::detect($digits);
        }

        return match ($type) {
            DocumentType::CPF => self::validateCpf($digits),
            DocumentType::CNPJ => self::validateCnpj($digits),
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
     * Validate CNPJ (14 digits).
     */
    public static function validateCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
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
     * Extract only digits from a document string.
     */
    public static function digitsOnly(string $doc): string
    {
        return preg_replace('/\D/', '', $doc);
    }

    /**
     * Format a document with the appropriate mask.
     */
    public static function format(string $doc, ?DocumentType $type = null): string
    {
        $digits = self::digitsOnly($doc);

        if ($type === null) {
            $type = DocumentType::detect($digits);
        }

        return match ($type) {
            DocumentType::CPF => preg_replace(
                '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                '$1.$2.$3-$4',
                str_pad($digits, 11, '0', STR_PAD_LEFT)
            ),
            DocumentType::CNPJ => preg_replace(
                '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
                '$1.$2.$3/$4-$5',
                str_pad($digits, 14, '0', STR_PAD_LEFT)
            ),
        };
    }
}