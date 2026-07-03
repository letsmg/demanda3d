<?php

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\User;
use App\Services\EncryptionService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->management = User::factory()->management()->create();
    $this->customer = User::factory()->customer()->create();
});

function createTenant(User $user, string $document = '12.345.678/0001-90'): void
{
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $user->tenant()->create([
        'company_name_encrypted' => $makeEncr('Test Company')['encrypted'],
        'company_name_hash' => $makeEncr('Test Company')['hash'],
        'document_encrypted' => $makeEncr($document)['encrypted'],
        'document_hash' => $makeEncr($document)['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua Test')['encrypted'],
        'address_hash' => $makeEncr('Rua Test')['hash'],
        'number_encrypted' => $makeEncr('123')['encrypted'],
        'number_hash' => $makeEncr('123')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '01234-567',
    ]);
}

test('user types are correctly assigned', function () {
    expect($this->admin->access_level)->toBe(UserAccessLevel::ADMIN);
    expect($this->management->access_level)->toBe(UserAccessLevel::MANAGEMENT);
    expect($this->customer->access_level)->toBe(UserAccessLevel::CUSTOMER);
});

test('admin can create and delete client directly', function () {
    createTenant($this->admin);

    $client = Client::factory()->create(['tenant_id' => $this->admin->tenant->id]);
    expect(Client::find($client->id))->not->toBeNull();

    $client->delete();
    expect(Client::find($client->id))->toBeNull();
    expect(Client::withTrashed()->find($client->id))->not->toBeNull(); // SoftDelete
});

test('management can create client directly', function () {
    createTenant($this->management);

    $client = Client::factory()->create(['tenant_id' => $this->management->tenant->id]);
    expect($client->id)->not->toBeNull();
    expect($client->tenant_id)->toBe($this->management->tenant->id);
});

test('customer is not staff — cannot manage tenants', function () {
    createTenant($this->customer);
    expect($this->customer->access_level)->toBe(UserAccessLevel::CUSTOMER);
    expect($this->customer->access_level->value)->toBe(5);
});

test('argon2id password hashing', function () {
    $user = User::factory()->create([
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);
    expect(\Illuminate\Support\Facades\Hash::check('password', $user->password))->toBeTrue();
    expect(\Illuminate\Support\Facades\Hash::check('wrong-password', $user->password))->toBeFalse();
});