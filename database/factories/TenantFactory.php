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

        return [
            'user_id' => User::factory(),
            'company_name_encrypted' => EncryptionService::encryptWithHash($companyName)['encrypted'],
            'company_name_hash' => EncryptionService::encryptWithHash($companyName)['hash'],
            'fantasy_name' => $fantasyName,
            'fantasy_slug' => Tenant::generateUniqueFantasySlug($fantasyName),
            'document' => $this->faker->numerify('##.###.###/####-##'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'number' => $this->faker->buildingNumber(),
            'district' => $this->faker->citySuffix(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zipcode' => $this->faker->postcode(),
            'active' => true,
        ];
    }
}
