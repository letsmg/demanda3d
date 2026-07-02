<?php

namespace App\Actions\Fortify;

use App\Enums\UserAccessLevel;
use App\Models\User;
use App\Services\EncryptionService;
use App\Services\LegalConsentService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     */
    public function create(array $input): User
    {
        $input['name'] = trim(strip_tags($input['name'] ?? ''));
        $input['email'] = trim(strip_tags($input['email'] ?? ''));
        $input['password'] = strip_tags($input['password'] ?? '');

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
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

        // Parse name for parity structure
        $nameParts = explode(' ', $input['name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $firstNameData = EncryptionService::encryptWithHash($firstName);
        $lastNameData = EncryptionService::encryptWithHash($lastName);

        $user = User::create([
            'email' => $input['email'],
            'display_name' => $input['name'],
            'first_name_encrypted' => $firstNameData['encrypted'],
            'first_name_hash' => $firstNameData['hash'],
            'last_name_encrypted' => $lastNameData['encrypted'],
            'last_name_hash' => $lastNameData['hash'],
            'password' => Hash::make($input['password']),
            'access_level' => UserAccessLevel::MANAGEMENT,
        ]);

        // Registrar consentimentos legais
        app(LegalConsentService::class)->recordBothAccepted(request(), userId: $user->id);

        return $user;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
