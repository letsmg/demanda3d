<?php

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\post;

beforeEach(function () {
    // Garantir que existe pelo menos um tenant ativo
    if (Tenant::count() === 0) {
        $user = User::factory()->seller1()->create();
        Tenant::factory()->create([
            'user_id' => $user->id,
            'active' => true,
            'is_profile_complete' => true,
        ]);
    }
});

// ═══════════════════════════════════════════════════
// REGISTRO DE VENDEDOR (/register)
// ═══════════════════════════════════════════════════
test('register seller creates user and tenant with valid credentials', function () {
    $email = 'vendedor_' . uniqid() . '@teste.com';

    $response = post('/register', [
        'name' => 'Loja Teste',
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '1',
        'accept_privacy' => '1',
    ]);

    $response->assertRedirect('/dashboard');

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->access_level)->toBe(UserAccessLevel::SELLER_1);
    expect($user->email_verified_at)->not->toBeNull();

    // Deve ter criado um tenant com placeholders
    $tenant = $user->tenant;
    expect($tenant)->not->toBeNull();
    expect($tenant->is_profile_complete)->toBeFalse();
    expect($tenant->active)->toBeFalse();
});

test('register seller fails without accepting terms', function () {
    $email = 'vendedor_' . uniqid() . '@teste.com';

    $response = post('/register', [
        'name' => 'Loja Teste',
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '0',
        'accept_privacy' => '0',
    ]);

    $response->assertSessionHasErrors(['accept_terms', 'accept_privacy']);
});

test('register seller fails with duplicate email', function () {
    $email = 'duplicado_' . uniqid() . '@teste.com';

    post('/register', [
        'name' => 'Loja 1',
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '1',
        'accept_privacy' => '1',
    ]);

    $response = post('/register', [
        'name' => 'Loja 2',
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '1',
        'accept_privacy' => '1',
    ]);

    $response->assertSessionHasErrors('email');
});

// ═══════════════════════════════════════════════════
// REGISTRO DE CLIENTE (/register_cli)
// ═══════════════════════════════════════════════════
test('register client creates client record', function () {
    $email = 'cliente_' . uniqid() . '@teste.com';

    $response = post('/register_cli', [
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '1',
        'accept_privacy' => '1',
    ]);

    $response->assertRedirect('/store');

    $client = \App\Models\Client::where('email', $email)->first();
    expect($client)->not->toBeNull();
    expect($client->is_profile_complete)->toBeFalse();
    expect($client->tenand_id)->not->toBeNull();
});

test('register client fails without accepting terms', function () {
    $email = 'cliente_' . uniqid() . '@teste.com';

    $response = post('/register_cli', [
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '0',
        'accept_privacy' => '0',
    ]);

    $response->assertSessionHasErrors(['accept_terms', 'accept_privacy']);
});

// ═══════════════════════════════════════════════════
// REGISTRO DE TRANSPORTADORA (/register_carrier)
// ═══════════════════════════════════════════════════
test('register carrier creates user + carrier', function () {
    $email = 'transp_' . uniqid() . '@teste.com';

    $response = post('/register_carrier', [
        'email' => $email,
        'password' => 'Mudar@123',
        'password_confirmation' => 'Mudar@123',
        'accept_terms' => '1',
        'accept_privacy' => '1',
    ]);

    $response->assertRedirect(route('login.carrier'));

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->access_level)->toBe(UserAccessLevel::CARRIER_1);
    expect($user->email_verified_at)->not->toBeNull();

    $carrier = $user->carrier;
    expect($carrier)->not->toBeNull();
    expect($carrier->is_active)->toBeTrue();
    expect($carrier->is_profile_complete)->toBeFalse();
    expect($carrier->fantasy_name)->toContain('Transportes');
});