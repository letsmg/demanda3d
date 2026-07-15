<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Auth;

use App\Enums\UserAccessLevel;
use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RegisterCarrierController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/RegisterCarrier');
    }

    /**
     * Autocadastro de transportadora — apenas e-mail + senha.
     * Demais campos preenchidos com placeholders.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'accept_terms' => ['required', 'accepted'],
            'accept_privacy' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_terms.accepted' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_privacy.required' => 'Você deve aceitar a Política de Privacidade para se cadastrar.',
            'accept_privacy.accepted' => 'Você deve aceitar a Política de Privacidade para se cadastrar.',
        ]);

        $email = trim(strip_tags($validated['email']));
        // Gera um nome fantasia a partir do e-mail
        $namePrefix = explode('@', $email)[0];
        $fantasyName = ucfirst($namePrefix) . ' Transportes';
        $companyName = $fantasyName . ' Ltda';

        // Placeholders LGPD
        $legalData = EncryptionService::encryptWithHash($companyName);
        $firstNameData = EncryptionService::encryptWithHash($fantasyName);
        $lastNameData = EncryptionService::encryptWithHash('Transportes');
        $addrPlaceholder = Crypt::encryptString('A preencher');
        $docPlaceholder = EncryptionService::encryptWithHash('00000000000000');

        DB::transaction(function () use ($validated, $email, $fantasyName, $firstNameData, $lastNameData, $addrPlaceholder, $docPlaceholder) {
            $user = User::create([
                'email' => $email,
                'display_name' => $fantasyName,
                'first_name_encrypted' => $firstNameData['encrypted'],
                'first_name_hash' => $firstNameData['hash'],
                'last_name_encrypted' => $lastNameData['encrypted'],
                'last_name_hash' => $lastNameData['hash'],
                'password' => $validated['password'],
                'access_level' => UserAccessLevel::CARRIER_1,
                'email_verified_at' => now(),
            ]);

            Carrier::create([
                'user_id' => $user->id,
                'fantasy_name' => $fantasyName,
                'slug' => Carrier::generateUniqueSlug($fantasyName),
                'company_name_encrypted' => $legalData['encrypted'],
                'company_name_hash' => $legalData['hash'],
                'document_type' => 'cnpj',
                'document_encrypted' => $docPlaceholder['encrypted'],
                'document_hash' => $docPlaceholder['hash'],
                'address_encrypted' => $addrPlaceholder,
                'phone_encrypted' => Crypt::encryptString('A preencher'),
                'is_active' => true,
                'is_profile_complete' => false,
            ]);
        });

        return redirect()->route('login.carrier')
            ->with('success', 'Cadastro realizado com sucesso! Faça login para completar seu perfil.');
    }
}