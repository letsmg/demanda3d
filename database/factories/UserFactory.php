<?php

namespace Database\Factories;

use App\Enums\UserAccessLevel;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        // Resolva a instância do faker explicitamente
        $faker = \Faker\Factory::create();

        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = fake()->unique()->safeEmail();

        $firstNameResult = EncryptionService::encryptWithHash($firstName);
        $lastNameResult = EncryptionService::encryptWithHash($lastName);

        return [
            'email' => $email,
            'display_name' => $firstName . ' ' . $lastName,
            'first_name_encrypted' => $firstNameResult['encrypted'],
            'first_name_hash' => $firstNameResult['hash'],
            'last_name_encrypted' => $lastNameResult['encrypted'],
            'last_name_hash' => $lastNameResult['hash'],
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'access_level' => UserAccessLevel::CUSTOMER,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => UserAccessLevel::ADMIN,
        ]);
    }

    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => UserAccessLevel::PARTNER,
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_level' => UserAccessLevel::CUSTOMER,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static {}
}