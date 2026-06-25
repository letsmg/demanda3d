<?php

use App\Models\Input;
use App\Models\Supplier;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->management = User::factory()->management()->create();
    $this->management->tenant()->create([
        'company_name_encrypted' => $makeEncr('Co')['encrypted'],
        'company_name_hash' => $makeEncr('Co')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua')['encrypted'],
        'address_hash' => $makeEncr('Rua')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '00000-000', 'active' => true,
    ]);
    $this->supplier = Supplier::factory()->create(['tenant_id' => $this->management->tenant->id]);
    $this->customer = User::factory()->customer()->create();
});

test('management can create input', function () {
    $response = actingAs($this->management)->postJson('/api/inputs', [
        'supplier_id' => $this->supplier->id,
        'description' => 'Filamento PLA 1.75mm 1kg',
        'brand' => '3DLab',
        'purchase_date' => now()->subMonth()->toDateString(),
        'quantity' => 1000,
        'shipping_cost' => 15.90,
        'cost_value' => 89.90,
    ]);

    $response->assertStatus(201);
    expect(Input::first()->description)->toBe('Filamento PLA 1.75mm 1kg');
    expect(Input::first()->supplier_id)->toBe($this->supplier->id);
});

test('input validation requires all fields', function () {
    $response = actingAs($this->management)->postJson('/api/inputs', []);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['supplier_id', 'description', 'brand', 'purchase_date', 'quantity']);
});

test('customer cannot create input', function () {
    $response = actingAs($this->customer)->postJson('/api/inputs', [
        'supplier_id' => $this->supplier->id,
        'description' => 'Test',
        'brand' => 'Brand',
        'purchase_date' => now()->toDateString(),
        'quantity' => 100,
        'shipping_cost' => 10,
        'cost_value' => 50,
    ]);
    $response->assertStatus(403);
});

test('input tenant isolation via global scope', function () {
    $otherUser = User::factory()->customer()->create();
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $otherUser->tenant()->create([
        'company_name_encrypted' => $makeEncr('Other')['encrypted'],
        'company_name_hash' => $makeEncr('Other')['hash'],
        'document_encrypted' => $makeEncr('11.111.111/0001-11')['encrypted'],
        'document_hash' => $makeEncr('11.111.111/0001-11')['hash'],
        'phone_encrypted' => $makeEncr('11111111111')['encrypted'],
        'phone_hash' => $makeEncr('11111111111')['hash'],
        'address_encrypted' => $makeEncr('Rua')['encrypted'],
        'address_hash' => $makeEncr('Rua')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('RJ')['encrypted'],
        'city_hash' => $makeEncr('RJ')['hash'],
        'state' => 'RJ', 'zipcode' => '20000-000', 'active' => true,
    ]);
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherUser->tenant->id]);

    Input::create([
        'tenant_id' => $otherUser->tenant->id,
        'supplier_id' => $otherSupplier->id,
        'description' => 'Hidden',
        'brand' => 'X',
        'purchase_date' => now(),
        'quantity' => 1,
        'shipping_cost' => 1,
        'cost_value' => 1,
    ]);

    // Via global scope, management should not see this input
    $ownInputs = Input::count();
    expect($ownInputs)->toBe(0);
});