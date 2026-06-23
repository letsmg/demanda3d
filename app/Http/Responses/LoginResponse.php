<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect staff users to dashboard after login.
     * Client login is handled separately via LoginClientController (guard clients).
     */
    public function toResponse($request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->intended('/dashboard');
    }
}