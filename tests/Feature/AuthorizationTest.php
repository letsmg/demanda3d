<?php

namespace Tests\Feature;

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $partner;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->partner = User::factory()->partner()->create();
        $this->customer = User::factory()->customer()->create();
    }

    private function createTenant(User $user, string $document = '12.345.678/0001-90'): void
    {
        $documentResult = EncryptionService::encryptWithHash($document);
        $phoneResult = EncryptionService::encryptWithHash('11999999999');

        $user->tenant()->create([
            'company_name' => 'Test Company',
            'fantasy_name' => 'Test Fantasy',
            'document' => $document,
            'document_encrypted' => $documentResult['encrypted'],
            'document_hash' => $documentResult['hash'],
            'phone' => '11999999999',
            'phone_encrypted' => $phoneResult['encrypted'],
            'phone_hash' => $phoneResult['hash'],
            'address' => 'Rua Test',
            'number' => '123',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
        ]);
    }

    public function test_admin_can_create_client(): void
    {
        $this->createTenant($this->admin);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/clients', [
                'first_name' => 'Test',
                'last_name' => 'Client',
                'doc' => '12345678901',
                'address' => 'Rua Test',
                'number' => '123',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
                'phone2' => '1144444444',
            ]);

        $response->assertStatus(201);
    }

    public function test_partner_can_create_client(): void
    {
        $this->createTenant($this->partner, '98.765.432/0001-10');

        $response = $this->actingAs($this->partner)
            ->postJson('/api/clients', [
                'first_name' => 'Test',
                'last_name' => 'Client',
                'doc' => '12345678902',
                'address' => 'Rua Test',
                'number' => '123',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
                'phone2' => '1144444444',
            ]);

        $response->assertStatus(201);
    }

    public function test_customer_cannot_create_client(): void
    {
        $this->createTenant($this->customer, '11.111.111/0001-11');

        $response = $this->actingAs($this->customer)
            ->postJson('/api/clients', [
                'first_name' => 'Test',
                'last_name' => 'Client',
                'doc' => '12345678901',
                'address' => 'Rua Test',
                'number' => '123',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
                'phone2' => '1144444444',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(200);
    }

    public function test_partner_cannot_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->partner)
            ->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/clients');

        $response->assertStatus(401);
    }

    public function test_argon2id_password_hashing(): void
    {
        $user = User::factory()->create([
            'password' => \Hash::make('password'),
        ]);

        $this->assertTrue(\Hash::check('password', $user->password));
        $this->assertFalse(\Hash::check('wrong-password', $user->password));
    }
}