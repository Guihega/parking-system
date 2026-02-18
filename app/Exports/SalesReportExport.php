<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    protected $tenantId;
    protected $start;
    protected $end;

    public function __construct($tenantId, $start, $end)
    {
        $this->tenantId = $tenantId;
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        return collect(
            DB::select(
                'CALL sp_get_sales_report(?, ?, ?)',
                [$this->tenantId, $this->start, $this->end]
            )
        );
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tickets',
            'Total'
        ];
    }
}
