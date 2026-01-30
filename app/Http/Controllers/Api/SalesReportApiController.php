<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SalesReportApiController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("CALL sp_get_sales_report(?, ?)");
            $stmt->execute([$request->start, $request->end]);

            $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt->nextRowset();
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmt->nextRowset();
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status' => 'success',
                'summary' => $summary[0],
                'payments' => $payments,
                'tickets' => $tickets
            ]);

        } catch (\Throwable $e) {

            Log::error('Sales report failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener el reporte de ventas'
            ], 500);
        }
    }
}

