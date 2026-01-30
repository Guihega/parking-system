<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketReceiptApiController extends Controller
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
            $result = DB::select('CALL sp_get_ticket_receipt(?)', [$token]);

            // REGISTRAR REIMPRESIÓN
/*             DB::select(
                'CALL sp_log_ticket_event(?, ?, ?, ?)',
                [
                    $token,
                    'reprint',
                    'Reimpresión de comprobante',
                    json_encode([
                        'reason' => 'Reimpresión solicitada'
                    ])
                ]
            ); */

            return response()->json([
                'status' => 'success',
                'receipt' => [
                    'folio' => $result[0]->folio,
                    'plate' => $result[0]->plate,
                    'entry_time' => $result[0]->entry_time,
                    'exit_time' => $result[0]->exit_time,
                    'minutes' => $result[0]->minutes,
                    'total_amount' => $result[0]->total_amount,
                    'payment_code' => $result[0]->payment_code,
                    'payment_name' => $result[0]->payment_name,
                    'cash_session_id' => $result[0]->cash_session_id,
                    'branch' => $result[0]->branch_name,
                    'parking_space' => $result[0]->parking_space,
                ],
                'print' => [
                    'qr_text' => $token
                ]
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Ticket receipt failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener el comprobante'
            ], 500);
        }
    }
}
