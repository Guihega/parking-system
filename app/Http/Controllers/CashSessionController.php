<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashSessionController extends Controller
{
    public function openForm()
    {
        // opcional: validar si ya tiene caja abierta y redirigir
        $hasOpen = DB::table('cash_sessions')
            ->where('user_id', auth()->id())
            ->where('is_open', 1)
            ->exists();

        $branches = DB::table('branches')->get();

        return view('cash.open', compact('branches'));
    }

    public function open(Request $request)
    {

        $request->validate([
            'branch_id' => 'required|integer',
            'opening_amount' => 'nullable|numeric|min:0'
        ]);

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("CALL sp_open_cash_session(?,?,?)");
            $stmt->execute([
                $request->branch_id,
                auth()->id(),
                $request->opening_amount ?? 0
            ]);

            return response()->json([
                'status' => 'success'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function closePreview(Request $request, int $id)
    {
        $tenantId = app('tenant_id');
        $userId   = auth()->id();

        // 1) Validar que la sesión exista, pertenezca al tenant, sea del usuario y esté abierta
        $session = DB::table('cash_sessions')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('is_open', 1)
            ->first();

        if (!$session) {
            // Retornamos HTML porque lo consumirá el fetch y se inyecta al DOM
            return response(
                '<div class="p-4 text-center text-danger fw-bold">No tienes una caja abierta válida para cerrar (o no te pertenece).</div>',
                403
            );
        }

        // 2) Total cobrado (por payments) para esta caja y tenant
        $totalCollected = (float) DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->where('cash_session_id', $id)
            ->sum('amount');

        $openingAmount  = (float) $session->opening_amount;
        $expectedAmount = $openingAmount + $totalCollected;

        // 3) Retornar una vista parcial HTML (modal 2)
        return view('admin.cash_sessions.close_preview', [
            'cashSessionId'  => $session->id,
            'openingAmount'  => $openingAmount,
            'totalCollected' => $totalCollected,
            'expectedAmount' => $expectedAmount,
        ]);
    }

    public function current()
    {
        $tenantId = app('tenant_id');
        $userId   = auth()->id();

        $session = CashSession::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('is_open', 1)
            ->first();

        if ($session) {
            return view('admin.cash_sessions.partials.session-open', compact('session'));
        }

        return view('admin.cash_sessions.partials.session-closed');
    }

}
