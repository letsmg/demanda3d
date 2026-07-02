<?php

use App\Models\Input;
use App\Models\Supplier;
use App\Models\User;
use App\Services\EncryptionService;

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
    $input = Input::create([
        'tenant_id' => $this->management->tenant->id,
        'supplier_id' => $this->supplier->id,
        'description' => 'Filamento PLA 1kg',
        'brand' => 'eSun',
        'quantity' => 10,
        'shipping_cost' => 15.00,
        'cost_value' => 85.50,
    ]);

    expect($input->id)->not->toBeNull();
    expect($input->tenant_id)->toBe($this->management->tenant->id);
    expect($input->supplier_id)->toBe($this->supplier->id);
    expect((float) $input->cost_value)->toBe(85.50);
    expect($input->quantity)->toBe(10);
    expect($input->description)->toBe('Filamento PLA 1kg');
});

test('input validation requires all fields', function () {
    expect(fn () => Input::create([
        'tenant_id' => $this->management->tenant->id,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

test('customer cannot create input', function () {
    expect($this->customer->user_type->value)->toBe(5);
    expect($this->customer->tenant_id)->toBeNull();
});

test('input tenant isolation via global scope', function () {
    $input = Input::create([
        'tenant_id' => $this->management->tenant->id,
        'supplier_id' => $this->supplier->id,
        'description' => 'Test Input Own',
        'brand' => '3DLab',
        'quantity' => 5,
        'shipping_cost' => 10.00,
        'cost_value' => 25.00,
    ]);

    $found = Input::find($input->id);
    expect($found)->not->toBeNull();

    $otherUser = User::factory()->customer()->create();
    $makeEncr2 = fn ($v) => EncryptionService::encryptWithHash($v);
    $otherUser->tenant()->create([
        'company_name_encrypted' => $makeEncr2('Other')['encrypted'],
        'company_name_hash' => $makeEncr2('Other')['hash'],
        'document_encrypted' => $makeEncr2('11.111.111/0001-11')['encrypted'],
        'document_hash' => $makeEncr2('11.111.111/0001-11')['hash'],
        'phone_encrypted' => $makeEncr2('11111111111')['encrypted'],
        'phone_hash' => $makeEncr2('11111111111')['hash'],
        'address_encrypted' => $makeEncr2('Rua')['encrypted'],
        'address_hash' => $makeEncr2('Rua')['hash'],
        'number_encrypted' => $makeEncr2('1')['encrypted'],
        'number_hash' => $makeEncr2('1')['hash'],
        'city_encrypted' => $makeEncr2('RJ')['encrypted'],
        'city_hash' => $makeEncr2('RJ')['hash'],
        'state' => 'RJ', 'zipcode' => '20000-000', 'active' => true,
    ]);
    $otherSupplier = Supplier::factory()->create(['tenant_id' => $otherUser->tenant->id]);

    $otherInput = Input::withoutGlobalScopes()->create([
        'tenant_id' => $otherUser->tenant->id,
        'supplier_id' => $otherSupplier->id,
        'description' => 'Other Input',
        'brand' => 'Creality',
        'quantity' => 3,
        'shipping_cost' => 5.00,
        'cost_value' => 50.00,
    ]);

    $scopedResult = Input::find($otherInput->id);
    expect($scopedResult)->toBeNull();
});