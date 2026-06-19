<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $doc1 = '12.345.678/0001-90';
        $doc1Result = EncryptionService::encryptWithHash($doc1);
        $phone1 = '(11) 99999-0001';
        $phone1Result = EncryptionService::encryptWithHash($phone1);

        Client::factory()->create([
            'first_name' => 'Tech3D',
            'last_name' => 'Soluções Ltda',
            'display_name' => 'Tech3D Soluções Ltda',
            'doc' => $doc1,
            'doc_encrypted' => $doc1Result['encrypted'],
            'doc_hash' => $doc1Result['hash'],
            'address' => 'Av. Paulista',
            'number' => '1000',
            'state' => 'SP',
            'zipcode' => '01310-100',
            'city' => 'São Paulo',
            'phone1' => $phone1,
            'phone1_encrypted' => $phone1Result['encrypted'],
            'phone1_hash' => $phone1Result['hash'],
            'phone2' => '(11) 3333-0001',
            'contact1' => 'Carlos Silva',
        ]);

        $doc2 = '98.765.432/0001-10';
        $doc2Result = EncryptionService::encryptWithHash($doc2);
        $phone2 = '(21) 98888-0002';
        $phone2Result = EncryptionService::encryptWithHash($phone2);

        Client::factory()->create([
            'first_name' => 'Prototipagem',
            'last_name' => 'Rápida S.A.',
            'display_name' => 'Prototipagem Rápida S.A.',
            'doc' => $doc2,
            'doc_encrypted' => $doc2Result['encrypted'],
            'doc_hash' => $doc2Result['hash'],
            'address' => 'Rua da Assembleia',
            'number' => '500',
            'state' => 'RJ',
            'zipcode' => '20011-000',
            'city' => 'Rio de Janeiro',
            'phone1' => $phone2,
            'phone1_encrypted' => $phone2Result['encrypted'],
            'phone1_hash' => $phone2Result['hash'],
            'phone2' => '(21) 3444-0002',
            'contact1' => 'Ana Oliveira',
            'contact2' => 'Pedro Santos',
        ]);

        $doc3 = '45.678.901/0001-23';
        $doc3Result = EncryptionService::encryptWithHash($doc3);
        $phone3 = '(48) 97777-0003';
        $phone3Result = EncryptionService::encryptWithHash($phone3);

        Client::factory()->create([
            'first_name' => 'Indústria',
            'last_name' => 'Criativa Maker',
            'display_name' => 'Indústria Criativa Maker',
            'doc' => $doc3,
            'doc_encrypted' => $doc3Result['encrypted'],
            'doc_hash' => $doc3Result['hash'],
            'address' => 'Rua XV de Novembro',
            'number' => '250',
            'state' => 'SC',
            'zipcode' => '88010-000',
            'city' => 'Florianópolis',
            'phone1' => $phone3,
            'phone1_encrypted' => $phone3Result['encrypted'],
            'phone1_hash' => $phone3Result['hash'],
            'contact1' => 'Mariana Costa',
        ]);

        Client::factory()->count(7)->create();
    }
}