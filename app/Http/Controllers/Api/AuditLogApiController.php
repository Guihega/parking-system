<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start'     => 'nullable|date',
            'end'       => 'nullable|date',
            'user_id'   => 'nullable|integer|exists:users,id',
            'action'    => 'nullable|string|max:30',
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $start = $request->start
            ? date('Y-m-d 00:00:00', strtotime($request->start))
            : now()->subDays(7)->startOfDay();

        $end = $request->end
            ? date('Y-m-d 23:59:59', strtotime($request->end))
            : now()->endOfDay();

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare("CALL sp_get_audit_log(?, ?, ?, ?, ?)");
            $stmt->execute([
                $start,
                $end,
                $request->user_id,
                $request->action,
                $request->branch_id
            ]);

            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status'  => 'success',
                'filters' => [
                    'start' => $start,
                    'end'   => $end,
                    'user_id' => $request->user_id,
                    'action' => $request->action,
                    'branch_id' => $request->branch_id,
                ],
                'logs' => $logs
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Audit log failed', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener la bitácora'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'start'  => 'required|date',
            'end'    => 'required|date',
            'format' => 'required|in:csv,json',
        ]);

        $logs = DB::table('audit_logs')
            ->whereBetween('created_at', [
                $request->start,
                $request->end
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->format === 'json') {
            return response()->json([
                'status' => 'success',
                'logs' => $logs
            ]);
        }

        // CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Fecha',
                'Acción',
                'Actor',
                'Target',
                'Descripción'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at,
                    $log->action,
                    $log->actor_user_id,
                    "{$log->target_type}#{$log->target_id}",
                    $log->description
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
