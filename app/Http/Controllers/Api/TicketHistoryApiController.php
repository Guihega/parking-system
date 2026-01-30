<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketHistoryApiController extends Controller
{
    public function show(string $token)
    {
        if (strlen($token) !== 64) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido'
            ], 422);
        }

        try {
            $result = DB::select(
                'CALL sp_get_ticket_history(?)',
                [$token]
            );

            return response()->json([
                'status' => 'success',
                'history' => $result
            ], 200);

        } catch (QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $driverMessage = $e->errorInfo[2] ?? $e->getMessage();

            if ($sqlState === '45000') {
                return response()->json([
                    'status' => 'error',
                    'message' => $driverMessage,
                ], 404);
            }

            Log::error('Ticket history failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener el historial',
            ], 400);

        } catch (\Throwable $e) {

            Log::error('Ticket history failed (throwable)', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }
}
