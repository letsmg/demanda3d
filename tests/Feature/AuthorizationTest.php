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
    protected User $staff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['access_level' => UserAccessLevel::ADMIN]);
        $this->staff = User::factory()->create(['access_level' => UserAccessLevel::STAFF]);
        $this->customer = User::factory()->create(['access_level' => UserAccessLevel::CUSTOMER]);
    }

    public function test_admin_can_create_client(): void
    {
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

    public function test_staff_can_create_client(): void
    {
        $response = $this->actingAs($this->staff)
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

    public function test_customer_cannot_create_client(): void
    {
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

    public function test_staff_cannot_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->staff)
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
            'password' => bcrypt('password'),
        ]);

        $this->assertTrue(\Hash::verify('password', $user->password));
        $this->assertFalse(\Hash::verify('wrong-password', $user->password));
    }
}
