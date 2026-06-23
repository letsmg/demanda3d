<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisterClientController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/RegisterClient');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['display_name'] = trim(strip_tags($validated['display_name']));
        $validated['email'] = trim(strip_tags($validated['email']));

        // Parse name parts for parity structure
        $nameParts = explode(' ', $validated['display_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $firstNameData = EncryptionService::encryptWithHash($firstName);
        $lastNameData = EncryptionService::encryptWithHash($lastName);

        // Assign to first available tenant (or create a default one)
        $tenant = Tenant::first();
        if (! $tenant) {
            $tenant = Tenant::create([
                'display_name' => 'Cliente ' . $validated['display_name'],
                'slug' => Str::slug('cliente-' . $validated['display_name']),
            ]);
        }

        $client = Client::create([
            'tenant_id' => $tenant->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'display_name' => $validated['display_name'],
            'doc_type' => 'CPF',
            'first_name_encrypted' => $firstNameData['encrypted'],
            'first_name_hash' => $firstNameData['hash'],
            'last_name_encrypted' => $lastNameData['encrypted'],
            'last_name_hash' => $lastNameData['hash'],
        ]);

        // Auto-login the client
        \Illuminate\Support\Facades\Auth::guard('clients')->login($client);

        return redirect('/store');
    }
}