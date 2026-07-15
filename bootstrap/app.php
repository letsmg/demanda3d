<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\CheckAccessLevel;
use App\Http\Middleware\CheckAgeRequirement;
use App\Http\Middleware\CheckLegalConsent;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\StaffOnly;
use App\Http\Middleware\VerifyUserExists;
use App\Jobs\SendCriticalErrorAlert;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            CheckLegalConsent::class,
        ]);

        $middleware->alias([
            'access.level'              => CheckAccessLevel::class,
            'admin.only'                => AdminOnly::class,
            'staff.only'                => StaffOnly::class,
            'partner.only'              => StaffOnly::class,
            'redirect_if_authenticated' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'ensure.staff'              => \App\Http\Middleware\EnsureStaff::class,
            'check.age'                 => CheckAgeRequirement::class,
            'verify.user.exists'        => VerifyUserExists::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // Defesa em profundidade: converte UniqueConstraintViolationException
        // em mensagem amigável.
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Este registro já existe. Verifique os dados e tente novamente.',
                ], 409);
            }

            return back()
                ->withInput()
                ->with('error', 'Este registro já existe. Verifique os dados e tente novamente.');
        });

        // ─────────────────────────────────────────────
        // Tratamento de indisponibilidade de infra
        // (Database offline).
        // ─────────────────────────────────────────────
        $exceptions->render(function (QueryException $e, Request $request) {
            $connectionCodes = ['08006', '08001', '08004', '57P01', '53300'];

            $code     = $e->getCode();
            $sqlState = is_string($code) ? $code : str_pad((string) $code, 5, '0', STR_PAD_LEFT);

            if (! in_array($sqlState, $connectionCodes, true)) {
                return null; // não é erro de conexão
            }

            Log::channel('single')->critical('Database offline', [
                'alert'    => 'database_down',
                'message'  => $e->getMessage(),
                'hostname' => gethostname(),
                'time'     => now()->toIso8601String(),
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Serviço temporariamente indisponível. Tente novamente em instantes.',
                ], 503);
            }

            return response()->view('errors.maintenance', [
                'service' => 'database',
            ], 503);
        });

        // ─────────────────────────────────────────────
        // Tratamento de indisponibilidade do Redis
        // (ext-redis nativa). As mensagens de erro da
        // extensão são inspecionadas para detectar
        // falhas de conexão.
        // ─────────────────────────────────────────────
        $exceptions->report(function (Throwable $e) {
            $message = $e->getMessage();

            $redisPatterns = [
                'Redis server went away',
                'Connection refused',
                'connect() failed',
                'Redis::connect()',
                'read error on connection',
                'NOAUTH Authentication required',
            ];

            foreach ($redisPatterns as $pattern) {
                if (str_contains($message, $pattern)) {
                    Log::channel('single')->critical('Redis offline', [
                        'alert'    => 'redis_down',
                        'message'  => $message,
                        'hostname' => gethostname(),
                        'time'     => now()->toIso8601String(),
                    ]);

                    break;
                }
            }
        });

        // ─────────────────────────────────────────────
        // Disparo de alerta de erro crítico via fila
        // Redis em ambiente de desenvolvimento.
        //
        // Em produção, o alerta é delegado ao Grafana
        // Alerting e/ou Sentry — este handler apenas
        // loga sem enfileirar jobs.
        // ─────────────────────────────────────────────
        $exceptions->report(function (Throwable $e) {
            if (! app()->environment('local')) {
                return;
            }

            // Evita loop infinito: não enfileira alertas
            // para erros de conexão com Redis (a fila não
            // funcionaria de qualquer forma).
            $skipPatterns = [
                'Redis server went away',
                'Connection refused',
                'connect() failed',
                'NOAUTH Authentication required',
            ];

            foreach ($skipPatterns as $pattern) {
                if (str_contains($e->getMessage(), $pattern)) {
                    return;
                }
            }

            SendCriticalErrorAlert::dispatch(
                errorMessage: $e->getMessage(),
                file: $e->getFile(),
                line: $e->getLine(),
                environment: app()->environment(),
                timestamp: now()->toIso8601String(),
                context: [
                    'class' => $e::class,
                    'trace' => collect($e->getTrace())->take(5)->toArray(),
                ],
            );
        });
    })->create();
