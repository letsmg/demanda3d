<?php

use App\Models\BankDetail;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;

beforeEach(function () {
    $this->seller = User::factory()->seller1()->create([
        'email_verified_at' => now(),
    ]);

    $this->tenant = $this->seller->tenant()->create([
        'company_name_encrypted' => makeEncr('Test Co')['encrypted'],
        'company_name_hash'      => makeEncr('Test Co')['hash'],
        'fantasy_name'           => 'Test Co',
        'fantasy_slug'           => 'test-co-' . uniqid(),
        'document'               => '12.345.678/0001-90',
        'city'                   => 'SP',
        'state'                  => 'SP',
        'zipcode'                => '01000-000',
        'active'                 => true,
    ]);

    $this->actingAs($this->seller);
});

test('bank details can be saved with matching document', function () {
    $response = $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'bank_pix_key'        => '12.345.678/0001-90',
        'account_holder_name' => 'Test Co',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => true,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');

    $bankDetail = BankDetail::where('tenant_id', $this->tenant->id)->first();
    expect($bankDetail)->not->toBeNull();
    expect($bankDetail->consented)->toBeTrue();
    expect($bankDetail->consented_at)->not->toBeNull();
    expect($bankDetail->consent_ip)->not->toBeNull();
    expect($bankDetail->consent_term_version)->toBe('1.0');
    expect($bankDetail->routing_number)->toBe('0001');
    expect($bankDetail->account_number)->toBe('123456-7');
});

test('bank details fail when document does not match tenant document', function () {
    $response = $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Outra Pessoa',
        'account_holder_doc'  => '98.765.432/0001-10', // DIFERENTE do tenant
        'consented'           => true,
    ]);

    $response->assertSessionHasErrors(['account_holder_doc']);
    expect(BankDetail::where('tenant_id', $this->tenant->id)->exists())->toBeFalse();
});

test('bank details fail when consent is not given', function () {
    $response = $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Test Co',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => false,
    ]);

    $response->assertSessionHasErrors(['consented']);
});

test('bank details update legal_responsible_name in tenant', function () {
    $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Responsável Legal',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => true,
    ]);

    $this->tenant->refresh();
    expect($this->tenant->legal_responsible_name)->toBe('Responsável Legal');
});

test('bank details are encrypted at rest', function () {
    $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Test Co',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => true,
    ]);

    $bankDetail = BankDetail::where('tenant_id', $this->tenant->id)->first();

    // Dados no banco devem estar criptografados (não texto puro)
    expect($bankDetail->routing_number_encrypted)->not->toBe('0001');
    expect($bankDetail->account_number_encrypted)->not->toBe('123456-7');

    // Accessors devem descriptografar
    expect($bankDetail->routing_number)->toBe('0001');
    expect($bankDetail->account_number)->toBe('123456-7');
});