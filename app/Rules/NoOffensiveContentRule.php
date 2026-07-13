<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Snipe\BanBuilder\CensorWords;

/**
 * Bloqueia mensagens que contenham palavras ofensivas (palavrões).
 *
 * Utiliza a biblioteca snipe/banbuilder combinada com um algoritmo
 * próprio de normalização e dedução avançada que detecta palavrões
 * mesmo quando o usuário tenta ofuscá-los com:
 *
 *  - Letras espaçadas (c a r a l h o → caralho)
 *  - Letras repetidas/esticadas (caralhoooo → caralho)
 *  - Substituição por números/símbolos (c4r4lh0 → caralho)
 *  - Variações de gênero/sufixo (caralha, caralhos)
 *
 * A regra rejeita a requisição completamente (HTTP 422) e lista
 * os termos ofensivos detectados, exigindo reescrita do texto.
 */
class NoOffensiveContentRule implements ValidationRule
{
    /**
     * Mapa de substituição leet-speak / símbolos → letras.
     *
     * @var array<string, string>
     */
    protected const LEET_MAP = [
        '0' => 'o',
        '1' => 'i',
        '2' => 'z',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '6' => 'g',
        '7' => 't',
        '8' => 'b',
        '9' => 'g',
        '@' => 'a',
        '$' => 's',
        '#' => 'h',
        '!' => 'i',
        '+' => 't',
        '*' => 'x',
        '(' => 'c',
        '|' => 'i',
        '€' => 'e',
    ];

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('O conteúdo deve ser um texto válido.');

            return;
        }

        $original = $value;

        // Etapa 1: Decodifica entidades HTML
        $decoded = html_entity_decode($original, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Etapa 2: Remove tags HTML
        $stripped = strip_tags($decoded);

        // Etapa 3: Normalização dedutiva (leet-speak, letras espaçadas, repetidas)
        $normalized = $this->normalizeText($stripped);

        // Etapa 4: Verificação via snipe/banbuilder
        $censor = new CensorWords();
        $result = $censor->censorString($normalized);

        if (! empty($result['matched'])) {
            $detected = array_unique($result['matched']);
            $list = implode(', ', $detected);

            $fail("O texto contém linguagem ofensiva. Termo(s) detectado(s): {$list}. "
                . "Por favor, remova esses termos e reescreva a mensagem antes de enviar.");
        }
    }

    /**
     * Normaliza o texto para deduzir palavrões ofuscados.
     *
     * Pipeline:
     *  1. Leet-speak: substitui números/símbolos por letras (c4r4lh0 → caralho)
     *  2. Remove espaços internos entre letras (c a r a l h o → caralho)
     *  3. Colapsa letras repetidas (caralhoooo → caralho)
     *  4. Normaliza sufixos de gênero/número (caralha → caralho)
     *
     * @param  string  $text
     * @return string
     */
    protected function normalizeText(string $text): string
    {
        $lower = mb_strtolower(trim($text), 'UTF-8');

        // Passo 1: Leet-speak — substitui números e símbolos por letras
        $leet = strtr($lower, self::LEET_MAP);

        // Passo 2: Remove espaçamento artificial entre letras de uma mesma palavra
        // Detecta padrão "letra espaço letra espaço letra..." para palavras com 4+ caracteres espaçados
        $despaced = preg_replace_callback(
            '/\b([a-záàâãéèêíìîóòôõúùûç])\s+(?=([a-záàâãéèêíìîóòôõúùûç]\s+){3,}[a-záàâãéèêíìîóòôõúùûç])/iu',
            function ($matches) {
                return $matches[1];
            },
            $leet
        );

        // Remove quaisquer espaços isolados entre letras (de forma mais ampla)
        // Ex: "c a r a l h o" → "caralho" (padrões de 3+ letras espaçadas em sequência)
        $despaced = preg_replace('/([a-záàâãéèêíìîóòôõúùûç])\s+(?=[a-záàâãéèêíìîóòôõúùûç])/iu', '$1', $despaced);

        // Passo 3: Colapsa letras repetidas 3+ vezes consecutivas → 2 repetições
        // Ex: "caralhoooo" → "caralhoo", "caaaralho" → "caaralho"
        $collapsed = preg_replace('/([a-záàâãéèêíìîóòôõúùûç])\1{3,}/iu', '$1$1', $despaced);

        // Segunda passada: colapsa 2 repetições → 1 (para pegar "caaaaralho" ou "caralhoooo")
        // Mas só se a letra for vogal (consoantes duplas podem ser legítimas em pt-BR)
        $collapsed = preg_replace('/([aeiouáàâãéèêíìîóòôõúùû])\1+/iu', '$1', $collapsed);

        // Passo 4: Normaliza sufixos comuns de gênero/número para capturar variações
        // Ex: "caralha" → "caralho", "caralhas" → "caralho"
        $normalized = preg_replace(
            [
                '/([a-záàâãéèêíìîóòôõúùûç]+)(?:a|as|os|ona|onas|ão|ões|oes|ado|ada|ados|adas|ando|endo)$/u',
            ],
            [
                '$1o',
            ],
            $collapsed
        );

        // Aplica strtolower final para garantir consistência
        return mb_strtolower($normalized, 'UTF-8');
    }
}