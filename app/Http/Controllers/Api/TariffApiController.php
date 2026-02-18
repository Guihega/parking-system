<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TariffApiController extends Controller
{
    public function index()
    {
        $tariffs = DB::table('tariffs_v2')
            ->where('is_active', 1)
            ->orderBy('priority', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'tariffs' => $tariffs
        ]);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'vehicle_type_id' => 'required|integer',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'calc_type' => 'required|in:hourly,flat',
            'price_per_hour' => 'nullable|numeric',
            'flat_amount' => 'nullable|numeric',
            'grace_minutes' => 'nullable|integer',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'days_of_week' => 'nullable|string',
            'priority' => 'nullable|integer',
        ]);

        try {
            $id = DB::table('tariffs_v2')->insertGetId([
                'branch_id' => $request->branch_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'calc_type' => $request->calc_type,
                'price_per_hour' => $request->price_per_hour,
                'flat_amount' => $request->flat_amount,
                'grace_minutes' => $request->grace_minutes ?? 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'days_of_week' => $request->days_of_week,
                'priority' => $request->priority ?? 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // === AUDITORÍA ===
            DB::statement("CALL sp_log_tariff_event(?, ?, ?, ?, ?)", [
                $id,
                'tariff_create',
                'Creación de tarifa',
                json_encode([
                    'created' => $request->all()
                ]),
                auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'tariff_id' => $id
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Create tariff failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible crear la tarifa'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'calc_type' => 'sometimes|required|in:hourly,flat',
            'price_per_hour' => 'nullable|numeric',
            'flat_amount' => 'nullable|numeric',
            'grace_minutes' => 'nullable|integer',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'days_of_week' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            // Obtener datos ANTES
            $before = DB::table('tariffs_v2')->where('id', $id)->first();

            if (!$before) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tarifa no encontrada'
                ], 404);
            }

            // Actualizar
            DB::table('tariffs_v2')->where('id', $id)->update(array_merge(
                $request->only([
                    'name',
                    'description',
                    'calc_type',
                    'price_per_hour',
                    'flat_amount',
                    'grace_minutes',
                    'start_time',
                    'end_time',
                    'start_date',
                    'end_date',
                    'days_of_week',
                    'priority',
                    'is_active'
                ]),
                ['updated_at' => now()]
            ));

            // Obtener datos DESPUÉS
            $after = DB::table('tariffs_v2')->where('id', $id)->first();

            // AUDITORÍA
            DB::statement("CALL sp_log_tariff_event(?, ?, ?, ?, ?)", [
                $id,
                'tariff_update',
                'Actualización de tarifa',
                json_encode([
                    'before' => $before,
                    'after'  => $after
                ]),
                auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tarifa actualizada'
            ]);

        } catch (\Throwable $e) {
            Log::error('Update tariff failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible actualizar la tarifa'
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        try {
            // Estado antes
            $before = DB::table('tariffs_v2')->where('id', $id)->first();

            if (!$before) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tarifa no encontrada'
                ], 404);
            }

            // Desactivar tarifa (soft delete)
            DB::table('tariffs_v2')->where('id', $id)->update([
                'is_active' => 0,
                'updated_at' => now()
            ]);

            // Estado después
            $after = DB::table('tariffs_v2')->where('id', $id)->first();

            // AUDITORÍA
            DB::statement("CALL sp_log_tariff_event(?, ?, ?, ?, ?)", [
                $id,
                'tariff_disable',
                'Desactivación de tarifa',
                json_encode([
                    'before' => $before,
                    'after'  => $after,
                    'reason' => $request->reason
                ]),
                auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tarifa desactivada'
            ]);

        } catch (\Throwable $e) {
            Log::error('Delete tariff failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible desactivar la tarifa'
            ], 500);
        }
    }

}
