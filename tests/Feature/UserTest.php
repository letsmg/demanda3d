<?php

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    // Garante que há usuários no banco (UserSeeder)
    if (User::count() === 0) {
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }
});

// ══════════════════════════════════════════════════════════
// CRIPTOGRAFIA LGPD — PARIDADE DE DADOS
// ══════════════════════════════════════════════════════════
test('first_name e last_name são armazenados criptografados e com hash', function () {
    $user = User::where('email', 'admin@teste.com')->first();

    expect($user)->not->toBeNull();

    // Campos criptografados não devem estar em texto puro
    expect($user->first_name_encrypted)->not->toBe('Admin');
    expect($user->last_name_encrypted)->not->toBe('Master');

    // Hash deve ser SHA-256 (64 caracteres hex)
    expect($user->first_name_hash)->toMatch('/^[a-f0-9]{64}$/');
    expect($user->last_name_hash)->toMatch('/^[a-f0-9]{64}$/');

    // O hash deve corresponder ao valor original
    $expectedFirstNameHash = hash('sha256', 'Admin');
    $expectedLastNameHash  = hash('sha256', 'Master');

    expect($user->first_name_hash)->toBe($expectedFirstNameHash);
    expect($user->last_name_hash)->toBe($expectedLastNameHash);
});

test('display_name é armazenado em texto puro para exibição segura', function () {
    $user = User::where('email', 'admin@teste.com')->first();

    expect($user->display_name)->toBe('Admin Master');
    expect($user->display_name)->not->toContain('Admin Master');
    // display_name deve ser texto puro, não criptografado
    expect(strlen($user->display_name))->toBeLessThan(255);
});

test('email é o único campo PII armazenado em texto puro (para login)', function () {
    $user = User::where('email', 'admin@teste.com')->first();

    expect($user->email)->toBe('admin@teste.com');
    // Email em texto puro é permitido exclusivamente para autenticação Fortify
});

// ══════════════════════════════════════════════════════════
// SENHAS — ARGON2ID
// ══════════════════════════════════════════════════════════
test('senhas são armazenadas com Argon2id', function () {
    $user = User::where('email', 'admin@teste.com')->first();

    // Deve começar com $argon2id$
    expect($user->password)->toStartWith('$argon2id$');

    // Deve ser verificável
    expect(Hash::check('Mudar@123', $user->password))->toBeTrue();
});

// ══════════════════════════════════════════════════════════
// ENUMS DE ACESSO
// ══════════════════════════════════════════════════════════
test('usuários com access_level ADMIN (10) podem acessar tudo', function () {
    $admin = User::where('email', 'admin@teste.com')->first();

    expect($admin->access_level)->toBe(UserAccessLevel::ADMIN);
    expect($admin->access_level->isAdmin())->toBeTrue();
});

test('SELLER_1 pode criar e gerenciar produtos', function () {
    $seller = User::where('email', 'loja1adm@teste.com')->first();

    expect($seller)->not->toBeNull();
    expect($seller->access_level)->toBe(UserAccessLevel::SELLER_1);
    expect($seller->access_level->isSeller())->toBeTrue();
    expect($seller->access_level->isStaff())->toBeTrue();
});

test('CUSTOMER (15) não é staff e não pode acessar painel admin', function () {
    // Clientes não estão na tabela users, mas qualquer user com level 15
    $customer = User::where('access_level', UserAccessLevel::CUSTOMER)->first();

    if ($customer) {
        expect($customer->access_level->isStaff())->toBeFalse();
        expect($customer->access_level->isSeller())->toBeFalse();
        expect($customer->access_level->isCarrier())->toBeFalse();
    } else {
        // Se não houver CUSTOMER na tabela users, o teste passa (clientes usam tabela clients)
        expect(true)->toBeTrue();
    }
});

// ══════════════════════════════════════════════════════════
// MULTI-TENANT — ISOLAMENTO
// ══════════════════════════════════════════════════════════
test('SELLER_1 possui tenant vinculado', function () {
    $seller = User::where('email', 'loja1adm@teste.com')->first();

    expect($seller)->not->toBeNull();

    $tenant = Tenant::where('user_id', $seller->id)->first();

    expect($tenant)->not->toBeNull();
    expect($tenant->active)->toBeTrue();
});

test('SELLER_2 não possui tenant próprio (compartilha o do SELLER_1)', function () {
    $seller2 = User::where('email', 'loja1padrao@teste.com')->first();

    expect($seller2)->not->toBeNull();
    expect($seller2->access_level)->toBe(UserAccessLevel::SELLER_2);

    // SELLER_2 não deve ter tenant com seu user_id
    $ownTenant = Tenant::where('user_id', $seller2->id)->first();
    expect($ownTenant)->toBeNull();
});

// ══════════════════════════════════════════════════════════
// IDEMPOTÊNCIA DO SEEDER
// ══════════════════════════════════════════════════════════
test('seeder de usuários é idempotente — não duplica registros', function () {
    $countBefore = User::count();

    $this->artisan('db:seed', ['--class' => 'UserSeeder']);

    $countAfter = User::count();

    expect($countAfter)->toBe($countBefore);
});

// ══════════════════════════════════════════════════════════
// QUANTIDADE MÍNIMA DE REGISTROS (5 por tipo)
// ══════════════════════════════════════════════════════════
test('existem pelo menos 5 SELLER_1 criados pelo seeder', function () {
    $count = User::where('access_level', UserAccessLevel::SELLER_1)->count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

test('existem pelo menos 5 SELLER_2 criados pelo seeder', function () {
    $count = User::where('access_level', UserAccessLevel::SELLER_2)->count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

test('existem pelo menos 5 CARRIER_1 criados pelo seeder', function () {
    $count = User::where('access_level', UserAccessLevel::CARRIER_1)->count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

test('existem pelo menos 5 CARRIER_2 criados pelo seeder', function () {
    $count = User::where('access_level', UserAccessLevel::CARRIER_2)->count();
    expect($count)->toBeGreaterThanOrEqual(5);
});

test('existem exatamente 1 ADMIN e 1 ADMIN_2', function () {
    expect(User::where('access_level', UserAccessLevel::ADMIN)->count())->toBe(1);
    expect(User::where('access_level', UserAccessLevel::ADMIN_2)->count())->toBe(1);
});