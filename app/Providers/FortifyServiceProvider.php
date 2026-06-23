<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $this->app->singleton(
            \Laravel\Fortify\Contracts\CreatesNewUsers::class,
            \App\Actions\Fortify\CreateNewUser::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::loginView(function () {
            return inertia('auth/Login');
        });

        Fortify::registerView(function () {
            return inertia('auth/Register');
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

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // Dynamic redirect based on user role
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, 
            \App\Http\Responses\LoginResponse::class
        );
    }
}
