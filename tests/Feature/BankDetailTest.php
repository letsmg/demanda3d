<?php

use App\Models\BankDetail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

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

test('bank details are saved as pending and generate a token', function () {
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
    expect($bankDetail->pending_token)->not->toBeNull();
    expect($bankDetail->pending_token)->toHaveLength(64);
    expect($bankDetail->pending_data)->not->toBeNull();
});

test('pending bank data contains encrypted fields', function () {
    $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Test Co',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => true,
    ]);

    $bankDetail = BankDetail::where('tenant_id', $this->tenant->id)->first();
    $data = json_decode($bankDetail->pending_data, true);

    // JSON contém os campos esperados
    expect($data)->toBeArray();
    expect($data)->toHaveKeys(['routing_number_encrypted', 'account_number_encrypted', 'bank_name']);
    expect($data['bank_name'])->toBe('Banco Teste');
});

test('verification email is queued when bank details are saved', function () {
    $tenantId = $this->tenant->id;

    $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Test Co',
        'account_holder_doc'  => '12.345.678/0001-90',
        'consented'           => true,
    ]);

    Mail::assertSent(\App\Mail\BankDetailChangeVerification::class, function ($mail) use ($tenantId) {
        return $mail->bankDetail->tenant_id === $tenantId;
    });
});

test('bank details fail when document does not match tenant document', function () {
    $response = $this->post('/settings/bank', [
        'bank_name'           => 'Banco Teste',
        'routing_number'      => '0001',
        'account_number'      => '123456-7',
        'account_holder_name' => 'Outra Pessoa',
        'account_holder_doc'  => '98.765.432/0001-10',
        'consented'           => true,
    ]);

    $response->assertSessionHasErrors(['account_holder_doc']);
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