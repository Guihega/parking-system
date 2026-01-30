<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureCashSessionState
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $hasOpen = DB::table('cash_sessions')
            ->where('user_id', auth()->id())
            ->where('is_open', 1)
            ->exists();

        $route = $request->route()?->getName();

        $cashRoutes = [
            'cash.open',
            'cash.store'
        ];

        $operationalRoutes = [
            'parking.select.space',
            'parking.checkout',
            'tickets.entry'
        ];

        // No hay caja y quiere operar
        if (!$hasOpen && in_array($route, $operationalRoutes)) {
            return redirect()->route('cash.open');
        }

        // Ya hay caja y quiere abrir otra
        if ($hasOpen && in_array($route, $cashRoutes)) {
            return redirect()->route('parking.select.space');
        }

        return $next($request);
    }
}
