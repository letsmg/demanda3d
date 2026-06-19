<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company(),
            'fantasy_name' => $this->faker->company(),
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