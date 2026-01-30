<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Middleware\JwtAuthMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\ValidateJwtTokenMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\EnsureCashSessionState;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cookie\Middleware\EncryptCookies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🔐 Cookie encryption (excluir JWT)
        $middleware->append(EncryptCookies::class);

        $middleware->encryptCookies(except: [
            'jwt_token',
        ]);

        $middleware->alias([
            'jwt'            => JwtAuthMiddleware::class,
            'perm'           => PermissionMiddleware::class,
            'vjwt'           => ValidateJwtTokenMiddleware::class,
            'secure.headers' => SecurityHeadersMiddleware::class,
            'cash.session' => EnsureCashSessionState::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->appendToGroup('api', \Illuminate\Http\Middleware\HandleCors::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        /*
        |--------------------------------------------------------------------------
        | 422 — Validaciones
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        });

        /*
        |--------------------------------------------------------------------------
        | 4xx — Excepciones HTTP controladas
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (HttpException $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'HTTP_ERROR',
                'message' => $e->getMessage() ?: 'Solicitud inválida',
            ], $e->getStatusCode());
        });

        /*
        |--------------------------------------------------------------------------
        | 500 — Error interno (fallback final)
        |--------------------------------------------------------------------------
        */
        $exceptions->render(function (Throwable $e, $request) {

            // 🔐 Autenticación (WEB)
            if ($e instanceof AuthenticationException) {

                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'status'  => 'error',
                        'code'    => 'UNAUTHENTICATED',
                        'message' => 'No autenticado',
                    ], 401);
                }

                // 👈 REDIRECT correcto para web
                return redirect()->guest(route('login'));
            }

            // 🌐 API: error genérico
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 'INTERNAL_ERROR',
                    'message' => 'Error interno del servidor',
                ], 500);
            }

            // 🖥 Web: deja que Laravel maneje
            throw $e;
        });


    })
    ->create();
