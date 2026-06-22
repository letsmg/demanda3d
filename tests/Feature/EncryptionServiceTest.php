<?php

use App\Services\EncryptionService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('encrypt with hash returns array with encrypted and hash', function () {
    $value = 'Test Value 123';
    $result = EncryptionService::encryptWithHash($value);

    expect($result)->toHaveKeys(['encrypted', 'hash']);
    expect($result['encrypted'])->not->toBe($value);
    expect($result['hash'])->toBe(hash('sha256', preg_replace('/[.\-\/()\s]/', '', $value)));
});

test('encrypt with hash returns null for empty value', function () {
    $result = EncryptionService::encryptWithHash('');
    expect($result['encrypted'])->toBeNull();
    expect($result['hash'])->toBeNull();
});

test('encrypt with hash returns null for null value', function () {
    $result = EncryptionService::encryptWithHash(null);
    expect($result['encrypted'])->toBeNull();
    expect($result['hash'])->toBeNull();
});

test('decrypt returns original value', function () {
    $original = 'Sensitive Data 456';
    $result = EncryptionService::encryptWithHash($original);
    $decrypted = EncryptionService::decrypt($result['encrypted']);

    expect($decrypted)->toBe($original);
});

test('decrypt returns null for null input', function () {
    expect(EncryptionService::decrypt(null))->toBeNull();
});

test('decrypt returns null for empty input', function () {
    expect(EncryptionService::decrypt(''))->toBeNull();
});

test('hash generates consistent value', function () {
    $value = 'test@example.com';
    $hash1 = EncryptionService::hash($value);
    $hash2 = EncryptionService::hash($value);

    expect($hash1)->toBe($hash2);
    expect($hash1)->toBe(hash('sha256', preg_replace('/[.\-\/()\s]/', '', $value)));
});

test('hash returns null for empty value', function () {
    expect(EncryptionService::hash(''))->toBeNull();
});

test('sanitize removes whitespace and tags', function () {
    $dirty = '  <script>alert("xss")</script>Hello World  ';
    $clean = EncryptionService::sanitize($dirty);

    expect($clean)->not->toContain('<script>');
    expect(trim($clean))->toBe($clean);
    expect($clean)->toContain('Hello World');
});

test('sanitize returns null for null input', function () {
    expect(EncryptionService::sanitize(null))->toBeNull();
});

test('build encrypted fields adds encrypted and hash to data array', function () {
    $data = ['doc' => '12345678901'];
    $result = EncryptionService::buildEncryptedFields($data, 'doc');

    expect($result)->toHaveKeys(['doc', 'doc_encrypted', 'doc_hash']);
    expect($result['doc_encrypted'])->not->toBeNull();
    expect($result['doc_hash'])->not->toBeNull();
});

test('build encrypted fields uses custom field names', function () {
    $data = ['email' => 'test@example.com'];
    $result = EncryptionService::buildEncryptedFields($data, 'email', 'email_encrypted', 'email_hash');

    expect($result)->toHaveKeys(['email', 'email_encrypted', 'email_hash']);
});

test('normalize removes formatting characters', function () {
    $formatted = '(11) 99999-0000';
    $normalized = EncryptionService::normalize($formatted);
    expect($normalized)->toBe('11999990000');
});

test('normalize handles cpf formatting', function () {
    $cpf = '529.982.247-25';
    expect(EncryptionService::normalize($cpf))->toBe('52998224725');
});

test('encryption is deterministic for same value', function () {
    $value = 'Same Value';
    $result1 = EncryptionService::encryptWithHash($value);
    $result2 = EncryptionService::encryptWithHash($value);

    // Hashes should match (deterministic)
    expect($result1['hash'])->toBe($result2['hash']);
    // Encrypted values should differ (non-deterministic encryption)
    expect($result1['encrypted'])->not->toBe($result2['encrypted']);
});

test('different values produce different hashes', function () {
    $hash1 = EncryptionService::hash('value1');
    $hash2 = EncryptionService::hash('value2');
    expect($hash1)->not->toBe($hash2);
});