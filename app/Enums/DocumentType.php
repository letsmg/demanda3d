<?php

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Enums;

enum DocumentType: string
{
    case CPF = 'CPF';
    case CNPJ = 'CNPJ';

    /**
     * Detect the document type based on the number of digits.
     */
    public static function detect(string $doc): self
    {
        $digits = preg_replace('/\D/', '', $doc);
        $len = strlen($digits);

        if ($len <= 11) {
            return self::CPF;
        }

        return self::CNPJ;
    }

    /**
     * Get the number of digits for this document type.
     */
    public function length(): int
    {
        return match ($this) {
            self::CPF => 11,
            self::CNPJ => 14,
        };
    }
}