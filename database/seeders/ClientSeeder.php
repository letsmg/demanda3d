<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->create([
            'name' => 'Tech3D Soluções Ltda',
            'doc' => '12.345.678/0001-90',
            'address' => 'Av. Paulista',
            'number' => '1000',
            'state' => 'SP',
            'zipcode' => '01310-100',
            'city' => 'São Paulo',
            'phone1' => '(11) 99999-0001',
            'phone2' => '(11) 3333-0001',
            'contact1' => 'Carlos Silva',
        ]);

        Client::factory()->create([
            'name' => 'Prototipagem Rápida S.A.',
            'doc' => '98.765.432/0001-10',
            'address' => 'Rua da Assembleia',
            'number' => '500',
            'state' => 'RJ',
            'zipcode' => '20011-000',
            'city' => 'Rio de Janeiro',
            'phone1' => '(21) 98888-0002',
            'phone2' => '(21) 3444-0002',
            'contact1' => 'Ana Oliveira',
            'contact2' => 'Pedro Santos',
        ]);

        Client::factory()->create([
            'name' => 'Indústria Criativa Maker',
            'doc' => '45.678.901/0001-23',
            'address' => 'Rua XV de Novembro',
            'number' => '250',
            'state' => 'SC',
            'zipcode' => '88010-000',
            'city' => 'Florianópolis',
            'phone1' => '(48) 97777-0003',
            'contact1' => 'Mariana Costa',
        ]);

        Client::factory()->count(7)->create();
    }
}