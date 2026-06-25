<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * Gera um CPF válido com dígitos verificadores corretos.
     */
    public static function validCpf(): string
    {
        $digits = [];
        for ($i = 0; $i < 9; $i++) {
            $digits[] = random_int(0, 9);
        }

        // Primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $digits[$i] * (10 - $i);
        }
        $d1 = $sum % 11;
        $d1 = $d1 < 2 ? 0 : 11 - $d1;
        $digits[] = $d1;

        // Segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $digits[$i] * (11 - $i);
        }
        $d2 = $sum % 11;
        $d2 = $d2 < 2 ? 0 : 11 - $d2;
        $digits[] = $d2;

        $raw = implode('', $digits);
        return substr($raw, 0, 3) . '.' . substr($raw, 3, 3) . '.' . substr($raw, 6, 3) . '-' . substr($raw, 9, 2);
    }

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = $this->faker->unique()->safeEmail();
        $doc = self::validCpf();
        $phone1 = $this->faker->numerify('(##) #####-####');
        $phone2 = $this->faker->numerify('(##) ####-####');
        $address = $this->faker->streetAddress();
        $number = $this->faker->buildingNumber();
        $state = $this->faker->stateAbbr();
        $zipcode = $this->faker->postcode();
        $city = $this->faker->city();
        $contact1 = $this->faker->name();

        return [
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? 1,
            'email' => $email,
            'password' => '$2y$12$LJ3m4ys3Lk0TSwHnbfOMiOXPm1Qlq5JdYcXqKQVJ3w5GzgvZvzRiy', // password
            'display_name' => $firstName . ' ' . $lastName,
            'doc_type' => 'CPF',
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