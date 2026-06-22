<?php

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->partner = User::factory()->partner()->create();
    $this->customer = User::factory()->customer()->create();
});

function createTenant(User $user, string $document = '12.345.678/0001-90'): void
{
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    $user->tenant()->create([
        'company_name_encrypted' => $makeEncr('Test Company')['encrypted'],
        'company_name_hash' => $makeEncr('Test Company')['hash'],
        'fantasy_name_encrypted' => $makeEncr('Test Fantasy')['encrypted'],
        'fantasy_name_hash' => $makeEncr('Test Fantasy')['hash'],
        'document_encrypted' => $makeEncr($document)['encrypted'],
        'document_hash' => $makeEncr($document)['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua Test')['encrypted'],
        'address_hash' => $makeEncr('Rua Test')['hash'],
        'number_encrypted' => $makeEncr('123')['encrypted'],
        'number_hash' => $makeEncr('123')['hash'],
        'district_encrypted' => $makeEncr('Centro')['encrypted'],
        'district_hash' => $makeEncr('Centro')['hash'],
        'city_encrypted' => $makeEncr('São Paulo')['encrypted'],
        'city_hash' => $makeEncr('São Paulo')['hash'],
        'state' => 'SP',
        'zipcode' => '01234-567',
    ]);
}

test('admin can create client', function () {
    createTenant($this->admin);

    $response = actingAs($this->admin)->postJson('/api/clients', [
        'first_name' => 'Test',
        'last_name' => 'Client',
        'doc' => '529.982.247-25',
        'address' => 'Rua Test',
        'number' => '123',
        'state' => 'SP',
        'zipcode' => '12345-678',
        'city' => 'São Paulo',
        'phone1' => '1133333333',
        'phone2' => '1144444444',
    ]);

    $response->assertStatus(201);
});

test('partner can create client', function () {
    createTenant($this->partner, '98.765.432/0001-10');

    $response = actingAs($this->partner)->postJson('/api/clients', [
        'first_name' => 'Test',
        'last_name' => 'Client',
        'doc' => '529.982.247-25',
        'address' => 'Rua Test',
        'number' => '123',
        'state' => 'SP',
        'zipcode' => '12345-678',
        'city' => 'São Paulo',
        'phone1' => '1133333333',
        'phone2' => '1144444444',
    ]);

    $response->assertStatus(201);
});

test('customer cannot create client', function () {
    createTenant($this->customer, '11.111.111/0001-11');

    $response = actingAs($this->customer)->postJson('/api/clients', [
        'first_name' => 'Test',
        'last_name' => 'Client',
        'doc' => '529.982.247-25',
        'address' => 'Rua Test',
        'number' => '123',
        'state' => 'SP',
        'zipcode' => '12345-678',
        'city' => 'São Paulo',
        'phone1' => '1133333333',
        'phone2' => '1144444444',
    ]);

    $response->assertStatus(403);
});

test('admin can delete client', function () {
    $client = Client::factory()->create();

    $response = actingAs($this->admin)->deleteJson("/api/clients/{$client->id}");

    $response->assertStatus(200);
});

test('partner cannot delete client', function () {
    $client = Client::factory()->create();

    $response = actingAs($this->partner)->deleteJson("/api/clients/{$client->id}");

    $response->assertStatus(403);
});

test('unauthenticated user cannot access api', function () {
    $response = getJson('/api/clients');

    $response->assertStatus(401);
});

test('argon2id password hashing', function () {
    $user = User::factory()->create([
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    expect(\Illuminate\Support\Facades\Hash::check('password', $user->password))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check('wrong-password', $user->password))->toBeFalse();
});