<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketInfoApiController extends Controller
{
    public function show(string $token)
    {
        if (strlen($token) !== 64) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido',
            ], 422);
        }

        try {

            $result = DB::select(
                'CALL sp_get_ticket_info(?)',
                [$token]
            );

            if (empty($result)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ticket no encontrado',
                ], 404);
            }

            $row = $result[0];

            return response()->json([
                'status' => 'success',
                'ticket' => [
                    'id' => $row->ticket_id,
                    'folio' => $row->folio,
                    'plate' => $row->plate,
                    'entry_time' => $row->entry_time,
                    'exit_time' => $row->exit_time,
                    'status' => $row->status,
                    'parking_space' => $row->parking_space,
                    'branch_id' => $row->branch_id,
                    'vehicle_type_id' => $row->vehicle_type_id,
                    'minutes_elapsed' => $row->minutes_elapsed,
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

            Log::error('Ticket info failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible consultar el ticket',
            ], 400);

        } catch (\Throwable $e) {

            Log::critical('Ticket info fatal', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible consultar el ticket',
            ], 500);
        }
    }
}
