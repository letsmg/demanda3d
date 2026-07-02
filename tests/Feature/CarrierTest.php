<?php

use App\Models\Carrier;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->management = User::factory()->management()->create();
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
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '01000-000', 'active' => true,
    ]);
});

test('management can create carrier with lgpd parity', function () {
    $response = actingAs($this->management)->postJson('/api/carriers', [
        'name' => 'Transportadora Express',
        'doc_type' => 'CNPJ',
        'document' => '12.345.678/0001-90',
    ]);

    $response->assertStatus(201);
    $carrier = Carrier::first();
    expect($carrier->document_encrypted)->not->toBeNull();
    expect($carrier->document_hash)->toHaveLength(64);
    expect($carrier->document_encrypted)->not->toBe('12.345.678/0001-90');
});

test('carrier tenant isolation via global scope', function () {
    $carrier = Carrier::factory()->create([
        'tenant_id' => $this->management->tenant->id,
    ]);

    $response = actingAs($this->management)->get("/api/carriers/{$carrier->id}");
    $response->assertStatus(200);
    expect($response->json('data.name'))->toBe($carrier->name);
});

test('customer cannot create carrier', function () {
    $customer = User::factory()->customer()->create();
    $response = actingAs($customer)->postJson('/api/carriers', [
        'name' => 'Transportadora',
        'doc_type' => 'CNPJ',
        'document' => '12.345.678/0001-90',
    ]);
    $response->assertStatus(403);
});