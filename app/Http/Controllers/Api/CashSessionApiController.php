<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CashSessionApiController extends Controller
{
    public function open(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'user_id' => 'nullable|integer',
            'opening_amount' => 'required|numeric'
        ]);

        try {
            $result = DB::select(
                'CALL sp_open_cash_session(?, ?, ?)',
                [$request->branch_id, $request->user_id, $request->opening_amount]
            );

            return response()->json([
                'status' => 'success',
                'cash_session_id' => $result[0]->cash_session_id
            ], 201);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->errorInfo[2] ?? 'Error'
            ], 409);
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
        $user = $request->get('auth_user');

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
