<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CashSession;
use Barryvdh\DomPDF\Facade\Pdf;

class CashSessionReportController extends Controller
{
    public function index()
    {
        $tenantId = app('tenant_id');
        $user = auth()->user();

        $query = CashSession::where('tenant_id', $tenantId)
            ->orderByDesc('opened_at');

        // 🔐 Si NO tiene permiso global, solo ve sus cajas
        if (!$user->can('cash.manage.all')) {
            $query->where('user_id', $user->id);
        }

        $sessions = $query->limit(50)->get();

        return view('admin.cash_sessions.index', compact('sessions'));
    }


    public function show($id)
    {
        $tenantId = app('tenant_id');

        $report = DB::select(
            'CALL sp_get_cash_session_report(?, ?)',
            [$tenantId, $id]
        );

        $summary = $report[0] ?? null;
        $byPayment = array_slice($report, 1);

        // Si la petición es AJAX, retornamos una vista parcial o "show_partial"
        // para que se renderice correctamente dentro del modal.
        if (request()->ajax()) {
            return view('admin.cash_sessions.show', compact('summary','byPayment'));
        }

        return view('admin.cash_sessions.show', compact('summary','byPayment'));
    }

    public function pdf($id)
    {
        $tenantId = app('tenant_id');

        $report = DB::select(
            'CALL sp_get_cash_session_report(?, ?)',
            [$tenantId, $id]
        );

        $summary = $report[0] ?? null;
        $byPayment = array_slice($report, 1);

        $pdf = Pdf::loadView('admin.cash_sessions.pdf', compact('summary','byPayment'));

        return $pdf->download("corte_caja_$id.pdf");
    }
}
