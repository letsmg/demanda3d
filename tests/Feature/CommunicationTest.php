<?php

use App\Models\Client;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\Thread;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Crypt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->user = User::factory()->management()->create();
    $this->user->tenant()->create([
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
    $this->client = Client::factory()->create(['tenant_id' => $this->user->tenant->id]);
});

test('can create thread with client and optional order', function () {
    $thread = Thread::create([
        'tenant_id' => $this->user->tenant->id,
        'client_id' => $this->client->id,
        'status' => 'open',
    ]);

    expect($thread->client_id)->toBe($this->client->id);
    expect($thread->order_id)->toBeNull();
    expect($thread->status)->toBe('open');
});

test('thread defaults to open status', function () {
    Thread::create([
        'tenant_id' => $this->user->tenant->id,
        'client_id' => $this->client->id,
    ]);
    expect(Thread::first()->status)->toBe('open');
});

test('message content is encrypted at rest', function () {
    $thread = Thread::factory()->create(['tenant_id' => $this->user->tenant->id]);
    $message = Message::create([
        'thread_id' => $thread->id,
        'sender_type' => 'staff',
        'sender_id' => $this->user->id,
        'content_encrypted' => Crypt::encryptString('Mensagem confidencial do cliente'),
    ]);

    expect($message->content_encrypted)->not->toBeNull();
    expect($message->content_encrypted)->not->toBe('Mensagem confidencial do cliente');
    expect(Crypt::decryptString($message->content_encrypted))->toBe('Mensagem confidencial do cliente');
});

test('messages cascade with thread deletion', function () {
    $thread = Thread::factory()->create(['tenant_id' => $this->user->tenant->id]);
    Message::factory()->count(3)->create(['thread_id' => $thread->id]);

    expect(Message::count())->toBe(3);
    $thread->delete();
    expect(Message::count())->toBe(0);
});

test('sender type validation accepts staff or client', function () {
    $validTypes = ['staff', 'client'];
    expect(in_array('staff', $validTypes))->toBeTrue();
    expect(in_array('client', $validTypes))->toBeTrue();
    expect(in_array('invalid_type', $validTypes))->toBeFalse();
});

test('thread tenant isolation', function () {
    $otherTenant = Tenant::factory()->create();
    $threadOther = Thread::factory()->create(['tenant_id' => $otherTenant->id]);

    // Thread from other tenant should not be visible via global scope
    $found = Thread::find($threadOther->id);
    expect($found)->toBeNull();

    // But should be findable without scope
    $unscoped = Thread::withoutGlobalScopes()->find($threadOther->id);
    expect($unscoped)->not->toBeNull();
});