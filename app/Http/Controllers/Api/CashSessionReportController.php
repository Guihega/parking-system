<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CashSessionReportController extends Controller
{
    public function show($id)
    {
        // 1. Caja
        $cashSession = DB::table('cash_sessions')
            ->where('id', $id)
            ->first();

        if (!$cashSession) {
            return response()->json([
                'status' => 'error',
                'message' => 'Caja no encontrada'
            ], 404);
        }

        // 2. Totales por método de pago
        $paymentsByType = DB::table('payments as p')
            ->join('payment_types as pt', 'pt.id', '=', 'p.payment_type_id')
            ->select(
                'pt.id',
                'pt.code',
                'pt.name',
                DB::raw('SUM(p.amount) as total')
            )
            ->where('p.cash_session_id', $id)
            ->groupBy('pt.id', 'pt.code', 'pt.name')
            ->get();

        // 3. Total cobrado
        $totalCollected = DB::table('payments')
            ->where('cash_session_id', $id)
            ->sum('amount');

        // 4. Monto esperado en caja
        $expectedAmount = $cashSession->opening_amount + $totalCollected;

        // 5. Respuesta estructurada
        return response()->json([
            'status' => 'success',
            'cash_session' => [
                'id' => $cashSession->id,
                'branch_id' => $cashSession->branch_id,
                'user_id' => $cashSession->user_id,
                'opening_amount' => $cashSession->opening_amount,
                'closing_amount' => $cashSession->closing_amount,
                'opened_at' => $cashSession->opened_at,
                'closed_at' => $cashSession->closed_at,
                'is_open' => $cashSession->is_open,
            ],
            'payments_summary' => $paymentsByType,
            'total_collected' => $totalCollected,
            'expected_cash_amount' => $expectedAmount
        ]);
    }
}
