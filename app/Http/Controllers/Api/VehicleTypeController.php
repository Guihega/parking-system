<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class VehicleTypeController extends Controller
{
    public function index()
    {
        try {

            $types = DB::select('CALL sp_get_vehicle_types()');

            return response()->json([
                'status' => 'success',
                'data'   => $types
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'Error cargando tipos de vehículo',
                'debug'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
