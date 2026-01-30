<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class ValidateJwtTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | Obtener token (HEADER o COOKIE)
        |--------------------------------------------------------------------------
        */

        $token = $request->bearerToken()
              ?? $request->cookie('jwt_token');

        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Usuario no autenticado'
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
        | Usuario + permisos reales (fuente de verdad)
        |--------------------------------------------------------------------------
        */

        $user = User::with('roles.permissions')
            ->where('id', $decoded->sub)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'USER_DISABLED',
                'message' => 'Usuario desactivado'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Construir permisos desde DB (no JWT)
        |--------------------------------------------------------------------------
        */

        $permissions = $user->roles
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('code')
            ->unique()
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Inyectar contexto auth
        |--------------------------------------------------------------------------
        */

        $request->attributes->set('auth_user', $user);
        $request->attributes->set('jwt_permissions', $permissions);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
