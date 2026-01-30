<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashSessionAuditApiController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'user_id' => 'nullable|integer|exists:users,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'only_closed' => 'nullable|in:0,1'
        ]);

        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare("CALL sp_get_cash_session_audit(?, ?, ?, ?, ?)");
            $stmt->execute([
                $request->start,
                $request->end,
                $request->user_id,
                $request->branch_id,
                $request->only_closed ?? 1
            ]);

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return response()->json([
                'status' => 'success',
                'filters' => $request->only(['start','end','user_id','branch_id','only_closed']),
                'cash_sessions' => $rows
            ]);

        } catch (\Throwable $e) {
            Log::error('Cash session audit error', ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'No fue posible obtener la auditoría de cajas'
            ], 500);
        }
    }
}
