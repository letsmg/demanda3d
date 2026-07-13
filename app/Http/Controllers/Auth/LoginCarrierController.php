<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Auth;

use App\Enums\UserAccessLevel;
use App\Http\Controllers\Controller;
use App\Models\User;
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

    /**
     * Autentica transportadores pelo guard 'carriers' (provider: users).
     *
     * Só permite login de usuários com access_level CARRIER_1 ou CARRIER_2.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email']    = trim(strip_tags($credentials['email']));
        $credentials['password'] = trim(strip_tags($credentials['password']));

        // Busca o User — o guard carriers agora usa o provider users
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas.',
            ])->onlyInput('email');
        }

        // Apenas transportadores (CARRIER_1 ou CARRIER_2) podem logar aqui
        if (! $user->isCarrier()) {
            return back()->withErrors([
                'email' => 'Acesso restrito a transportadoras.',
            ])->onlyInput('email');
        }

        Auth::guard('carriers')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/carrier/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::guard('carriers')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login_carrier');
    }
}