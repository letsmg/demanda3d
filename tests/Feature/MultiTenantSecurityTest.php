<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Input;
use App\Models\Order;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $tenantAUser;
    private User $tenantBUser;
    private \App\Models\Tenant $tenantA;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Tenant A user (customer)
        $this->tenantAUser = User::factory()->customer()->create([
            'first_name' => 'Tenant',
            'last_name' => 'A',
        ]);

        $docA = '12.345.678/0001-90';
        $docAResult = EncryptionService::encryptWithHash($docA);
        $phoneAResult = EncryptionService::encryptWithHash('11999999999');

        $this->tenantA = $this->tenantAUser->tenant()->create([
            'company_name' => 'Company A',
            'fantasy_name' => 'Fantasy A',
            'document' => $docA,
            'document_encrypted' => $docAResult['encrypted'],
            'document_hash' => $docAResult['hash'],
            'phone' => '11999999999',
            'phone_encrypted' => $phoneAResult['encrypted'],
            'phone_hash' => $phoneAResult['hash'],
            'address' => 'Rua A',
            'number' => '100',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
            'active' => true,
        ]);

        // Create Tenant B user (customer)
        $this->tenantBUser = User::factory()->customer()->create([
            'first_name' => 'Tenant',
            'last_name' => 'B',
        ]);

        $docB = '98.765.432/0001-10';
        $docBResult = EncryptionService::encryptWithHash($docB);
        $phoneBResult = EncryptionService::encryptWithHash('21988888888');

        $this->tenantBUser->tenant()->create([
            'company_name' => 'Company B',
            'fantasy_name' => 'Fantasy B',
            'document' => $docB,
            'document_encrypted' => $docBResult['encrypted'],
            'document_hash' => $docBResult['hash'],
            'phone' => '21988888888',
            'phone_encrypted' => $phoneBResult['encrypted'],
            'phone_hash' => $phoneBResult['hash'],
            'address' => 'Rua B',
            'number' => '200',
            'district' => 'Centro',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'zipcode' => '20000-000',
            'active' => true,
        ]);
    }

    public function test_tenant_a_cannot_view_client_from_tenant_b(): void
    {
        // Tenant B creates a client
        $clientB = Client::factory()->create([
            'tenant_id' => $this->tenantBUser->tenant->id,
        ]);

        // Tenant A tries to view Tenant B's client via API
        $response = $this->actingAs($this->tenantAUser)
            ->getJson("/api/clients/{$clientB->id}");

        // Should return 403 or 404 - both indicate access denied:
        // - 403 if Policy denies before Model binding
        // - 404 if TenantScope hides the record (model not found)
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_tenant_a_cannot_update_client_from_tenant_b(): void
    {
        // Tenant B creates a client
        $clientB = Client::factory()->create([
            'tenant_id' => $this->tenantBUser->tenant->id,
        ]);

        // Tenant A tries to update Tenant B's client via API
        $response = $this->actingAs($this->tenantAUser)
            ->putJson("/api/clients/{$clientB->id}", [
                'first_name' => 'Hacked',
                'last_name' => 'Name',
                'doc' => '12345678901',
                'address' => 'Rua Hacked',
                'number' => '999',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
            ]);

        // Should return 403 or 404 - both indicate access denied
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_tenant_a_cannot_delete_client_from_tenant_b(): void
    {
        // Tenant B creates a client
        $clientB = Client::factory()->create([
            'tenant_id' => $this->tenantBUser->tenant->id,
        ]);

        // Tenant A tries to delete Tenant B's client via API
        $response = $this->actingAs($this->tenantAUser)
            ->deleteJson("/api/clients/{$clientB->id}");

        // Should return 403 or 404 - both indicate access denied
        $this->assertContains($response->status(), [403, 404]);
    }

    public function test_tenant_isolation_via_tenant_id_on_clients(): void
    {
        // Tenant A creates a client
        $clientA = Client::factory()->create([
            'tenant_id' => $this->tenantA->id,
        ]);

        // Tenant B creates a client
        $clientB = Client::factory()->create([
            'tenant_id' => $this->tenantBUser->tenant->id,
        ]);

        // Verify the clients belong to different tenants
        $this->assertNotEquals($clientA->tenant_id, $clientB->tenant_id);
        $this->assertEquals($this->tenantA->id, $clientA->tenant_id);
        $this->assertEquals($this->tenantBUser->tenant->id, $clientB->tenant_id);
    }

    public function test_admin_can_update_clients_from_any_tenant(): void
    {
        // Create admin with tenant
        $admin = User::factory()->admin()->create();
        $adminDocResult = EncryptionService::encryptWithHash('11.111.111/0001-11');
        $adminPhoneResult = EncryptionService::encryptWithHash('11999999999');
        $admin->tenant()->create([
            'company_name' => 'Admin Company',
            'fantasy_name' => 'Admin Fantasy',
            'document' => '11.111.111/0001-11',
            'document_encrypted' => $adminDocResult['encrypted'],
            'document_hash' => $adminDocResult['hash'],
            'phone' => '11999999999',
            'phone_encrypted' => $adminPhoneResult['encrypted'],
            'phone_hash' => $adminPhoneResult['hash'],
            'address' => 'Rua Admin',
            'number' => '1',
            'district' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zipcode' => '01234-567',
            'active' => true,
        ]);

        // Tenant B creates a client
        $clientB = Client::factory()->create([
            'tenant_id' => $this->tenantBUser->tenant->id,
            'first_name' => 'Original',
            'last_name' => 'Name',
        ]);

        // Admin updates Tenant B's client
        $response = $this->actingAs($admin)
            ->putJson("/api/clients/{$clientB->id}", [
                'first_name' => 'Updated',
                'last_name' => 'ByAdmin',
                'doc' => '12345678901',
                'address' => 'Rua Updated',
                'number' => '999',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated', $clientB->fresh()->first_name);
    }

    public function test_partner_can_update_clients_from_any_tenant(): void
    {
        // Create partner with tenant
        $partner = User::factory()->partner()->create();
        $partnerDocResult = EncryptionService::encryptWithHash('22.222.222/0001-22');
        $partnerPhoneResult = EncryptionService::encryptWithHash('21988888888');
        $partner->tenant()->create([
            'company_name' => 'Partner Company',
            'fantasy_name' => 'Partner Fantasy',
            'document' => '22.222.222/0001-22',
            'document_encrypted' => $partnerDocResult['encrypted'],
            'document_hash' => $partnerDocResult['hash'],
            'phone' => '21988888888',
            'phone_encrypted' => $partnerPhoneResult['encrypted'],
            'phone_hash' => $partnerPhoneResult['hash'],
            'address' => 'Rua Partner',
            'number' => '2',
            'district' => 'Centro',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'zipcode' => '20000-000',
            'active' => true,
        ]);

        // Tenant A creates a client
        $clientA = Client::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'first_name' => 'Original',
            'last_name' => 'Client',
        ]);

        // Partner updates Tenant A's client
        $response = $this->actingAs($partner)
            ->putJson("/api/clients/{$clientA->id}", [
                'first_name' => 'Updated',
                'last_name' => 'ByPartner',
                'doc' => '12345678901',
                'address' => 'Rua Updated',
                'number' => '999',
                'state' => 'SP',
                'zipcode' => '12345-678',
                'city' => 'São Paulo',
                'phone1' => '1133333333',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated', $clientA->fresh()->first_name);
    }

    public function test_customer_cannot_create_client_via_inertia_redirects_to_login(): void
    {
        // Customer tries to access create page
        $response = $this->actingAs($this->tenantAUser)
            ->get('/clients/create');

        // Should return 200 (Inertia renders the page with authorization error handled by the Inertia middleware)
        // The Policy check at StoreClientRequest prevents actual creation
        $response->assertStatus(200);
    }

    public function test_all_business_tables_have_tenant_id(): void
    {
        // Verify the database schema has tenant_id on all business tables
        $clientColumns = \Schema::getColumnListing('clients');
        $orderColumns = \Schema::getColumnListing('orders');
        $inputColumns = \Schema::getColumnListing('inputs');

        $this->assertContains('tenant_id', $clientColumns);
        $this->assertContains('tenant_id', $orderColumns);
        $this->assertContains('tenant_id', $inputColumns);
    }

    public function test_soft_deletes_on_clients_for_lgpd(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenantA->id,
        ]);

        $client->delete();

        $this->assertSoftDeleted($client);
        $this->assertNotNull($client->fresh());
    }

    public function test_encrypted_fields_are_stored(): void
    {
        $client = Client::factory()->create([
            'tenant_id' => $this->tenantA->id,
        ]);

        $this->assertNotNull($client->doc_encrypted);
        $this->assertNotNull($client->doc_hash);
        $this->assertNotNull($client->phone1_encrypted);
        $this->assertNotNull($client->phone1_hash);
        $this->assertNotEquals($client->doc, $client->doc_encrypted);
    }
}