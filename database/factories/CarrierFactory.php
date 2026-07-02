<?php

namespace Database\Factories;

use App\Models\Carrier;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition(): array
    {
        $doc = fake()->numerify('##.###.###/0001-##');
        $docData = EncryptionService::encryptWithHash($doc);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company() . ' Transportes',
            'doc_type' => 'CNPJ',
            'document_hash' => $docData['hash'],
            'document_encrypted' => $docData['encrypted'],
        ];
    }
}