<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginClientController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/LoginClient');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = trim(strip_tags($credentials['email']));
        $credentials['password'] = trim(strip_tags($credentials['password']));

        // Tentativa de login manual para evitar TenantScope
        $client = Client::withoutGlobalScopes()->where('email', $credentials['email'])->first();

        if ($client && Hash::check($credentials['password'], $client->password)) {
            Auth::guard('clients')->login($client, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended('/store');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('clients')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login_cli');
    }
}