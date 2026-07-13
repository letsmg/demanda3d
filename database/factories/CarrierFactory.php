<?php

namespace Database\Factories;

use App\Enums\UserAccessLevel;
use App\Models\Carrier;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarrierFactory extends Factory
{
    protected $model = Carrier::class;

    public function definition(): array
    {
        $doc    = fake()->numerify('##.###.###/0001-##');
        $docData = EncryptionService::encryptWithHash($doc);
        $name   = fake()->company() . ' Transportes';
        $phone  = fake()->numerify('###########');
        $phoneData = EncryptionService::encryptWithHash($phone);
        $address   = fake()->streetAddress();
        $addressData = EncryptionService::encryptWithHash($address);

        return [
            'fantasy_name'        => $name,
            'slug'                => \App\Models\Carrier::generateUniqueSlug($name),
            'document_type'       => 'cnpj',
            'document_encrypted'  => $docData['encrypted'],
            'document_hash'       => $docData['hash'],
            'address_encrypted'   => $addressData['encrypted'],
            'phone_encrypted'     => $phoneData['encrypted'],
        ];
    }

    public function withUser(array $userAttributes = []): static
    {
        return $this->afterMaking(function (Carrier $carrier) use ($userAttributes) {
            if (! $carrier->user_id) {
                $user = User::factory()->create(array_merge([
                    'access_level' => UserAccessLevel::CARRIER_1,
                    'email'        => fake()->unique()->safeEmail(),
                    'display_name' => $carrier->fantasy_name,
                ], $userAttributes));
                $carrier->user_id = $user->id;
            }
        })->afterCreating(function (Carrier $carrier) use ($userAttributes) {
            if (! $carrier->user_id) {
                $user = User::factory()->create(array_merge([
                    'access_level' => UserAccessLevel::CARRIER_1,
                    'email'        => fake()->unique()->safeEmail(),
                    'display_name' => $carrier->fantasy_name,
                ], $userAttributes));
                $carrier->update(['user_id' => $user->id]);
            }
        });
    }
}