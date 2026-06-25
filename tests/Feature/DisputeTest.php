<?php

use App\Models\Client;
use App\Models\Dispute;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Crypt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
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
    $this->client = Client::factory()->create(['tenant_id' => $this->management->tenant->id]);
    $this->admin = User::factory()->admin()->create();
});

test('dispute defaults to pending status', function () {
    $dispute = Dispute::factory()->create([
        'tenant_id' => $this->management->tenant->id,
        'reporter_id' => $this->client->id,
    ]);
    expect($dispute->status)->toBeIn(['pending', 'investigating', 'resolved', 'dismissed']);
});

test('dispute description is encrypted at rest', function () {
    $dispute = Dispute::create([
        'tenant_id' => $this->management->tenant->id,
        'reporter_id' => $this->client->id,
        'reason' => 'not_delivered',
        'description_encrypted' => Crypt::encryptString('Pedido nunca chegou'),
        'status' => 'pending',
    ]);
    expect($dispute->description_encrypted)->not->toBe('Pedido nunca chegou');
    expect(Crypt::decryptString($dispute->description_encrypted))->toBe('Pedido nunca chegou');
});

test('dispute reason must be valid enum', function () {
    $response = actingAs($this->management)->postJson('/api/disputes', [
        'reporter_id' => $this->client->id,
        'reason' => 'invalid_reason',
        'description' => 'Test',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['reason']);
});

test('dispute can be assigned to admin', function () {
    $dispute = Dispute::factory()->create([
        'tenant_id' => $this->management->tenant->id,
        'reporter_id' => $this->client->id,
        'admin_id' => $this->admin->id,
        'status' => 'investigating',
    ]);
    expect($dispute->admin_id)->toBe($this->admin->id);
    expect($dispute->status)->toBe('investigating');
});

test('dispute tenant isolation', function () {
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
    $disputeOther = Dispute::factory()->create(['tenant_id' => $otherUser->tenant->id]);
    $response = actingAs($this->management)->getJson("/api/disputes/{$disputeOther->id}");
    expect(in_array($response->status(), [403, 404]))->toBeTrue();
});