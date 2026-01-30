<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TicketApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'plate' => ['required', 'regex:/^[A-Z0-9-]{5,10}$/'],
            'parking_space_id' => 'required|exists:parking_spaces,id',
        ]);

        // 🔐 JWT seguro
        //$user = $request->get('auth_user');
        $user = $request->attributes->get('auth_user'); // o auth()->user() si setUser() ya se hizo

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {

            $result = DB::select(
                'CALL sp_register_ticket_entry(?, ?, ?)',
                [
                    strtoupper($request->plate),
                    $request->parking_space_id,
                    $user->id
                ]
            );

            if (empty($result)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El SP no devolvió datos del ticket (revisa resultsets / SELECTs internos).',
                ], 500);
            }

            $row = $result[0];

            // Validación defensiva (si el resultset no es el esperado)
            if (!isset($row->ticket_id, $row->folio, $row->token)) {
                Log::warning('SP returned unexpected resultset', ['result' => $result]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Respuesta inesperada del SP (posible SELECT interno en sp_log_ticket_event).',
                    ...(config('app.debug') ? ['debug' => $result] : [])
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'ticket' => [
                    'id' => $row->ticket_id,
                    'folio' => $row->folio,
                    'token' => $row->token,
                    'plate' => $row->plate,
                    'entry_time' => $row->entry_time,
                    'branch_id' => $row->branch_id,
                    'parking_space_id' => $row->parking_space_id,
                    'vehicle_type_id' => $row->vehicle_type_id,
                ],
                'print' => [
                    'qr_text' => $row->qr_text
                ]
            ], 201);

        } catch (QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $driverMessage = $e->errorInfo[2] ?? $e->getMessage();

            // 🎯 errores de negocio desde SP
            if ($sqlState === '45000') {
                return response()->json([
                    'status' => 'error',
                    'message' => $driverMessage,
                ], 409);
            }

            Log::error('Ticket entry SQL failed', [
                'sqlstate' => $sqlState,
                'message' => $driverMessage
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible registrar el ingreso',
                ...(config('app.debug') ? [
                    'debug' => [
                        'sqlstate' => $sqlState,
                        'detail' => $driverMessage
                    ]
                ] : [])
            ], 400);

        } catch (\Throwable $e) {

            Log::critical('Ticket entry fatal error', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible registrar el ingreso',
            ], 500);
        }
    }

}
