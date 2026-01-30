<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashSessionReportApiController extends Controller
{
    public function show(int $cashSessionId)
    {
        try {

            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("CALL sp_get_cash_session_report(?)");
            $stmt->execute([$cashSessionId]);

            // 1) Resumen
            $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 2) Pagos por tipo
            $stmt->nextRowset();
            $payments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 3) Tickets
            $stmt->nextRowset();
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status' => 'success',
                'summary' => $summary[0] ?? null,
                'payments' => $payments,
                'tickets' => $tickets
            ]);

        } catch (\Throwable $e) {

            Log::error('Cash session report failed', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener el reporte de caja'
            ], 500);
        }
    }
}
