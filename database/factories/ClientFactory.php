<?php

namespace Database\Factories;

use App\Models\Client;
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
        return [
            'name' => fake()->company(),
            'doc' => fake()->numerify('##.###.###/####-##'),
            'address' => fake()->streetAddress(),
            'number' => fake()->buildingNumber(),
            'state' => fake()->stateAbbr(),
            'zipcode' => fake()->postcode(),
            'city' => fake()->city(),
            'phone1' => fake()->phoneNumber(),
            'phone2' => fake()->phoneNumber(),
            'contact1' => fake()->name(),
            'contact2' => null,
        ];
    }
}