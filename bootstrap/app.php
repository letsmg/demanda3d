<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\CheckAccessLevel;
use App\Http\Middleware\CheckAgeRequirement;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\StaffOnly;
use App\Http\Middleware\VerifyUserExists;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'access.level' => CheckAccessLevel::class,
            'admin.only' => AdminOnly::class,
            'staff.only' => StaffOnly::class,
            'partner.only' => StaffOnly::class,
            'redirect_if_authenticated' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'ensure.staff' => \App\Http\Middleware\EnsureStaff::class,
            'check.age' => CheckAgeRequirement::class,
            'verify.user.exists' => VerifyUserExists::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Defesa em profundidade: converte UniqueConstraintViolationException
        // em mensagem amigável. A validação nas Requests DEVE impedir que
        // isso ocorra, mas este handler garante que o usuário nunca veja um
        // erro 500 de banco de dados.
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Este registro já existe. Verifique os dados e tente novamente.',
                ], 409); // 409 Conflict
            }

            return back()
                ->withInput()
                ->with('error', 'Este registro já existe. Verifique os dados e tente novamente.');
        });
    })->create();
