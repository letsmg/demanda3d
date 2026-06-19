<?php

namespace Database\Factories;

use App\Models\Client;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $doc = fake()->numerify('##.###.###/####-##');
        $phone1 = fake()->phoneNumber();

        $docResult = EncryptionService::encryptWithHash($doc);
        $phone1Result = EncryptionService::encryptWithHash($phone1);

        return [
            'tenant_id' => TenantFactory::new()->create()->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $firstName . ' ' . $lastName,
            'doc' => $doc,
            'doc_encrypted' => $docResult['encrypted'],
            'doc_hash' => $docResult['hash'],
            'address' => fake()->streetAddress(),
            'number' => fake()->buildingNumber(),
            'state' => fake()->stateAbbr(),
            'zipcode' => fake()->postcode(),
            'city' => fake()->city(),
            'phone1' => $phone1,
            'phone1_encrypted' => $phone1Result['encrypted'],
            'phone1_hash' => $phone1Result['hash'],
            'phone2' => fake()->phoneNumber(),
            'contact1' => fake()->name(),
            'contact2' => null,
        ];
    }
}