<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ClientProfileController extends Controller
{
    /**
     * Ensure the client is authenticated or redirect to login.
     */
    private function guardClient(): \App\Models\Client
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            abort(redirect('/login_cli'));
        }
        return $client;
    }

    public function profile()
    {
        $client = $this->guardClient();
        return Inertia::render('Client/Profile', [
            'client' => $client,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $client = $this->guardClient();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email,' . $client->id],
        ]);

        $client->update([
            'display_name' => trim(strip_tags($validated['display_name'])),
            'email' => trim(strip_tags($validated['email'])),
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function addresses()
    {
        $client = $this->guardClient();
        return Inertia::render('Client/Addresses', [
            'client' => $client,
        ]);
    }

    public function updateAddress(Request $request)
    {
        $client = $this->guardClient();

        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:2'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $client->update($validated);

        return back()->with('success', 'Endereço atualizado com sucesso!');
    }
}
