<?php

namespace Tests\Feature;

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
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

    public function test_admin_can_create_client(): void
    {
        $this->admin->tenant()->create([
            'company_name' => 'Admin Company',
            'fantasy_name' => 'Admin Fantasy',
            'document' => '12.345.678/0001-90',
            'phone' => '11999999999',
            'address' => 'Rua Test',
            'number' => '123',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/clients', [
                'name' => 'Test Client',
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
        $this->partner->tenant()->create([
            'company_name' => 'Partner Company',
            'fantasy_name' => 'Partner Fantasy',
            'document' => '98.765.432/0001-10',
            'phone' => '11988888888',
            'address' => 'Rua Test 2',
            'number' => '456',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
        ]);

        $response = $this->actingAs($this->partner)
            ->postJson('/api/clients', [
                'name' => 'Test Client',
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
        $this->customer->tenant()->create([
            'company_name' => 'Customer Company',
            'fantasy_name' => null,
            'document' => '11.111.111/0001-11',
            'phone' => '11977777777',
            'address' => 'Rua Test 3',
            'number' => '789',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
        ]);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/clients', [
                'name' => 'Test Client',
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

        $this->assertTrue(\Hash::verify('password', $user->password));
        $this->assertFalse(\Hash::verify('wrong-password', $user->password));
    }
}
