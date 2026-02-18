<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ✅ Carbon, NO string
        $start = now()->subDays(30);
        $end   = now();

        // ============================
        // KPIs
        // ============================
        $kpis = DB::select(
            'CALL sp_get_dashboard_kpis(?, ?, ?, ?, ?, ?)',
            [
                $start->toDateString(), // p_start (DATE)
                $end->toDateString(),   // p_end   (DATE)
                0,                      // p_branch_id (GLOBAL)
                'created_at',           // p_date_field
                0,                      // p_include_cancelled
                'ticket',               // p_avg_type
            ]
        );

        // ============================
        // Charts
        // ============================
        $charts = DB::select(
            'CALL sp_get_dashboard_charts(?, ?, ?)',
            [
                (int) app('tenant_id'),
                $start->startOfDay()->toDateTimeString(),
                $end->endOfDay()->toDateTimeString(),
            ]
        );

        return view('admin.dashboard.index', [
            'kpis'   => $kpis[0] ?? null,
            'charts' => $charts,
        ]);
    }


}
