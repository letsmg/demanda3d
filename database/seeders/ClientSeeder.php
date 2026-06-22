<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $makeEncrypted = fn ($value) => EncryptionService::encryptWithHash($value);

        // Client 1
        $firstName1 = 'Tech3D';
        $lastName1 = 'Soluções Ltda';
        $r1 = $makeEncrypted($firstName1);
        $r1l = $makeEncrypted($lastName1);
        $r1d = $makeEncrypted('12.345.678/0001-90');
        $r1p = $makeEncrypted('(11) 99999-0001');
        $r1p2 = $makeEncrypted('(11) 3333-0001');
        $r1a = $makeEncrypted('Av. Paulista');
        $r1n = $makeEncrypted('1000');
        $r1s = $makeEncrypted('SP');
        $r1z = $makeEncrypted('01310-100');
        $r1c = $makeEncrypted('São Paulo');
        $r1ct = $makeEncrypted('Carlos Silva');

        Client::factory()->create([
            'display_name' => 'Tech3D Soluções Ltda',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $r1['encrypted'],
            'first_name_hash' => $r1['hash'],
            'last_name_encrypted' => $r1l['encrypted'],
            'last_name_hash' => $r1l['hash'],
            'doc_encrypted' => $r1d['encrypted'],
            'doc_hash' => $r1d['hash'],
            'address_encrypted' => $r1a['encrypted'],
            'address_hash' => $r1a['hash'],
            'number_encrypted' => $r1n['encrypted'],
            'number_hash' => $r1n['hash'],
            'state_encrypted' => $r1s['encrypted'],
            'state_hash' => $r1s['hash'],
            'zipcode_encrypted' => $r1z['encrypted'],
            'zipcode_hash' => $r1z['hash'],
            'city_encrypted' => $r1c['encrypted'],
            'city_hash' => $r1c['hash'],
            'phone1_encrypted' => $r1p['encrypted'],
            'phone1_hash' => $r1p['hash'],
            'phone2_encrypted' => $r1p2['encrypted'],
            'phone2_hash' => $r1p2['hash'],
            'contact1_encrypted' => $r1ct['encrypted'],
            'contact1_hash' => $r1ct['hash'],
        ]);

        // Client 2
        $firstName2 = 'Prototipagem';
        $lastName2 = 'Rápida S.A.';
        $r2 = $makeEncrypted($firstName2);
        $r2l = $makeEncrypted($lastName2);
        $r2d = $makeEncrypted('98.765.432/0001-10');
        $r2p = $makeEncrypted('(21) 98888-0002');
        $r2p2 = $makeEncrypted('(21) 3444-0002');
        $r2a = $makeEncrypted('Rua da Assembleia');
        $r2n = $makeEncrypted('500');
        $r2s = $makeEncrypted('RJ');
        $r2z = $makeEncrypted('20011-000');
        $r2c = $makeEncrypted('Rio de Janeiro');
        $r2ct1 = $makeEncrypted('Ana Oliveira');
        $r2ct2 = $makeEncrypted('Pedro Santos');

        Client::factory()->create([
            'display_name' => 'Prototipagem Rápida S.A.',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $r2['encrypted'],
            'first_name_hash' => $r2['hash'],
            'last_name_encrypted' => $r2l['encrypted'],
            'last_name_hash' => $r2l['hash'],
            'doc_encrypted' => $r2d['encrypted'],
            'doc_hash' => $r2d['hash'],
            'address_encrypted' => $r2a['encrypted'],
            'address_hash' => $r2a['hash'],
            'number_encrypted' => $r2n['encrypted'],
            'number_hash' => $r2n['hash'],
            'state_encrypted' => $r2s['encrypted'],
            'state_hash' => $r2s['hash'],
            'zipcode_encrypted' => $r2z['encrypted'],
            'zipcode_hash' => $r2z['hash'],
            'city_encrypted' => $r2c['encrypted'],
            'city_hash' => $r2c['hash'],
            'phone1_encrypted' => $r2p['encrypted'],
            'phone1_hash' => $r2p['hash'],
            'phone2_encrypted' => $r2p2['encrypted'],
            'phone2_hash' => $r2p2['hash'],
            'contact1_encrypted' => $r2ct1['encrypted'],
            'contact1_hash' => $r2ct1['hash'],
            'contact2_encrypted' => $r2ct2['encrypted'],
            'contact2_hash' => $r2ct2['hash'],
        ]);

        // Client 3
        $firstName3 = 'Indústria';
        $lastName3 = 'Criativa Maker';
        $r3 = $makeEncrypted($firstName3);
        $r3l = $makeEncrypted($lastName3);
        $r3d = $makeEncrypted('45.678.901/0001-23');
        $r3p = $makeEncrypted('(48) 97777-0003');
        $r3a = $makeEncrypted('Rua XV de Novembro');
        $r3n = $makeEncrypted('250');
        $r3s = $makeEncrypted('SC');
        $r3z = $makeEncrypted('88010-000');
        $r3c = $makeEncrypted('Florianópolis');
        $r3ct = $makeEncrypted('Mariana Costa');

        Client::factory()->create([
            'display_name' => 'Indústria Criativa Maker',
            'doc_type' => 'CNPJ',
            'first_name_encrypted' => $r3['encrypted'],
            'first_name_hash' => $r3['hash'],
            'last_name_encrypted' => $r3l['encrypted'],
            'last_name_hash' => $r3l['hash'],
            'doc_encrypted' => $r3d['encrypted'],
            'doc_hash' => $r3d['hash'],
            'address_encrypted' => $r3a['encrypted'],
            'address_hash' => $r3a['hash'],
            'number_encrypted' => $r3n['encrypted'],
            'number_hash' => $r3n['hash'],
            'state_encrypted' => $r3s['encrypted'],
            'state_hash' => $r3s['hash'],
            'zipcode_encrypted' => $r3z['encrypted'],
            'zipcode_hash' => $r3z['hash'],
            'city_encrypted' => $r3c['encrypted'],
            'city_hash' => $r3c['hash'],
            'phone1_encrypted' => $r3p['encrypted'],
            'phone1_hash' => $r3p['hash'],
            'contact1_encrypted' => $r3ct['encrypted'],
            'contact1_hash' => $r3ct['hash'],
        ]);

        Client::factory()->count(7)->create();
    }
}