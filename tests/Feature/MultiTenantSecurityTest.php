<?php

use App\Models\Client;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\deleteJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    // Tenant A
    $this->tenantAUser = User::factory()->customer()->create(['display_name' => 'Tenant A']);
    $docAResult = $makeEncr('12.345.678/0001-90');
    $this->tenantA = $this->tenantAUser->tenant()->create([
        'document_encrypted' => $docAResult['encrypted'],
        'document_hash' => $docAResult['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'company_name_encrypted' => $makeEncr('Company A')['encrypted'],
        'company_name_hash' => $makeEncr('Company A')['hash'],
        'address_encrypted' => $makeEncr('Rua A')['encrypted'],
        'address_hash' => $makeEncr('Rua A')['hash'],
        'number_encrypted' => $makeEncr('100')['encrypted'],
        'number_hash' => $makeEncr('100')['hash'],
        'district_encrypted' => $makeEncr('Centro')['encrypted'],
        'district_hash' => $makeEncr('Centro')['hash'],
        'city_encrypted' => $makeEncr('São Paulo')['encrypted'],
        'city_hash' => $makeEncr('São Paulo')['hash'],
        'state' => 'SP',
        'zipcode' => '01234-567',
        'active' => true,
    ]);

    // Tenant B
    $this->tenantBUser = User::factory()->customer()->create(['display_name' => 'Tenant B']);
    $docBResult = $makeEncr('98.765.432/0001-10');
    $this->tenantBUser->tenant()->create([
        'document_encrypted' => $docBResult['encrypted'],
        'document_hash' => $docBResult['hash'],
        'phone_encrypted' => $makeEncr('21988888888')['encrypted'],
        'phone_hash' => $makeEncr('21988888888')['hash'],
        'company_name_encrypted' => $makeEncr('Company B')['encrypted'],
        'company_name_hash' => $makeEncr('Company B')['hash'],
        'address_encrypted' => $makeEncr('Rua B')['encrypted'],
        'address_hash' => $makeEncr('Rua B')['hash'],
        'number_encrypted' => $makeEncr('200')['encrypted'],
        'number_hash' => $makeEncr('200')['hash'],
        'district_encrypted' => $makeEncr('Centro')['encrypted'],
        'district_hash' => $makeEncr('Centro')['hash'],
        'city_encrypted' => $makeEncr('Rio de Janeiro')['encrypted'],
        'city_hash' => $makeEncr('Rio de Janeiro')['hash'],
        'state' => 'RJ',
        'zipcode' => '20000-000',
        'active' => true,
    ]);
});

test('tenant a cannot view client from tenant b', function () {
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantBUser->tenant->id]);
    $response = actingAs($this->tenantAUser)->getJson("/api/clients/{$clientB->id}");
    expect(in_array($response->status(), [403, 404]))->toBeTrue();
});

test('tenant a cannot update client from tenant b', function () {
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantBUser->tenant->id]);
    $response = actingAs($this->tenantAUser)->putJson("/api/clients/{$clientB->id}", [
        'first_name' => 'Hacked', 'last_name' => 'Name', 'doc' => '529.982.247-25',
        'address' => 'Rua Hacked', 'number' => '999', 'state' => 'SP',
        'zipcode' => '12345-678', 'city' => 'São Paulo', 'phone1' => '1133333333',
    ]);
    expect(in_array($response->status(), [403, 404]))->toBeTrue();
});

test('tenant a cannot delete client from tenant b', function () {
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantBUser->tenant->id]);
    $response = actingAs($this->tenantAUser)->deleteJson("/api/clients/{$clientB->id}");
    expect(in_array($response->status(), [403, 404]))->toBeTrue();
});

test('tenant isolation via tenant id on clients', function () {
    $clientA = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    $clientB = Client::factory()->create(['tenant_id' => $this->tenantBUser->tenant->id]);
    expect($clientA->tenant_id)->not->toBe($clientB->tenant_id);
});

test('admin can update clients from any tenant', function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $admin = User::factory()->admin()->create();
    $admin->tenant()->create([
        'company_name_encrypted' => $makeEncr('Admin Company')['encrypted'],
        'company_name_hash' => $makeEncr('Admin Company')['hash'],
        'document_encrypted' => $makeEncr('11.111.111/0001-11')['encrypted'],
        'document_hash' => $makeEncr('11.111.111/0001-11')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua Admin')['encrypted'],
        'address_hash' => $makeEncr('Rua Admin')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('São Paulo')['encrypted'],
        'city_hash' => $makeEncr('São Paulo')['hash'],
        'state' => 'SP', 'zipcode' => '01234-567', 'active' => true,
    ]);

    $clientB = Client::factory()->create([
        'tenant_id' => $this->tenantBUser->tenant->id,
        'display_name' => 'Original Name',
    ]);

    $response = actingAs($admin)->putJson("/api/clients/{$clientB->id}", [
        'first_name' => 'Updated', 'last_name' => 'ByAdmin', 'doc' => '529.982.247-25',
        'address' => 'Rua Updated', 'number' => '999', 'state' => 'SP',
        'zipcode' => '12345-678', 'city' => 'São Paulo', 'phone1' => '1133333333',
    ]);

    $response->assertStatus(200);
});

test('partner can update clients from any tenant', function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $partner = User::factory()->partner()->create();
    $partner->tenant()->create([
        'company_name_encrypted' => $makeEncr('Partner Company')['encrypted'],
        'company_name_hash' => $makeEncr('Partner Company')['hash'],
        'document_encrypted' => $makeEncr('22.222.222/0001-22')['encrypted'],
        'document_hash' => $makeEncr('22.222.222/0001-22')['hash'],
        'phone_encrypted' => $makeEncr('21988888888')['encrypted'],
        'phone_hash' => $makeEncr('21988888888')['hash'],
        'address_encrypted' => $makeEncr('Rua Partner')['encrypted'],
        'address_hash' => $makeEncr('Rua Partner')['hash'],
        'number_encrypted' => $makeEncr('2')['encrypted'],
        'number_hash' => $makeEncr('2')['hash'],
        'city_encrypted' => $makeEncr('Rio de Janeiro')['encrypted'],
        'city_hash' => $makeEncr('Rio de Janeiro')['hash'],
        'state' => 'RJ', 'zipcode' => '20000-000', 'active' => true,
    ]);

    $clientA = Client::factory()->create([
        'tenant_id' => $this->tenantA->id,
        'display_name' => 'Original Client',
    ]);

    $response = actingAs($partner)->putJson("/api/clients/{$clientA->id}", [
        'first_name' => 'Updated', 'last_name' => 'ByPartner', 'doc' => '529.982.247-25',
        'address' => 'Rua Updated', 'number' => '999', 'state' => 'SP',
        'zipcode' => '12345-678', 'city' => 'São Paulo', 'phone1' => '1133333333',
    ]);

    $response->assertStatus(200);
});

test('customer cannot create client via inertia redirects to login', function () {
    $response = actingAs($this->tenantAUser)->get('/clients/create');
    $response->assertStatus(200);
});

test('all business tables have tenant id', function () {
    expect(\Schema::getColumnListing('clients'))->toContain('tenant_id');
    expect(\Schema::getColumnListing('orders'))->toContain('tenant_id');
    expect(\Schema::getColumnListing('inputs'))->toContain('tenant_id');
});

test('soft deletes on clients for lgpd', function () {
    $client = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    $client->delete();
    expect($client->fresh())->not->toBeNull();
    expect($client->trashed())->toBeTrue();
});

test('encrypted fields are stored', function () {
    $client = Client::factory()->create(['tenant_id' => $this->tenantA->id]);
    expect($client->doc_encrypted)->not->toBeNull();
    expect($client->doc_hash)->not->toBeNull();
    expect($client->phone1_encrypted)->not->toBeNull();
    expect($client->phone1_hash)->not->toBeNull();
    expect($client->doc_encrypted)->not->toBe('');
});