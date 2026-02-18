<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | 0️⃣ Excluir rutas públicas (login, logout, etc.)
        |--------------------------------------------------------------------------
        | Esto evita loops de redirección cuando no hay tenant activo
        */

        if (
            $request->routeIs('login') ||
            $request->routeIs('logout') ||
            $request->routeIs('password.*') ||
            $request->is('login') ||
            $request->is('logout')
        ) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Tenant desde sesión (WEB)
        |--------------------------------------------------------------------------
        */

        $tenantId = session('user_payload.tenant_id');

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Fallback desde JWT (API → future-proof)
        |--------------------------------------------------------------------------
        */

        if (!$tenantId) {
            $tenantId = $request->attributes->get('tenant_id');
        }

        $isSuper = session('user_payload.is_superadmin')
            ?? $request->attributes->get('is_superadmin', false);

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Validación
        |--------------------------------------------------------------------------
        */

        if (!$tenantId && !$isSuper) {

            // Solo cerrar sesión si estaba autenticado
            if (auth()->check()) {
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
            }

            return redirect('/login');
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Registrar tenant en container
        |--------------------------------------------------------------------------
        */

        app()->instance('tenant_id', $tenantId);

        return $next($request);
    }
}

