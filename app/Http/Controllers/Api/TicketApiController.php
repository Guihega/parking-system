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
        // 1️⃣ Normalización defensiva
        $request->merge([
            'plate' => strtoupper(trim($request->plate ?? ''))
        ]);

        // 2️⃣ Validación robusta
        $validated = $request->validate([
            'plate' => ['required', 'regex:/^[A-Z0-9-]{4,12}$/'],
            'parking_space_id' => 'required|integer|exists:parking_spaces,id',
            'vehicle_type_id' => 'required|integer|exists:vehicle_types,id',
        ]);

        // 🔐 Usuario desde JWT
        $user = $request->attributes->get('auth_user');

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        try {

            // 3️⃣ Ejecutar SP
            $result = DB::select(
                'CALL sp_register_ticket_entry(?, ?, ?, ?, ?)',
                [
                    (int) $user->tenant_id,                  // 1️⃣ tenant
                    $validated['plate'],                     // 2️⃣ plate
                    (int) $validated['parking_space_id'],    // 3️⃣ space
                    (int) $validated['vehicle_type_id'],     // 4️⃣ vehicle
                    (int) $user->id                          // 5️⃣ user
                ]
            );

            if (empty($result)) {
                Log::error('SP returned empty resultset for ticket entry');
                return response()->json([
                    'status' => 'error',
                    'message' => 'No fue posible generar el ticket (respuesta vacía del SP).'
                ], 500);
            }

            $row = $result[0];

            // 4️⃣ Validación estructural del resultset
            if (
                !isset(
                    $row->ticket_id,
                    $row->folio,
                    $row->token
                )
            ) {
                Log::warning('Unexpected SP result structure', [
                    'result' => $result
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Respuesta inesperada del sistema al registrar ticket.'
                ], 500);
            }

            // 5️⃣ Respuesta consistente
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
                    'qr_text' => $row->qr_text ?? null
                ]
            ], 201);

        } catch (QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $driverMessage = $e->errorInfo[2] ?? $e->getMessage();

            // 🎯 Error de negocio lanzado desde SP (SIGNAL SQLSTATE '45000')
            if ($sqlState === '45000') {
                return response()->json([
                    'status' => 'error',
                    'message' => $driverMessage,
                ], 409);
            }

            Log::error('Ticket entry SQL error', [
                'sqlstate' => $sqlState,
                'message' => $driverMessage
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible registrar el ingreso.'
            ], 400);

        } catch (\Throwable $e) {

            Log::critical('Ticket entry fatal error', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error inesperado al registrar el ingreso.'
            ], 500);
        }
    }


}
