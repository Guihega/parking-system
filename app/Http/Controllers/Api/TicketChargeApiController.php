<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketChargeApiController extends Controller
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
            $result = DB::select('CALL sp_get_ticket_charge_v2(?)', [$token]);

            $charge = $result[0];

            return response()->json([
                'status' => 'success',
                'ticket' => [
                    'id' => $charge->ticket_id,
                    'entry_time' => $charge->entry_time,
                    'now' => $charge->calc_time,
                    'minutes' => $charge->minutes,
                    'grace_minutes' => $charge->grace_minutes,
                    'charged_hours' => $charge->charged_hours,
                    'price_per_hour' => $charge->price_per_hour,
                    'amount' => $charge->amount,
                ]
            ]);

            return response()->json([
                'status' => 'success',
                'ticket' => [
                    'id' => $result[0]->ticket_id,
                    'entry_time' => $result[0]->entry_time,
                    'now' => $result[0]->now,
                    'minutes' => $result[0]->minutes,
                    'grace_minutes' => $result[0]->grace_minutes,
                    'charged_hours' => $result[0]->charged_hours,
                    'price_per_hour' => $result[0]->price_per_hour,
                    'amount' => $result[0]->amount,
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

            Log::error('Ticket charge preview failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible consultar el cobro',
            ], 400);

        } catch (\Throwable $e) {

            Log::error('Ticket charge preview failed (throwable)', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible consultar el cobro',
            ], 500);
        }
    }
}
