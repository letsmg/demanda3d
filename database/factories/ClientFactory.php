<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Client;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $doc = $this->faker->numerify('##.###.###/####-##');
        $phone1 = $this->faker->phoneNumber();
        $phone2 = $this->faker->phoneNumber();
        $address = $this->faker->streetAddress();
        $number = $this->faker->buildingNumber();
        $state = $this->faker->stateAbbr();
        $zipcode = $this->faker->postcode();
        $city = $this->faker->city();
        $contact1 = $this->faker->name();

        return [
            'tenant_id' => TenantFactory::new()->create()->id,
            'display_name' => $firstName . ' ' . $lastName,
            'doc_type' => DocumentType::detect($doc)->value,
            'first_name_encrypted' => EncryptionService::encryptWithHash($firstName)['encrypted'],
            'first_name_hash' => EncryptionService::encryptWithHash($firstName)['hash'],
            'last_name_encrypted' => EncryptionService::encryptWithHash($lastName)['encrypted'],
            'last_name_hash' => EncryptionService::encryptWithHash($lastName)['hash'],
            'doc_encrypted' => EncryptionService::encryptWithHash($doc)['encrypted'],
            'doc_hash' => EncryptionService::encryptWithHash($doc)['hash'],
            'address_encrypted' => EncryptionService::encryptWithHash($address)['encrypted'],
            'address_hash' => EncryptionService::encryptWithHash($address)['hash'],
            'number_encrypted' => EncryptionService::encryptWithHash($number)['encrypted'],
            'number_hash' => EncryptionService::encryptWithHash($number)['hash'],
            'state_encrypted' => EncryptionService::encryptWithHash($state)['encrypted'],
            'state_hash' => EncryptionService::encryptWithHash($state)['hash'],
            'zipcode_encrypted' => EncryptionService::encryptWithHash($zipcode)['encrypted'],
            'zipcode_hash' => EncryptionService::encryptWithHash($zipcode)['hash'],
            'city_encrypted' => EncryptionService::encryptWithHash($city)['encrypted'],
            'city_hash' => EncryptionService::encryptWithHash($city)['hash'],
            'phone1_encrypted' => EncryptionService::encryptWithHash($phone1)['encrypted'],
            'phone1_hash' => EncryptionService::encryptWithHash($phone1)['hash'],
            'phone2_encrypted' => EncryptionService::encryptWithHash($phone2)['encrypted'],
            'phone2_hash' => EncryptionService::encryptWithHash($phone2)['hash'],
            'contact1_encrypted' => EncryptionService::encryptWithHash($contact1)['encrypted'],
            'contact1_hash' => EncryptionService::encryptWithHash($contact1)['hash'],
            'contact2_encrypted' => null,
            'contact2_hash' => null,
        ];
    }
}