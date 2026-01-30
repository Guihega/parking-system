<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class TicketCancelApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:64',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $result = DB::select(
                'CALL sp_cancel_ticket(?, ?)',
                [$request->token, $request->reason]
            );

            // REGISTRAR EVENTO DE CANCELACIÓN
/*             DB::select(
                'CALL sp_log_ticket_event(?, ?, ?, ?)',
                [
                    $request->token,
                    'cancel',
                    'Cancelación de ticket',
                    json_encode([
                        'reason' => $request->reason
                    ])
                ]
            ); */

            return response()->json([
                'status' => 'success',
                'ticket' => [
                    'folio' => $result[0]->folio,
                    'status' => $result[0]->status,
                ]
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

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible cancelar el ticket',
            ], 400);
        }
    }
}
