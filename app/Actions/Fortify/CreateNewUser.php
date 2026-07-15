<?php

namespace App\Actions\Fortify;

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;
use App\Services\LegalConsentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user (Vendedor).
     *
     * Apenas e-mail + senha obrigatórios. Demais campos NOT NULL
     * são preenchidos com placeholders. Perfil fica com
     * is_profile_complete = false e active = false.
     */
    public function create(array $input): User
    {
        $input['email'] = trim(strip_tags($input['email'] ?? ''));
        $input['password'] = strip_tags($input['password'] ?? '');

        Validator::make($input, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'accept_terms' => ['required', 'accepted'],
            'accept_privacy' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_terms.accepted' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_privacy.required' => 'Você deve aceitar a Política de Privacidade para se cadastrar.',
            'accept_privacy.accepted' => 'Você deve aceitar a Política de Privacidade para se cadastrar.',
        ])->validate();

        // Gera nome de exibição a partir do e-mail
        $namePrefix = explode('@', $input['email'])[0];
        $displayName = ucfirst($namePrefix);

        // Parse name for parity structure
        $nameParts = explode('.', $namePrefix, 2);
        $firstName = ucfirst($nameParts[0]);
        $lastName = count($nameParts) > 1 ? ucfirst($nameParts[1]) : ucfirst($namePrefix);

        // Garantir que lastName nunca seja vazio (coluna NOT NULL)
        if ($lastName === '') {
            $lastName = $firstName;
        }

        $firstNameData = EncryptionService::encryptWithHash($firstName);
        $lastNameData = EncryptionService::encryptWithHash($lastName);

        $user = DB::transaction(function () use ($input, $displayName, $firstNameData, $lastNameData) {
            $user = User::create([
                'email' => $input['email'],
                'display_name' => $displayName,
                'first_name_encrypted' => $firstNameData['encrypted'],
                'first_name_hash' => $firstNameData['hash'],
                'last_name_encrypted' => $lastNameData['encrypted'],
                'last_name_hash' => $lastNameData['hash'],
                'password' => Hash::make($input['password']),
                'access_level' => UserAccessLevel::SELLER_1,
                'email_verified_at' => now(),
            ]);

            Tenant::create([
                'user_id' => $user->id,
                'fantasy_name' => $displayName,
                'fantasy_slug' => Tenant::generateUniqueFantasySlug($displayName),
                'document_type' => 'cnpj',
                'document' => '00000000000000',
                'phone' => '0000000000',
                'address' => 'A preencher',
                'number' => 'S/N',
                'district' => 'A preencher',
                'city' => 'A preencher',
                'state' => 'SP',
                'zipcode' => '00000000',
                'active' => false,
                'is_profile_complete' => false,
            ]);

            return $user;
        });

        app(LegalConsentService::class)->recordBothAccepted(request(), userId: $user->id);

        return $user;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.