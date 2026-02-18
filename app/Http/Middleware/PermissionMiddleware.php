<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // ✅ primero intenta Laravel session (WEB)
        $user = auth()->user();

        // 🔁 fallback JWT (API)
        if (!$user) {
            $user = $request->attributes->get('auth_user');
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'code' => 'UNAUTHENTICATED',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // obtener permisos reales
        $permissions = $user->roles
            ->flatMap(fn($r) => $r->permissions)
            ->pluck('code')
            ->unique()
            ->toArray();

        if (!in_array($permission, $permissions, true)) {
            return response()->json([
                'status' => 'error',
                'code' => 'PERMISSION_DENIED',
                'message' => 'No tienes permiso para esta acción'
            ], 403);
        }

        return $next($request);
    }
}
