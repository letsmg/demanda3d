<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Encrypt a value and return both the hash and encrypted value.
     *
     * @param  string|null  $value  The value to encrypt
     * @return array{encrypted: string|null, hash: string|null}
     */
    public static function encryptWithHash(?string $value): array
    {
        if ($value === null || $value === '') {
            return [
                'encrypted' => null,
                'hash' => null,
            ];
        }

        $normalized = self::normalize($value);
        $hash = hash('sha256', $normalized);
        $encrypted = Crypt::encryptString($value);

        return [
            'encrypted' => $encrypted,
            'hash' => $hash,
        ];
    }

    /**
     * Decrypt an encrypted value.
     *
     * @param  string|null  $encryptedValue
     * @return string|null
     */
    public static function decrypt(?string $encryptedValue): ?string
    {
        if ($encryptedValue === null || $encryptedValue === '') {
            return null;
        }

        try {
            $result = Crypt::decryptString($encryptedValue);

            // Defesa contra dupla criptografia: os casts nativos 'encrypted' do
            // Laravel re-criptografavam valores já criptografados na inserção,
            // criando uma segunda camada. Descriptografamos recursivamente até
            // obter texto plano (máximo 2 iterações por segurança).
            if (self::looksEncrypted($result)) {
                $result = Crypt::decryptString($result);
            }

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verifica se uma string parece ser um payload criptografado do Laravel.
     * Payloads válidos são JSON base64-encoded com as chaves "iv", "value", "mac".
     */
    private static function looksEncrypted(string $value): bool
    {
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    /**
     * Generate a hash for a value (for searching).
     *
     * @param  string|null  $value
     * @return string|null
     */
    public static function hash(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return hash('sha256', self::normalize($value));
    }

    /**
     * Normalize a value before hashing to ensure consistent search.
     * Removes formatting characters like ., -, /, (, ), spaces.
     *
     * @param  string  $value
     * @return string
     */
    public static function normalize(string $value): string
    {
        // Remove common formatting characters for document/phone normalization
        return preg_replace('/[.\-\/()\s]/', '', $value);
    }

    /**
     * Build data array with both encrypted and hash fields.
     *
     * @param  array  $data
     * @param  string  $field  The original field name (e.g., 'document')
     * @param  string|null  $encryptedField  The encrypted field name (e.g., 'document_encrypted')
     * @param  string|null  $hashField  The hash field name (e.g., 'document_hash')
     * @return array
     */
    public static function buildEncryptedFields(array $data, string $field, ?string $encryptedField = null, ?string $hashField = null): array
    {
        $encryptedField = $encryptedField ?? $field . '_encrypted';
        $hashField = $hashField ?? $field . '_hash';

        $result = self::encryptWithHash($data[$field] ?? null);

        $data[$encryptedField] = $result['encrypted'];
        $data[$hashField] = $result['hash'];

        return $data;
    }

    /**
     * Sanitize a string value: trim, strip tags, remove duplicate spaces.
     *
     * @param  string|null  $value
     * @return string|null
     */
    public static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        $value = strip_tags($value);
        // Remove duplicate spaces
        $value = preg_replace('/\s+/', ' ', $value);

        return $value === '' ? null : $value;
    }
}