<?php

use App\Models\Supplier;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->management = User::factory()->management()->create();
    $this->customer = User::factory()->customer()->create();
    // Create tenant for both
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->management->tenant()->create([
        'company_name_encrypted' => $makeEncr('Mgmt Co')['encrypted'],
        'company_name_hash' => $makeEncr('Mgmt Co')['hash'],
        'document_encrypted' => $makeEncr('12.345.678/0001-90')['encrypted'],
        'document_hash' => $makeEncr('12.345.678/0001-90')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua Test')['encrypted'],
        'address_hash' => $makeEncr('Rua Test')['hash'],
        'number_encrypted' => $makeEncr('123')['encrypted'],
        'number_hash' => $makeEncr('123')['hash'],
        'district_encrypted' => $makeEncr('Centro')['encrypted'],
        'district_hash' => $makeEncr('Centro')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '01000-000', 'active' => true,
    ]);
});

test('management can create supplier with lgpd parity', function () {
    $response = actingAs($this->management)->postJson('/api/suppliers', [
        'name' => 'Fornecedor Teste Ltda',
        'document' => '12.345.678/0001-90',
        'contact' => '(11) 99999-0000 / contato@teste.com',
    ]);

    $response->assertStatus(201);
    $supplier = Supplier::first();
    expect($supplier->document_hash)->not->toBeNull();
    expect($supplier->document_encrypted)->not->toBeNull();
    expect($supplier->document_hash)->toHaveLength(64);
    expect($supplier->document_encrypted)->not->toBe('12.345.678/0001-90');
    expect($supplier->contact_encrypted)->not->toBe('(11) 99999-0000 / contato@teste.com');
});

test('customer cannot create supplier', function () {
    $response = actingAs($this->customer)->postJson('/api/suppliers', [
        'name' => 'Fornecedor',
        'document' => '12.345.678/0001-90',
        'contact' => '(11) 99999-0000',
    ]);

    $response->assertStatus(403);
});

test('supplier tenant isolation', function () {
    $tenantAUser = User::factory()->customer()->create();
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $tenantAUser->tenant()->create([
        'company_name_encrypted' => $makeEncr('A')['encrypted'],
        'company_name_hash' => $makeEncr('A')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11111111111')['encrypted'],
        'phone_hash' => $makeEncr('11111111111')['hash'],
        'address_encrypted' => $makeEncr('A')['encrypted'],
        'address_hash' => $makeEncr('A')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('A')['encrypted'],
        'city_hash' => $makeEncr('A')['hash'],
        'state' => 'SP', 'zipcode' => '00000-000', 'active' => true,
    ]);

    $supplierA = Supplier::factory()->create(['tenant_id' => $tenantAUser->tenant->id]);
    $supplierB = Supplier::factory()->create(['tenant_id' => $this->management->tenant->id]);

    // Management should only see their own supplier via global scope
    $response = actingAs($this->management)->get("/api/suppliers/{$supplierA->id}");
    expect(in_array($response->status(), [403, 404]))->toBeTrue();
});

test('document hash unique per tenant', function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    Supplier::create([
        'tenant_id' => $this->management->tenant->id,
        'name' => 'Supplier 1',
        'document_hash' => $makeEncr('12.345.678/0001-90')['hash'],
        'document_encrypted' => $makeEncr('12.345.678/0001-90')['encrypted'],
        'contact_encrypted' => $makeEncr('test')['encrypted'],
    ]);

    expect(fn () => Supplier::create([
        'tenant_id' => $this->management->tenant->id,
        'name' => 'Supplier 2',
        'document_hash' => $makeEncr('12.345.678/0001-90')['hash'],
        'document_encrypted' => $makeEncr('12.345.678/0001-90')['encrypted'],
        'contact_encrypted' => $makeEncr('test')['encrypted'],
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});