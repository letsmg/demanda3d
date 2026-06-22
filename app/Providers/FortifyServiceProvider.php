<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(function () {
            return inertia('auth/Login');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return inertia('auth/ForgotPassword');
        });

        Fortify::resetPasswordView(function (Request $request) {
            return inertia('auth/ResetPassword', [
                'token' => $request->token,
                'email' => $request->email,
            ]);
        });

        Fortify::confirmPasswordView(function () {
            return inertia('auth/ConfirmPassword');
        });

        Fortify::twoFactorChallengeView(function () {
            return inertia('auth/TwoFactorChallenge');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });
    }
}