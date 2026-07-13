<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Auth;

use App\Enums\UserAccessLevel;
use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterCarrierController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/RegisterCarrier');
    }

    /**
     * Autocadastro de transportadora.
     *
     * Cria um User global (access_level = CARRIER_1) e vincula
     * o perfil Carrier via user_id (1:1).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fantasy_name'   => ['required', 'string', 'max:255'],
            'company_name'     => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
            'document_type'  => ['required', 'in:cnpj,cpf'],
            'document'       => ['required', 'string', 'max:20'],
            'phone'          => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:500'],
            'accept_terms'   => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_terms.accepted' => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'document.required'     => 'O documento (CPF/CNPJ) é obrigatório.',
        ]);

        // Sanitização
        $validated['fantasy_name'] = trim(strip_tags($validated['fantasy_name']));
        $validated['company_name']   = trim(strip_tags($validated['company_name']));
        $validated['email']        = trim(strip_tags($validated['email']));
        $validated['document']     = preg_replace('/[^0-9]/', '', $validated['document']);

        // Paridade LGPD: criptografa dados sensíveis
        $documentData = EncryptionService::encryptWithHash($validated['document']);
        $legalData    = EncryptionService::encryptWithHash($validated['company_name']);
        $phoneData    = EncryptionService::encryptWithHash($validated['phone']);
        $addressData  = EncryptionService::encryptWithHash($validated['address']);

        DB::transaction(function () use ($validated, $documentData, $legalData, $phoneData, $addressData) {
            // 1. Cria o User global com nível CARRIER_1 (Transportador Admin)
            $user = User::create([
                'email'        => $validated['email'],
                'display_name' => $validated['fantasy_name'],
                'password'     => $validated['password'],
                'access_level' => UserAccessLevel::CARRIER_1,
            ]);

            // 2. Cria o perfil Carrier vinculado ao User
            Carrier::create([
                'user_id'             => $user->id,
                'fantasy_name'        => $validated['fantasy_name'],
                'slug'                => Carrier::generateUniqueSlug($validated['fantasy_name']),
                'company_name_encrypted'=> $legalData['encrypted'],
                'company_name_hash'     => $legalData['hash'],
                'document_type'       => $validated['document_type'],
                'document_encrypted'  => $documentData['encrypted'],
                'document_hash'       => $documentData['hash'],
                'address_encrypted'   => $addressData['encrypted'],
                'phone_encrypted'     => $phoneData['encrypted'],
                'is_active'           => true,
            ]);
        });

        return redirect()->route('login.carrier')
            ->with('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
    }
}