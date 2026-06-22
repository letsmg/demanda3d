<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    public function definition(): array
    {
        $companyName = $this->faker->company();
        $fantasyName = $this->faker->company();
        $document = $this->faker->numerify('##.###.###/####-##');
        $phone = $this->faker->phoneNumber();
        $address = $this->faker->streetAddress();
        $number = $this->faker->buildingNumber();
        $district = $this->faker->citySuffix();
        $city = $this->faker->city();

        return [
            'user_id' => User::factory(),
            'company_name_encrypted' => EncryptionService::encryptWithHash($companyName)['encrypted'],
            'company_name_hash' => EncryptionService::encryptWithHash($companyName)['hash'],
            'fantasy_name_encrypted' => EncryptionService::encryptWithHash($fantasyName)['encrypted'],
            'fantasy_name_hash' => EncryptionService::encryptWithHash($fantasyName)['hash'],
            'document_encrypted' => EncryptionService::encryptWithHash($document)['encrypted'],
            'document_hash' => EncryptionService::encryptWithHash($document)['hash'],
            'phone_encrypted' => EncryptionService::encryptWithHash($phone)['encrypted'],
            'phone_hash' => EncryptionService::encryptWithHash($phone)['hash'],
            'address_encrypted' => EncryptionService::encryptWithHash($address)['encrypted'],
            'address_hash' => EncryptionService::encryptWithHash($address)['hash'],
            'number_encrypted' => EncryptionService::encryptWithHash($number)['encrypted'],
            'number_hash' => EncryptionService::encryptWithHash($number)['hash'],
            'district_encrypted' => EncryptionService::encryptWithHash($district)['encrypted'],
            'district_hash' => EncryptionService::encryptWithHash($district)['hash'],
            'city_encrypted' => EncryptionService::encryptWithHash($city)['encrypted'],
            'city_hash' => EncryptionService::encryptWithHash($city)['hash'],
            'state' => $this->faker->stateAbbr(),
            'zipcode' => $this->faker->postcode(),
            'active' => true,
        ];
    }
}