<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardChartsApiController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'date_field' => 'nullable|in:exit_time,created_at',
            'include_cancelled' => 'nullable|in:0,1',
        ]);

        $start = $request->start;
        $end = $request->end;
        $branchId = $request->branch_id ?? null;
        $dateField = $request->date_field ?? 'exit_time';
        $includeCancelled = $request->include_cancelled ?? 0;

        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("CALL sp_get_dashboard_charts(?, ?, ?, ?, ?)");
            $stmt->execute([
                $start,
                $end,
                $branchId,
                $dateField,
                $includeCancelled
            ]);

            // 1) Ventas por día
            $salesByDay = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 2) Ventas por método
            $stmt->nextRowset();
            $salesByMethod = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // 3) Flujo por sucursal
            $stmt->nextRowset();
            $flowByBranch = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status' => 'success',
                'filters' => [
                    'start' => $start,
                    'end' => $end,
                    'branch_id' => $branchId,
                    'date_field' => $dateField,
                    'include_cancelled' => (int)$includeCancelled
                ],
                'charts' => [
                    'sales_by_day' => $salesByDay,
                    'sales_by_method' => $salesByMethod,
                    'flow_by_branch' => $flowByBranch
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Dashboard charts failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener las gráficas del dashboard'
            ], 500);
        }
    }
}
