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
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'doc_type'        => ['required', 'in:CNPJ,CPF'],
            'document'        => ['required', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date', 'before:today'],
            'accept_terms'    => ['required', 'accepted'],
        ], [
            'accept_terms.required'  => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'accept_terms.accepted'  => 'Você deve aceitar os Termos de Uso para se cadastrar.',
            'document.required'      => 'O documento (CPF/CNPJ) é obrigatório.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
        ]);

        // Sanitização
        $validated['name']     = trim(strip_tags($validated['name']));
        $validated['email']    = trim(strip_tags($validated['email']));
        $validated['document'] = preg_replace('/[^0-9]/', '', $validated['document']);

        // Paridade LGPD: criptografa documento
        $documentData = EncryptionService::encryptWithHash($validated['document']);

        DB::transaction(function () use ($validated, $documentData) {
            // 1. Cria o User global com nível CARRIER_1 (Transportador Admin)
            $user = User::create([
                'email'        => $validated['email'],
                'display_name' => $validated['name'],
                'password'     => Hash::make($validated['password']),
                'access_level' => UserAccessLevel::CARRIER_1,
                'data_nascimento' => $validated['data_nascimento'] ?? null,
            ]);

            // 2. Cria o perfil Carrier vinculado ao User
            Carrier::create([
                'user_id'            => $user->id,
                'name'               => $validated['name'],
                'doc_type'           => $validated['doc_type'],
                'document_encrypted' => $documentData['encrypted'],
                'document_hash'      => $documentData['hash'],
                'data_nascimento'    => $validated['data_nascimento'] ?? null,
                'is_active'          => true,
            ]);
        });

        return redirect()->route('login.carrier')
            ->with('success', 'Cadastro realizado com sucesso! Faça login para continuar.');
    }
}