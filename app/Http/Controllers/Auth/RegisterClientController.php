<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use App\Services\LegalConsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisterClientController extends Controller
{
    public function __construct(
        private LegalConsentService $consentService,
    ) {}

    public function create()
    {
        return Inertia::render('auth/RegisterClient');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
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

        // Placeholders LGPD: nome de exibição a partir do e-mail
        $displayName = explode('@', $email)[0];
        $nameParts = explode(' ', $displayName, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? $firstName;

        // Garantir que lastName nunca seja vazio (coluna NOT NULL)
        if ($lastName === '') {
            $lastName = $firstName;
        }

        $firstNameData = EncryptionService::encryptWithHash($firstName);
        $lastNameData = EncryptionService::encryptWithHash($lastName);

        // Placeholder de endereço e demais campos NOT NULL
        $addrPlaceholder = EncryptionService::encryptWithHash('A preencher');

        // Assign to first available tenant
        $tenant = Tenant::first();
        if (! $tenant) {
            return back()->with('error', 'Nenhuma loja disponível no momento. Tente novamente mais tarde.');
        }

        $client = Client::create([
            'tenant_id' => $tenant->id,
            'email' => $email,
            'password' => Hash::make($validated['password']),
            'display_name' => $displayName,
            'doc_type' => 'CPF',
            'first_name_encrypted' => $firstNameData['encrypted'],
            'first_name_hash' => $firstNameData['hash'],
            'last_name_encrypted' => $lastNameData['encrypted'],
            'last_name_hash' => $lastNameData['hash'],
            // Address + phone placeholders
            'address_encrypted' => $addrPlaceholder['encrypted'],
            'address_hash' => $addrPlaceholder['hash'],
            'number_encrypted' => $addrPlaceholder['encrypted'],
            'number_hash' => $addrPlaceholder['hash'],
            'zipcode_encrypted' => $addrPlaceholder['encrypted'],
            'zipcode_hash' => $addrPlaceholder['hash'],
            'state_encrypted' => $addrPlaceholder['encrypted'],
            'state_hash' => $addrPlaceholder['hash'],
            'city_encrypted' => $addrPlaceholder['encrypted'],
            'city_hash' => $addrPlaceholder['hash'],
            'phone1_encrypted' => $addrPlaceholder['encrypted'],
            'phone1_hash' => $addrPlaceholder['hash'],
            'doc_encrypted' => $addrPlaceholder['encrypted'],
            'doc_hash' => $addrPlaceholder['hash'],
            'is_profile_complete' => false,
        ]);

        $this->consentService->recordBothAccepted($request, $client->id);

        \Illuminate\Support\Facades\Auth::guard('clients')->login($client);

        return redirect('/store');
    }
}