<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | Obtener token (Header o Cookie)
        |--------------------------------------------------------------------------
        */

        $token = $request->bearerToken()
              ?? $request->cookie('jwt_token');

        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Token no proporcionado'
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar JWT
        |--------------------------------------------------------------------------
        */

        try {

            $decoded = JWT::decode(
                $token,
                new Key(config('jwt.secret'), 'HS256')
            );

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'TOKEN_EXPIRED',
                'message' => 'Token expirado'
            ], 401);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 'error',
                'code'    => 'INVALID_TOKEN',
                'message' => 'Token inválido'
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar usuario
        |--------------------------------------------------------------------------
        */

        $user = User::find($decoded->sub);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'USER_NOT_FOUND',
                'message' => 'Usuario no encontrado'
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Inyectar usuario autenticado en request
        |--------------------------------------------------------------------------
        */

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
