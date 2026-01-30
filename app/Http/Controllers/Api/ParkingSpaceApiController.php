<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParkingSpaceApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'status'    => 'nullable|string'
        ]);

        $spaces = DB::select(
            'CALL sp_get_parking_spaces(?, ?)',
            [
                $request->branch_id,
                $request->status
            ]
        );

        return response()->json([
            'status' => 'success',
            'data'   => $spaces
        ]);
    }
}
