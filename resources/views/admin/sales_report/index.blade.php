@extends('layouts.app')
@section('content')
{{-- 1. SCRIPTS DE SOPORTE (Plugin Collapse + Alpine Core) --}}
<script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="dashboard-wrapper" x-data="{ chartVisible: true, tableVisible: true }">
    <div class="container-fluid px-4 py-4">

        {{-- HEADER --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Reporte de Ventas</h1>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.sales-report.export', request()->all()) }}" class="btn-action-outline success">
                    <i class="fas fa-file-excel me-2"></i> Excel
                </a>
                <a href="{{ route('admin.sales-report.pdf', request()->all()) }}" class="btn-action-outline danger">
                    <i class="fas fa-file-pdf me-2"></i> PDF
                </a>
            </div>
        </header>

        {{-- BARRA DE CONTROL (FILTROS + KPIs) --}}
        <div class="header-control-wrapper mb-4">
            <div class="filter-container-glass">
                <form class="filters-row-custom">
                    <div class="filter-field">
                        <label class="form-label-custom">Desde</label>
                        <div class="input-group-custom">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="date" name="start_date" class="ua-input" value="{{ $start->toDateString() }}">
                        </div>
                    </div>
                    <div class="filter-separator">/</div>
                    <div class="filter-field">
                        <label class="form-label-custom">Hasta</label>
                        <div class="input-group-custom">
                            <i class="fas fa-calendar-check"></i>
                            <input type="date" name="end_date" class="ua-input" value="{{ $end->toDateString() }}">
                        </div>
                    </div>
                    <div class="filter-btn-box">
                        <button class="btn-new-space-vibrant btn-sync">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="kpi-container-horizontal">
                <div class="kpi-card-modern compact">
                    <div class="kpi-content">
                        <span class="kpi-label">Tickets</span>
                        <h3 class="kpi-value text-white mb-0">{{ is_array($sales) ? array_sum(array_column($sales, 'tickets_count')) : $sales->sum('tickets_count') }}</h3>
                    </div>
                    <div class="kpi-icon-box info small"><i class="fas fa-ticket-alt"></i></div>
                </div>
                <div class="kpi-card-modern compact">
                    <div class="kpi-content">
                        <span class="kpi-label">Ingresos</span>
                        <h3 class="kpi-value text-white mb-0">${{ number_format(is_array($sales) ? array_sum(array_column($sales, 'total_amount')) : $sales->sum('total_amount'), 0) }}</h3>
                    </div>
                    <div class="kpi-icon-box success small"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>

        {{-- 4. CHART (GRANDE Y COLAPSABLE) --}}
        <div class="chart-container-glass mb-4 overflow-hidden shadow-lg">
            <div class="panel-header-modern">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line text-accent"></i>
                    <h5 class="fw-bold mb-0 text-white small text-uppercase tracking-wider">Tendencia de Ventas</h5>
                </div>
                {{-- Botón para colapsar con Alpine.js --}}
                <button @click="chartVisible = !chartVisible" class="btn-toggle-panel">
                    <i class="fas" :class="chartVisible ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>

            <div x-show="chartVisible" x-collapse x-transition.duration.400ms class="p-4 pt-0">
                {{-- ALTURA INCREMENTADA A 500PX --}}
                <div class="chart-wrapper" style="height: 500px; position: relative; width: 100%;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- 5. TABLE (COLAPSABLE) --}}
        <div class="glass-table-container overflow-hidden shadow-lg">
            <div class="panel-header-modern">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-table text-accent"></i>
                    <h5 class="fw-bold mb-0 text-white small text-uppercase tracking-wider">Desglose por Fecha</h5>
                </div>
                <button @click="tableVisible = !tableVisible" class="btn-toggle-panel">
                    <i class="fas" :class="tableVisible ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
            </div>
            <div x-show="tableVisible" x-collapse x-transition.duration.400ms>
                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Fecha de Venta</th>
                                <th class="text-center">Tickets Emitidos</th>
                                <th class="text-end">Monto Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $s)
                            <tr>
                                <td class="fw-medium text-slate-300">{{ $s->sale_date }}</td>
                                <td class="text-center">
                                    <span class="badge-tickets">{{ $s->tickets_count }}</span>
                                </td>
                                <td class="text-end fw-bold text-white">${{ number_format($s->total_amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- ESTRUCTURA BASE --- */
    :root {
        --bg-deep: #0a0e17;
        --card-bg: rgba(22, 27, 44, 0.7);
        --accent: #3b82f6;
        --border-glass: rgba(255, 255, 255, 0.06);
        --text-muted: #94a3b8;
    }

    .dashboard-wrapper {
        min-height: 100vh;
        background: radial-gradient(circle at top right, #1a202c, #0a0e17);
        font-family: 'Inter', sans-serif;
    }

    .header-distribuido {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }


    /* --- COMPONENTES GLASS --- */
    .glass-toolpanel, .chart-container-glass, .kpi-card-modern, .glass-table-container {
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
    }

    .glass-toolpanel { padding: 1.5rem; }

    .kpi-card-modern {
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: 0.3s;
    }

    .kpi-card-modern:hover { transform: translateY(-3px); border-color: var(--accent); }

    .form-label-custom {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
        display: block;
    }

    .input-group-custom {
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--border-glass);
        border-radius: 12px;
        padding: 4px 15px;
        display: flex;
        align-items: center;
    }

    .select-clean {
        background: transparent;
        border: none;
        color: white;
        padding: 10px 0;
        width: 100%;
        outline: none;
    }

    /* Estilo del botón */
    .btn-new-space-vibrant {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 12px;
        font-weight: 700;
        transition: 0.3s;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }

    .btn-new-space-vibrant:hover {
        filter: brightness(1.1);
        transform: scale(1.02);
    }

    /* KPI BOXES */
    .kpi-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .kpi-icon-box.info { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
    .kpi-icon-box.success { background: rgba(16, 185, 129, 0.15); color: #10b981; }

    /* TABLA */
    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { padding: 1rem 1.5rem; color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--border-glass); }
    .table-custom td { padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.02); color: #cbd5e1; }
    .badge-tickets { background: rgba(59, 130, 246, 0.1); color: var(--accent); padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.8rem; }

    .btn-action-outline {
        padding: 8px 18px;
        border-radius: 10px;
        border: 1px solid var(--border-glass);
        color: white;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-action-outline.success:hover { background: #10b981; border-color: #10b981; }
    .btn-action-outline.danger:hover { background: #ef4444; border-color: #ef4444; }

    /* Calendario blanco */
    input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }

    /* Ajuste crucial para forzar la distribución horizontal */
    @media (min-width: 1200px) {
        .col-xl-7 { flex: 0 0 60%; max-width: 60%; }
        .col-xl-5 { flex: 0 0 40%; max-width: 40%; }
    }

    /* FORZAR DISTRIBUCIÓN HORIZONTAL Y ALTURA IGUAL */
    .header-control-wrapper {
        display: flex;
        gap: 20px;
        align-items: stretch; /* Esto iguala las alturas */
        width: 100%;
    }

    .filter-container-glass {
        flex: 0 0 60%; /* Ocupa exactamente el 60% */
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
    }

    .kpi-container-horizontal {
        flex: 0 0 calc(40% - 20px); /* Ocupa el 40% restante menos el gap */
        display: flex;
        gap: 15px;
    }

    .filters-row-custom {
        display: flex;
        align-items: flex-end;
        width: 100%;
        gap: 15px;
    }

    .filter-field { flex: 1; }

    .filter-separator {
        color: rgba(255,255,255,0.2);
        font-size: 1.2rem;
        padding-bottom: 8px;
    }

    .kpi-card-modern.compact {
        flex: 1; /* Hace que las dos cards midan lo mismo */
        background: var(--card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border-glass);
        border-radius: 20px;
        padding: 0.8rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: 0.3s;
    }

    .btn-sync {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
    }

    /* Responsivo: Si la pantalla es pequeña, se apilan */
    @media (max-width: 1200px) {
        .header-control-wrapper { flex-direction: column; }
        .filter-container-glass, .kpi-container-horizontal { flex: 0 0 100%; }
    }
 
    /* Mantenemos tus clases de estilo previas */
    .kpi-value { font-size: 1.5rem; font-weight: 900; }
    .kpi-label { font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; display: block; }
    .kpi-icon-box.small { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

    /* CABECERAS DE PANEL (NUEVAS) */
    .panel-header-modern {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
    }

    .btn-toggle-panel {
        background: rgba(255,255,255,0.05);
        border: 1px solid var(--border-glass);
        color: var(--text-muted);
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .btn-toggle-panel:hover { background: var(--accent); color: white; transform: scale(1.05); }
</style>

{{-- CHART SCRIPT (Mismo que antes, asegurando que data no sea nulo) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const data = @json($sales);
        if(data && data.length > 0) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            // Gradiente ajustado a 500px de altura
            const gradient = ctx.createLinearGradient(0, 0, 0, 500);
            gradient.addColorStop(0, '#3b82f6');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.01)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(r => r.sale_date),
                    datasets: [{
                        label: 'Ingresos',
                        data: data.map(r => r.total_amount),
                        backgroundColor: gradient,
                        borderRadius: 12,
                        hoverBackgroundColor: '#60a5fa'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // CLAVE PARA RESPETAR LOS 500PX
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10, 14, 23, 0.9)',
                            padding: 12,
                            cornerRadius: 10,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 12 } } },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.03)' },
                            ticks: {
                                color: '#64748b',
                                font: { size: 12 },
                                callback: v => '$' + v
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
