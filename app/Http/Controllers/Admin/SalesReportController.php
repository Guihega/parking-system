<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReportController extends Controller
{
    private function getDates(Request $request)
    {
        $start = $request->get('start_date')
            ? now()->parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end = $request->get('end_date')
            ? now()->parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        return [$start, $end];
    }

    public function index(Request $request)
    {
        $tenantId = app('tenant_id');

        $start = $request->get('start_date')
            ? now()->parse($request->start_date)->startOfDay()
            : now()->subDays(30)->startOfDay();

        $end = $request->get('end_date')
            ? now()->parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        $sales = DB::select(
            'CALL sp_get_sales_report(?, ?, ?)',
            [$tenantId, $start, $end]
        );

        DB::disconnect(); // 👈 ESTE es el fix importante

        return view('admin.sales_report.index', compact('sales','start','end'));
    }

    public function export(Request $request)
    {
        $tenantId = app('tenant_id');
        [$start, $end] = $this->getDates($request);

        // Se pasan las fechas formateadas para evitar el error del SP
        return Excel::download(
            new SalesReportExport($tenantId, $start->toDateString(), $end->toDateString()),
            'reporte_ventas.xlsx'
        );
    }

    public function pdf(Request $request)
    {
        $tenantId = app('tenant_id');
        [$start, $end] = $this->getDates($request);

        $sales = DB::select('CALL sp_get_sales_report(?, ?, ?)', [$tenantId, $start, $end]);

        $pdf = Pdf::loadView('admin.sales_report.pdf', compact('sales', 'start', 'end'));
        return $pdf->download('reporte_ventas.pdf');
    }
}
