<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardFinanceApiController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'date_field' => 'nullable|in:exit_time,created_at',
            'include_cancelled' => 'nullable|in:0,1',
            'avg_type' => 'nullable|in:ticket,session,branch',
        ]);

        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch_id ?? null;
        $dateField = $request->date_field ?? 'exit_time';
        $includeCancelled = $request->include_cancelled ?? 0;
        $avgType = $request->avg_type ?? 'ticket';

        try {

            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("CALL sp_get_dashboard_kpis(?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $start,
                $end,
                $branchId,
                $dateField,
                $includeCancelled,
                $avgType
            ]);

            // 1) KPIs
            $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 2) Métodos de pago
            $stmt->nextRowset();
            $paymentMethods = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status' => 'success',
                'filters' => [
                    'start' => $start,
                    'end' => $end,
                    'branch_id' => $branchId,
                    'date_field' => $dateField,
                    'include_cancelled' => (int)$includeCancelled,
                    'avg_type' => $avgType,
                ],
                'kpis' => $summary[0] ?? [],
                'payment_methods' => $paymentMethods
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Dashboard KPIs failed', [
                'exception' => $e
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener los indicadores financieros'
            ], 500);
        }
    }
}
