<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use App\Models\CashSession;

class CashSessionApiController extends Controller
{
    public function open(Request $request)
    {
        try {
            $user = $request->user();
            $tenantId = $request->get('tenant_id');
            $permissions = $request->get('jwt_permissions');

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autenticado'
                ], 401);
            }

            if (!in_array('cash.open', $permissions)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No tienes permiso para abrir caja'
                ], 403);
            }

            $validated = $request->validate([
                'opening_amount' => 'required|numeric|min:0'
            ]);

            $existing = CashSession::where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->where('is_open', 1)
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya tienes una caja abierta'
                ], 422);
            }

            DB::beginTransaction();

            $cashSession = CashSession::create([
                'tenant_id' => $tenantId,
                'branch_id' => $user->branch_id ?? 1, // ajusta si tienes lógica de sucursal
                'user_id' => $user->id,
                'opening_amount' => $validated['opening_amount'],
                'expected_amount' => $validated['opening_amount'],
                'is_open' => 1,
                'opened_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Caja abierta correctamente',
                'data' => [
                    'id' => $cashSession->id,
                    'opening_amount' => $cashSession->opening_amount,
                    'opened_at' => $cashSession->opened_at
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Error al abrir caja', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al abrir la caja'
            ], 500);
        }
    }

    public function close(Request $request)
    {
        $request->validate([
            'cash_session_id' => 'required|integer'
        ]);

        try {
            $result = DB::select(
                'CALL sp_close_cash_session(?)',
                [$request->cash_session_id]
            );

            return response()->json([
                'status' => 'success',
                'total_collected' => $result[0]->total_collected
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errorInfo[2]
            ], 409);
        }
    }

    public function current(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {
            $session = DB::table('cash_sessions')
                ->where('user_id', $user->id)
                ->where('is_open', 1)
                ->first();

            if (!$session) {
                return response()->json([
                    'status' => 'success',
                    'open' => false
                ]);
            }

            return response()->json([
                'status' => 'success',
                'open' => true,
                'session' => $session
            ]);

        } catch (\Throwable $e) {

            \Log::error('Cash current validation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible validar la caja'
            ], 500);
        }
    }
}
