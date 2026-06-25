<?php

use App\Models\Product;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

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
    $this->customer = User::factory()->customer()->create();
});

test('management can create product with 3d printing fields', function () {
    $response = actingAs($this->management)->postJson('/api/products', [
        'name' => 'Vaso Geométrico',
        'description' => 'Vaso decorativo',
        'sale_price' => 45.90,
        'height' => 150,
        'width' => 100,
        'approximate_weight' => 120,
        'waste_weight' => 15,
        'material_type' => 'filament',
        'print_time' => 360,
        'pieces_produced' => 1,
        'maintenance_fee' => 8.00,
        'painting_time' => null,
        'painting_material' => null,
        'painting_cost' => null,
        'extras_cost' => 2.00,
        'approximate_cost' => 28.50,
    ]);

    $response->assertStatus(201);
    $product = Product::first();
    expect($product->material_type)->toBe('filament');
    expect($product->print_time)->toBe(360);
    expect($product->approximate_weight)->toBe(120);
    expect($product->waste_weight)->toBe(15);
    expect($product->painting_time)->toBeNull();
    expect($product->painting_cost)->toBeNull();
    expect((float) $product->sale_price)->toBe(45.90);
});

test('product nullable painting fields accepted', function () {
    $product = Product::factory()->create([
        'painting_time' => null,
        'painting_material' => null,
        'painting_cost' => null,
        'tenant_id' => $this->management->tenant->id,
    ]);
    expect($product->painting_time)->toBeNull();
    expect($product->painting_material)->toBeNull();
    expect($product->painting_cost)->toBeNull();
});

test('customer cannot create product', function () {
    $response = actingAs($this->customer)->postJson('/api/products', [
        'name' => 'Teste',
        'sale_price' => 10,
    ]);
    $response->assertStatus(403);
});

test('product name unique per tenant', function () {
    Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->management->tenant->id,
    ]);

    expect(fn () => Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->management->tenant->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});