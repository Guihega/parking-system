<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // 1️⃣ Usuario autenticado (inyectado por ValidateJwtTokenMiddleware)
        $user = $request->attributes->get('auth_user');

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'UNAUTHENTICATED',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // 2️⃣ Permisos expuestos desde el JWT
        $permissions = (array) $request->attributes->get('jwt_permissions', []);

        // 3️⃣ Permiso faltante
        if (!in_array($permission, $permissions, true)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 'PERMISSION_DENIED',
                'message' => 'No tienes permiso para esta acción'
            ], 403);
        }

        return $next($request);
    }
}
