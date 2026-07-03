<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterCarrierController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/RegisterCarrier');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:carriers,email'],
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

        $carrier = DB::transaction(function () use ($validated, $documentData) {
            // Busca ou cria tenant
            $tenant = Tenant::firstOrCreate(
                ['slug' => 'carrier'],
                ['display_name' => 'Transportadoras'],
            );

            return Carrier::create([
                'tenant_id'          => $tenant->id,
                'name'               => $validated['name'],
                'email'              => $validated['email'],
                'password'           => Hash::make($validated['password']),
                'doc_type'           => $validated['doc_type'],
                'document_encrypted' => $documentData['encrypted'],
                'document_hash'      => $documentData['hash'],
                'data_nascimento'    => $validated['data_nascimento'] ?? null,
                'is_active'          => true,
            ]);
        });

        Auth::guard('carriers')->login($carrier);

        return redirect()->intended('/carrier/dashboard');
    }
}