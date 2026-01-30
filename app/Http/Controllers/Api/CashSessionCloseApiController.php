<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CashSessionCloseApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cash_session_id' => 'required|integer|exists:cash_sessions,id',
            'user_id' => 'required|integer',
            'real_amount' => 'required|numeric|min:0',
            'observations' => 'nullable|string'
        ]);

        try {

            DB::statement(
                'CALL sp_close_cash_session(?, ?, ?, ?)',
                [
                    $request->cash_session_id,
                    auth()->id(),
                    //$request->user_id,
                    $request->real_amount,
                    $request->observations
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Caja cerrada correctamente'
            ], 200);

        } catch (QueryException $e) {

            Log::error('Cash session close failed', [
                'sql' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible cerrar la caja'
            ], 400);

        } catch (\Throwable $e) {

            Log::error('Cash session close failed (throwable)', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno al cerrar la caja'
            ], 500);
        }
    }
}
