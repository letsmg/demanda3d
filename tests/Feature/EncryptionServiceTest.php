<?php

namespace Tests\Feature;

use App\Services\EncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncryptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypt_with_hash_returns_encrypted_value_and_hash(): void
    {
        $value = '12.345.678/0001-90';
        $result = EncryptionService::encryptWithHash($value);

        $this->assertNotNull($result['encrypted']);
        $this->assertNotNull($result['hash']);
        $this->assertNotEquals($value, $result['encrypted']);
        $this->assertEquals(64, strlen($result['hash']));
    }

    public function test_encrypt_with_hash_returns_null_for_null_value(): void
    {
        $result = EncryptionService::encryptWithHash(null);

        $this->assertNull($result['encrypted']);
        $this->assertNull($result['hash']);
    }

    public function test_encrypt_with_hash_returns_null_for_empty_value(): void
    {
        $result = EncryptionService::encryptWithHash('');

        $this->assertNull($result['encrypted']);
        $this->assertNull($result['hash']);
    }

    public function test_decrypt_returns_original_value(): void
    {
        $value = '(11) 99999-0000';
        $result = EncryptionService::encryptWithHash($value);

        $decrypted = EncryptionService::decrypt($result['encrypted']);

        $this->assertEquals($value, $decrypted);
    }

    public function test_decrypt_returns_null_for_null(): void
    {
        $decrypted = EncryptionService::decrypt(null);

        $this->assertNull($decrypted);
    }

    public function test_decrypt_returns_null_for_invalid_value(): void
    {
        $decrypted = EncryptionService::decrypt('invalid-encrypted-data');

        $this->assertNull($decrypted);
    }

    public function test_hash_returns_consistent_hash_for_normalized_values(): void
    {
        $value1 = '12.345.678/0001-90';
        $value2 = '12345678000190'; // same value without formatting

        $hash1 = EncryptionService::hash($value1);
        $hash2 = EncryptionService::hash($value2);

        $this->assertEquals($hash1, $hash2);
    }

    public function test_hash_returns_different_hash_for_different_values(): void
    {
        $hash1 = EncryptionService::hash('12.345.678/0001-90');
        $hash2 = EncryptionService::hash('98.765.432/0001-10');

        $this->assertNotEquals($hash1, $hash2);
    }

    public function test_normalize_removes_formatting_characters(): void
    {
        $normalized = EncryptionService::normalize('(11) 99999-0000');

        $this->assertEquals('11999990000', $normalized);
    }

    public function test_normalize_removes_dots_slashes_dashes(): void
    {
        $normalized = EncryptionService::normalize('12.345.678/0001-90');

        $this->assertEquals('12345678000190', $normalized);
    }

    public function test_build_encrypted_fields_adds_encrypted_and_hash_to_data(): void
    {
        $data = ['doc' => '12.345.678/0001-90'];

        $result = EncryptionService::buildEncryptedFields($data, 'doc');

        $this->assertArrayHasKey('doc_encrypted', $result);
        $this->assertArrayHasKey('doc_hash', $result);
        $this->assertNotNull($result['doc_encrypted']);
        $this->assertNotNull($result['doc_hash']);
    }

    public function test_build_encrypted_fields_uses_custom_field_names(): void
    {
        $data = ['phone' => '(11) 99999-0000'];

        $result = EncryptionService::buildEncryptedFields($data, 'phone', 'phone_enc', 'phone_hash_col');

        $this->assertArrayHasKey('phone_enc', $result);
        $this->assertArrayHasKey('phone_hash_col', $result);
    }

    public function test_sanitize_trims_spaces(): void
    {
        $sanitized = EncryptionService::sanitize('  Hello World  ');

        $this->assertEquals('Hello World', $sanitized);
    }

    public function test_sanitize_strips_html_tags(): void
    {
        $sanitized = EncryptionService::sanitize('<script>alert("xss")</script>John');

        $this->assertEquals('alert("xss")John', $sanitized);
    }

    public function test_sanitize_removes_duplicate_spaces(): void
    {
        $sanitized = EncryptionService::sanitize('John    Doe');

        $this->assertEquals('John Doe', $sanitized);
    }

    public function test_sanitize_returns_null_for_null(): void
    {
        $this->assertNull(EncryptionService::sanitize(null));
    }

    public function test_encryption_and_decryption_roundtrip_for_document(): void
    {
        $originalDoc = '12.345.678/0001-90';

        $result = EncryptionService::encryptWithHash($originalDoc);
        $decrypted = EncryptionService::decrypt($result['encrypted']);

        $this->assertEquals($originalDoc, $decrypted);
        $this->assertEquals(
            EncryptionService::hash($originalDoc),
            $result['hash']
        );
    }

    public function test_encryption_and_decryption_roundtrip_for_phone(): void
    {
        $originalPhone = '(11) 98765-4321';

        $result = EncryptionService::encryptWithHash($originalPhone);
        $decrypted = EncryptionService::decrypt($result['encrypted']);

        $this->assertEquals($originalPhone, $decrypted);
        $this->assertEquals(
            EncryptionService::hash($originalPhone),
            $result['hash']
        );
    }
}