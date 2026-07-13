<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Bloqueia mensagens que contenham dados de contato pessoal
 * (e-mails, telefones) para segurança da transação.
 *
 * A regra identifica o termo suspeito e o inclui na mensagem
 * de erro para que o usuário saiba exatamente o que remover.
 */
class NoContactDataRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('O conteúdo deve ser um texto válido.');

            return;
        }

        $pattern = self::pattern();
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $stripped = strip_tags($decoded);

        if (preg_match($pattern, $stripped, $matches)) {
            $offending = $matches[1] ?? $matches[0];

            $fail("Dados de contato não são permitidos para a segurança da transação. "
                . "Termo detectado: \"{$offending}\". Por favor, remova qualquer telefone ou e-mail e tente novamente.");
        }
    }

    /**
     * Pattern que detecta e-mails e telefones brasileiros.
     *
     * Formatos detectados:
     *  - E-mails (ex: nome@dominio.com)
     *  - Telefones fixos: (XX) XXXX-XXXX
     *  - Celulares: (XX) XXXXX-XXXX
     *  - Celulares com 9 dígitos: 9XXXX-XXXX
     *  - Telefones sem DDD: XXXX-XXXX, XXXXX-XXXX
     *  - Sequências numéricas contínuas com 8+ dígitos
     *  - Formatos com pontos/espaços: XX XXXX XXXX, XX.XXXXX.XXXX
     */
    public static function pattern(): string
    {
        return '/('
            // E-mails
            . '[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}'
            . '|'
            // Telefones com DDD e separadores: (XX) XXXX-XXXX, (XX) XXXXX-XXXX
            . '\(\d{2,3}\)\s*\d{4,5}-?\d{4}'
            . '|'
            // Telefones sem DDD com hífen: XXXX-XXXX, XXXXX-XXXX
            . '\d{4,5}-\d{4}'
            . '|'
            // Sequências de 8 ou mais dígitos (potenciais números de telefone sem formatação)
            . '\b\d{8,}\b'
            . '|'
            // WhatsApp formatado: XX XXXXX XXXX, XX XXXXX-XXXX
            . '\d{2}\s\d{4,5}\s\d{4}'
            . '|'
            // Celular com 9 na frente: 9XXXX-XXXX, 9 XXXX-XXXX
            . '\b9\d{4}[\s-]?\d{4}\b'
            . '|'
            // Formatos com pontos: XX.XXXXX.XXXX, XX.XXXX.XXXX
            . '\d{2}\.\d{4,5}\.\d{4}'
            . ')/i';
    }
}