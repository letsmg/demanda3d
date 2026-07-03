<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginCarrierController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/LoginCarrier');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email']    = trim(strip_tags($credentials['email']));
        $credentials['password'] = trim(strip_tags($credentials['password']));

        // Login manual para evitar TenantScope no momento da busca
        $carrier = Carrier::withoutGlobalScopes()->where('email', $credentials['email'])->first();

        if ($carrier && Hash::check($credentials['password'], $carrier->password)) {
            Auth::guard('carriers')->login($carrier, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/carrier/dashboard');
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('carriers')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login_carrier');
    }
}