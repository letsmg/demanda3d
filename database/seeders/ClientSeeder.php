<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $makeEncr = fn ($value) => EncryptionService::encryptWithHash($value);
        $password = Hash::make('password');

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado para associar clientes.');
            return;
        }

        // Distribuir clientes fixos entre o primeiro tenant
        $firstTenant = $tenants->first();

        // Client 1 - CNPJ
        Client::factory()->create([
            'tenant_id' => $firstTenant->id,
            'email' => 'tech3d@demanda3d.com',
            'password' => $password,
            'display_name' => 'Tech3D Soluções Ltda',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $makeEncr('Tech3D')['encrypted'],
            'first_name_hash' => $makeEncr('Tech3D')['hash'],
            'last_name_encrypted' => $makeEncr('Soluções Ltda')['encrypted'],
            'last_name_hash' => $makeEncr('Soluções Ltda')['hash'],
            'doc_encrypted' => $makeEncr('12.345.678/0001-90')['encrypted'],
            'doc_hash' => $makeEncr('12.345.678/0001-90')['hash'],
            'address_encrypted' => $makeEncr('Av. Paulista')['encrypted'],
            'address_hash' => $makeEncr('Av. Paulista')['hash'],
            'number_encrypted' => $makeEncr('1000')['encrypted'],
            'number_hash' => $makeEncr('1000')['hash'],
            'state_encrypted' => $makeEncr('SP')['encrypted'],
            'state_hash' => $makeEncr('SP')['hash'],
            'zipcode_encrypted' => $makeEncr('01310-100')['encrypted'],
            'zipcode_hash' => $makeEncr('01310-100')['hash'],
            'city_encrypted' => $makeEncr('São Paulo')['encrypted'],
            'city_hash' => $makeEncr('São Paulo')['hash'],
            'phone1_encrypted' => $makeEncr('(11) 99999-0001')['encrypted'],
            'phone1_hash' => $makeEncr('(11) 99999-0001')['hash'],
            'phone2_encrypted' => $makeEncr('(11) 3333-0001')['encrypted'],
            'phone2_hash' => $makeEncr('(11) 3333-0001')['hash'],
            'contact1_encrypted' => $makeEncr('Carlos Silva')['encrypted'],
            'contact1_hash' => $makeEncr('Carlos Silva')['hash'],
        ]);

        // Client 2 - CNPJ
        Client::factory()->create([
            'tenant_id' => $firstTenant->id,
            'email' => 'prototipagem@demanda3d.com',
            'password' => $password,
            'display_name' => 'Prototipagem Rápida S.A.',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $makeEncr('Prototipagem')['encrypted'],
            'first_name_hash' => $makeEncr('Prototipagem')['hash'],
            'last_name_encrypted' => $makeEncr('Rápida S.A.')['encrypted'],
            'last_name_hash' => $makeEncr('Rápida S.A.')['hash'],
            'doc_encrypted' => $makeEncr('98.765.432/0001-10')['encrypted'],
            'doc_hash' => $makeEncr('98.765.432/0001-10')['hash'],
            'address_encrypted' => $makeEncr('Rua da Assembleia')['encrypted'],
            'address_hash' => $makeEncr('Rua da Assembleia')['hash'],
            'number_encrypted' => $makeEncr('500')['encrypted'],
            'number_hash' => $makeEncr('500')['hash'],
            'state_encrypted' => $makeEncr('RJ')['encrypted'],
            'state_hash' => $makeEncr('RJ')['hash'],
            'zipcode_encrypted' => $makeEncr('20011-000')['encrypted'],
            'zipcode_hash' => $makeEncr('20011-000')['hash'],
            'city_encrypted' => $makeEncr('Rio de Janeiro')['encrypted'],
            'city_hash' => $makeEncr('Rio de Janeiro')['hash'],
            'phone1_encrypted' => $makeEncr('(21) 98888-0002')['encrypted'],
            'phone1_hash' => $makeEncr('(21) 98888-0002')['hash'],
            'phone2_encrypted' => $makeEncr('(21) 3444-0002')['encrypted'],
            'phone2_hash' => $makeEncr('(21) 3444-0002')['hash'],
            'contact1_encrypted' => $makeEncr('Ana Oliveira')['encrypted'],
            'contact1_hash' => $makeEncr('Ana Oliveira')['hash'],
            'contact2_encrypted' => $makeEncr('Pedro Santos')['encrypted'],
            'contact2_hash' => $makeEncr('Pedro Santos')['hash'],
        ]);

        // Client 3 - CNPJ
        Client::factory()->create([
            'tenant_id' => $firstTenant->id,
            'email' => 'industria@demanda3d.com',
            'password' => $password,
            'display_name' => 'Indústria Criativa Maker',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $makeEncr('Indústria')['encrypted'],
            'first_name_hash' => $makeEncr('Indústria')['hash'],
            'last_name_encrypted' => $makeEncr('Criativa Maker')['encrypted'],
            'last_name_hash' => $makeEncr('Criativa Maker')['hash'],
            'doc_encrypted' => $makeEncr('45.678.901/0001-23')['encrypted'],
            'doc_hash' => $makeEncr('45.678.901/0001-23')['hash'],
            'address_encrypted' => $makeEncr('Rua XV de Novembro')['encrypted'],
            'address_hash' => $makeEncr('Rua XV de Novembro')['hash'],
            'number_encrypted' => $makeEncr('250')['encrypted'],
            'number_hash' => $makeEncr('250')['hash'],
            'state_encrypted' => $makeEncr('SC')['encrypted'],
            'state_hash' => $makeEncr('SC')['hash'],
            'zipcode_encrypted' => $makeEncr('88010-000')['encrypted'],
            'zipcode_hash' => $makeEncr('88010-000')['hash'],
            'city_encrypted' => $makeEncr('Florianópolis')['encrypted'],
            'city_hash' => $makeEncr('Florianópolis')['hash'],
            'phone1_encrypted' => $makeEncr('(48) 97777-0003')['encrypted'],
            'phone1_hash' => $makeEncr('(48) 97777-0003')['hash'],
            'contact1_encrypted' => $makeEncr('Mariana Costa')['encrypted'],
            'contact1_hash' => $makeEncr('Mariana Costa')['hash'],
        ]);

        // Distribuir 2 clientes aleatórios para cada tenant (incluindo o primeiro já usado)
        foreach ($tenants as $tenant) {
            Client::factory()->count(2)->create([
                'tenant_id' => $tenant->id,
            ]);
        }

        $total = Client::count();
        $this->command->info("✓ {$total} clientes criados com sucesso (distribuídos entre todos os tenants).");
    }
}