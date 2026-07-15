<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    if (Tenant::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }
    if (Client::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'ClientSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// LGPD — CRIPTOGRAFIA DE DADOS PII
// ══════════════════════════════════════════════════════════
test('dados pessoais do cliente são armazenados criptografados', function () {
    $client = Client::where('email', 'cliente1@teste.com')->first();

    expect($client)->not->toBeNull();

    // Campos criptografados não devem conter os valores em texto puro
    expect($client->first_name_encrypted)->not->toBe('João');
    expect($client->last_name_encrypted)->not->toBe('Silva');
    expect($client->doc_encrypted)->not->toBe('12345678901');
    expect($client->address_encrypted)->not->toBe('Rua das Flores');
});

test('hashes SHA-256 correspondem aos valores originais', function () {
    $client = Client::where('email', 'cliente1@teste.com')->first();

    expect($client->first_name_hash)->toMatch('/^[a-f0-9]{64}$/');
    expect($client->last_name_hash)->toMatch('/^[a-f0-9]{64}$/');
    expect($client->doc_hash)->toMatch('/^[a-f0-9]{64}$/');

    // Verifica integridade: hash deve bater com o valor descriptografado
    $decryptedFirstName = EncryptionService::decryptString($client->first_name_encrypted);
    expect($client->first_name_hash)->toBe(hash('sha256', $decryptedFirstName));
});

test('display_name é texto puro para exibição segura', function () {
    $client = Client::where('email', 'cliente1@teste.com')->first();

    expect($client->display_name)->toBe('Cliente 1 Silva');
    // display_name NÃO deve ser criptografado
    expect(strlen($client->display_name))->toBeGreaterThan(0);
    expect(strlen($client->display_name))->toBeLessThan(255);
});

// ══════════════════════════════════════════════════════════
// AUTENTICAÇÃO DE CLIENTE
// ══════════════════════════════════════════════════════════
test('cliente possui senha hash Argon2id', function () {
    $client = Client::where('email', 'cliente1@teste.com')->first();

    expect($client->password)->toStartWith('$argon2id$');
    expect(Hash::check('Mudar@123', $client->password))->toBeTrue();
});

test('cliente não possui relação direta com users', function () {
    $client = Client::where('email', 'cliente1@teste.com')->first();

    // A tabela clients NÃO tem coluna user_id
    expect(\Illuminate\Support\Facades\Schema::hasColumn('clients', 'user_id'))->toBeFalse();
});

// ══════════════════════════════════════════════════════════
// VÍNCULO COM TENANT
// ══════════════════════════════════════════════════════════
test('todos os clientes do seeder pertencem ao primeiro tenant', function () {
    $firstTenant = Tenant::first();
    $clients = Client::all();

    foreach ($clients as $client) {
        expect($client->tenant_id)->toBe($firstTenant->id);
    }
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de clientes é idempotente — não duplica registros', function () {
    $countBefore = Client::count();

    $this->artisan('db:seed', ['--class' => 'ClientSeeder']);

    $countAfter = Client::count();
    expect($countAfter)->toBe($countBefore);
});

// ══════════════════════════════════════════════════════════
// QUANTIDADE MÍNIMA (5 clientes)
// ══════════════════════════════════════════════════════════
test('existem pelo menos 5 clientes cadastrados', function () {
    $count = Client::count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

// ══════════════════════════════════════════════════════════
// SOFT DELETES (LGPD)
// ══════════════════════════════════════════════════════════
test('clientes possuem soft deletes ativado', function () {
    expect(\Illuminate\Support\Facades\Schema::hasColumn('clients', 'deleted_at'))->toBeTrue();

    $client = Client::first();
    $clientId = $client->id;

    // Soft delete
    $client->delete();

    // Não encontra com query normal
    expect(Client::find($clientId))->toBeNull();

    // Encontra com withTrashed
    expect(Client::withTrashed()->find($clientId))->not->toBeNull();
    expect(Client::withTrashed()->find($clientId)->deleted_at)->not->toBeNull();
});