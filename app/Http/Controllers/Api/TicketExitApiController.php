<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketExitApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:64',
            'payment_code' => 'required|in:cash,card,transfer',
        ]);

        // 🔐 usuario real desde JWT
        $user = $request->get('auth_user');

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {
            $result = DB::select(
                'CALL sp_register_ticket_exit(?, ?, ?, ?)',
                [
                    $user->tenant_id,
                    $request->token,
                    $request->payment_code,
                    $user->id
                ]
            );

            DB::statement('SELECT 1'); // fuerza cierre

            if (empty($result)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo obtener recibo'
                ], 500);
            }

            //$row = $result[0];
            $row = $result[0] ?? null;

            if (!$row || !isset($row->folio)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error interno generando recibo'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'receipt' => [
                    'folio' => $row->folio,
                    'exit_time' => $row->exit_time,
                    'minutes' => $row->minutes,
                    'charged_hours' => $row->charged_hours,
                    'total_amount' => $row->total_amount,
                    'payment_code' => $row->payment_code,
                ],
            ], 200);
        } catch (QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $driverMessage = $e->errorInfo[2] ?? $e->getMessage();

            if ($sqlState === '45000') {
                return response()->json([
                    'status' => 'error',
                    'message' => $driverMessage,
                ], 409);
            }

            Log::error('Ticket exit failed', [
                'sqlstate' => $sqlState,
                'message' => $driverMessage
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible registrar la salida',
            ], 400);

        } catch (\Throwable $e) {

            Log::critical('Ticket exit fatal error', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible registrar la salida',
            ], 500);
        }
    }

}

