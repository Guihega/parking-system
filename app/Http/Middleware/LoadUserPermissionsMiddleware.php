<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoadUserPermissionsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ❌ No correr en API
        if ($request->is('api/*')) {
            return $next($request);
        }

        if (auth()->check()) {
            $permissions = auth()->user()
                ->roles
                ->flatMap(fn ($role) => $role->permissions)
                ->pluck('code')
                ->unique()
                ->values()
                ->toArray();

            session(['permissions' => $permissions]);
        }

        return $next($request);
    }
}
