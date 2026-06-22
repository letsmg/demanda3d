<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect user based on role after login.
     */
    public function toResponse($request): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        if ($user && $user->role === 'customer') {
            return redirect()->intended('/store');
        }

        return redirect()->intended('/dashboard');
    }
}