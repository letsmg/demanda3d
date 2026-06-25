<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $doc = fake()->numerify('##.###.###/0001-##');
        $contact = fake()->phoneNumber() . ' / ' . fake()->safeEmail();
        $docData = EncryptionService::encryptWithHash($doc);
        $contactData = EncryptionService::encryptWithHash($contact);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->company(),
            'document_hash' => $docData['hash'],
            'document_encrypted' => $docData['encrypted'],
            'contact_encrypted' => $contactData['encrypted'],
        ];
    }
}