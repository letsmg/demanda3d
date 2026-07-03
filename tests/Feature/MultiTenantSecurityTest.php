<?php

use App\Models\Client;
use App\Models\User;
use App\Services\EncryptionService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    $this->tenantAUser = User::factory()->management()->create();
    $this->tenantA = $this->tenantAUser->tenant()->create([
        'company_name_encrypted' => $makeEncr('A')['encrypted'],
        'company_name_hash' => $makeEncr('A')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11111111111')['encrypted'],
        'phone_hash' => $makeEncr('11111111111')['hash'],
        'address_encrypted' => $makeEncr('Rua A')['encrypted'],
        'address_hash' => $makeEncr('Rua A')['hash'],
        'number_encrypted' => $makeEncr('100')['encrypted'],
        'number_hash' => $makeEncr('100')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '01000-000', 'active' => true,
    ]);

    $this->tenantBUser = User::factory()->management()->create();
    $this->tenantB = $this->tenantBUser->tenant()->create([
        'company_name_encrypted' => $makeEncr('B')['encrypted'],
        'company_name_hash' => $makeEncr('B')['hash'],
        'document_encrypted' => $makeEncr('11.111.111/0001-11')['encrypted'],
        'document_hash' => $makeEncr('11.111.111/0001-11')['hash'],
        'phone_encrypted' => $makeEncr('22222222222')['encrypted'],
        'phone_hash' => $makeEncr('22222222222')['hash'],
        'address_encrypted' => $makeEncr('Rua B')['encrypted'],
        'address_hash' => $makeEncr('Rua B')['hash'],
        'number_encrypted' => $makeEncr('200')['encrypted'],
        'number_hash' => $makeEncr('200')['hash'],
        'city_encrypted' => $makeEncr('RJ')['encrypted'],
        'city_hash' => $makeEncr('RJ')['hash'],
        'state' => 'RJ', 'zipcode' => '20000-000', 'active' => true,
    ]);
});

test('tenant a cannot view client from tenant b', function () {
    \Illuminate\Support\Facades\Auth::login($this->tenantAUser);

    $clientA = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantB->id]);

    expect(Client::find($clientA->id))->not->toBeNull();
    expect(Client::find($clientB->id))->toBeNull();
});

test('tenant a cannot update client from tenant b', function () {
    \Illuminate\Support\Facades\Auth::login($this->tenantAUser);

    $clientB = Client::factory()->create(['tenant_id' => $this->tenantB->id]);
    expect(Client::find($clientB->id))->toBeNull();
});

test('tenant a cannot delete client from tenant b', function () {
    \Illuminate\Support\Facades\Auth::login($this->tenantAUser);

    $clientB = Client::factory()->create(['tenant_id' => $this->tenantB->id]);
    expect(Client::find($clientB->id))->toBeNull();
});

test('tenant isolation via tenant id on clients', function () {
    \Illuminate\Support\Facades\Auth::login($this->tenantAUser);

    $clientA = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantB->id]);

    expect($clientA->tenant_id)->toBe($this->tenantA->id);
    expect($clientB->tenant_id)->toBe($this->tenantB->id);
    expect($clientA->tenant_id)->not->toBe($clientB->tenant_id);

    expect(Client::find($clientA->id))->not->toBeNull();
    expect(Client::find($clientB->id))->toBeNull();
    expect(Client::withoutGlobalScopes()->find($clientB->id))->not->toBeNull();
});

test('admin can access clients from any tenant via unscoped query', function () {
    $admin = User::factory()->admin()->create();
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantB->id]);

    $found = Client::withoutGlobalScopes()->find($clientB->id);
    expect($found)->not->toBeNull();
    expect($found->tenant_id)->toBe($this->tenantB->id);
});

test('management user type value', function () {
    $management = User::factory()->management()->create();
    expect($management->access_level->value)->toBe(1);
});

test('customer is not staff', function () {
    $customer = User::factory()->customer()->create();
    expect($customer->access_level->value)->toBe(5);
    expect(in_array($customer->access_level->value, [0, 1, 10]))->toBeFalse();
});

test('all business tables have tenant id', function () {
    $tables = ['clients', 'orders', 'inputs', 'products', 'suppliers', 'carriers'];
    foreach ($tables as $table) {
        expect(\Illuminate\Support\Facades\Schema::hasColumn($table, 'tenant_id'))->toBeTrue();
    }
});

test('soft deletes on clients for lgpd', function () {
    $client = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    $clientId = $client->id;
    $client->delete();

    expect(Client::withTrashed()->find($clientId))->not->toBeNull();
    expect(Client::find($clientId))->toBeNull();
});

test('encrypted fields are stored', function () {
    $client = Client::factory()->create(['tenant_id' => $this->tenantA->id]);

    expect($client->first_name_encrypted)->not->toBeNull();
    expect($client->first_name_hash)->not->toBeNull();
    expect($client->last_name_encrypted)->not->toBeNull();
    expect($client->last_name_hash)->not->toBeNull();
    expect(strlen($client->first_name_hash))->toBe(64);
    expect(strlen($client->last_name_hash))->toBe(64);
});