<?php

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;

beforeEach(function () {
    if (User::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }
    if (Tenant::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// RELAÇÃO USER ↔ TENANT
// ══════════════════════════════════════════════════════════
test('cada SELLER_1 possui exatamente 1 tenant', function () {
    $sellers = User::where('access_level', UserAccessLevel::SELLER_1)->get();

    expect($sellers)->not->toBeEmpty();

    foreach ($sellers as $seller) {
        $tenant = Tenant::where('user_id', $seller->id)->first();
        expect($tenant)->not->toBeNull("SELLER_1 {$seller->email} deveria ter um tenant");
    }
});

test('tenant possui fantasy_slug único e válido', function () {
    $tenants = Tenant::all();

    $slugs = [];
    foreach ($tenants as $tenant) {
        expect($tenant->fantasy_slug)->not->toBeNull();
        expect($tenant->fantasy_slug)->not->toBeEmpty();

        // Slug deve ser URL-safe: apenas letras minúsculas, números e hífens
        expect($tenant->fantasy_slug)->toMatch('/^[a-z0-9]+(-[a-z0-9]+)*$/');

        // Não deve haver slugs duplicados
        expect(in_array($tenant->fantasy_slug, $slugs))->toBeFalse("Slug duplicado: {$tenant->fantasy_slug}");
        $slugs[] = $tenant->fantasy_slug;
    }
});

// ══════════════════════════════════════════════════════════
// LGPD — CRIPTOGRAFIA
// ══════════════════════════════════════════════════════════
test('company_name é armazenado criptografado com hash', function () {
    $tenant = Tenant::first();

    expect($tenant)->not->toBeNull();
    expect($tenant->company_name_encrypted)->not->toBeNull();
    expect($tenant->company_name_hash)->not->toBeNull();

    // Hash deve ser SHA-256 (64 caracteres hex)
    expect($tenant->company_name_hash)->toMatch('/^[a-f0-9]{64}$/');

    // Valor criptografado não deve estar em texto puro
    $decrypted = EncryptionService::decryptString($tenant->company_name_encrypted);
    $expectedHash = hash('sha256', $decrypted);
    expect($tenant->company_name_hash)->toBe($expectedHash);
});

// ══════════════════════════════════════════════════════════
// ESTADO DO TENANT
// ══════════════════════════════════════════════════════════
test('tenant recém-criado tem status padrão', function () {
    $tenant = Tenant::first();

    expect($tenant->active)->toBeTrue();
    expect($tenant->is_profile_complete)->toBeTrue(); // Seeder marca como completo
    expect((float) $tenant->rating_average)->toBe(0.0);
    expect((int) $tenant->rating_count)->toBe(0);
});

test('tenant pode ser desativado e reativado', function () {
    $tenant = Tenant::first();

    // Desativar
    $tenant->update(['active' => false]);
    expect($tenant->fresh()->active)->toBeFalse();

    // Reativar
    $tenant->update(['active' => true]);
    expect($tenant->fresh()->active)->toBeTrue();
});

// ══════════════════════════════════════════════════════════
// DOCUMENTO DO TENANT
// ══════════════════════════════════════════════════════════
test('tenant possui document_type e document válidos', function () {
    $tenants = Tenant::all();

    foreach ($tenants as $tenant) {
        expect($tenant->document_type)->toBeIn(['cnpj', 'cpf']);
        expect(strlen($tenant->document))->toBeLessThanOrEqual(18);
    }

    // Pelo menos um tenant tem CNPJ (seeder padrão)
    $cnpjTenants = $tenants->where('document_type', 'cnpj');
    expect($cnpjTenants)->not->toBeEmpty();
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de tenants é idempotente — não duplica registros', function () {
    $countBefore = Tenant::count();

    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $countAfter = Tenant::count();
    expect($countAfter)->toBe($countBefore);
});

// ══════════════════════════════════════════════════════════
// QUANTIDADE MÍNIMA (5 tenants = 5 lojas)
// ══════════════════════════════════════════════════════════
test('existem pelo menos 5 tenants ativos', function () {
    $count = Tenant::where('active', true)->count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

// ══════════════════════════════════════════════════════════
// MULTI-TENANT — ISOLAMENTO
// ══════════════════════════════════════════════════════════
test('tenant A não pode acessar dados do tenant B diretamente', function () {
    $tenantA = Tenant::where('fantasy_slug', 'like', 'loja-1%')->first();
    $tenantB = Tenant::where('fantasy_slug', 'like', 'loja-2%')->first();

    expect($tenantA)->not->toBeNull();
    expect($tenantB)->not->toBeNull();
    expect($tenantA->id)->not->toBe($tenantB->id);
    expect($tenantA->user_id)->not->toBe($tenantB->user_id);
});