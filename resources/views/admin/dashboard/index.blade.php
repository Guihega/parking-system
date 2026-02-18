@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="container-fluid px-4 py-4">
        {{-- 1. HEADER --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Panel de Control</h1>
            </div>
            <div class="header-actions">
                <span class="badge-status status-open py-2 px-3">
                    <i class="fas fa-clock me-2"></i>{{ now()->format('d/m/Y H:i') }}
                </span>
            </div>
        </header>

        {{-- 2. KPI GRID (CORREGIDO: CADA CARD DENTRO DE SU COLUMNA) --}}
        <div class="row g-3 mb-4">
            {{-- Ventas Totales --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card-modern glass-vibrant h-100">
                    <div class="kpi-content">
                        <span class="kpi-label">Ventas Totales</span>
                        <h3 class="kpi-value text-white">${{ number_format($kpis->total_sales ?? 0, 2) }}</h3>
                    </div>
                    <div class="kpi-icon-box success shadow-glow-success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            {{-- Tickets --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card-modern glass-vibrant h-100">
                    <div class="kpi-content">
                        <span class="kpi-label">Tickets Generados</span>
                        <h3 class="kpi-value text-white">{{ number_format($kpis->tickets_count ?? 0) }}</h3>
                    </div>
                    <div class="kpi-icon-box info shadow-glow-info">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>

            {{-- Ticket Promedio --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card-modern glass-vibrant h-100">
                    <div class="kpi-content">
                        <span class="kpi-label">Ticket Promedio</span>
                        <h3 class="kpi-value text-white">${{ number_format($kpis->avg_ticket ?? 0, 2) }}</h3>
                    </div>
                    <div class="kpi-icon-box warning shadow-glow-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            {{-- Cajas Cerradas --}}
            <div class="col-xl-3 col-md-6">
                <div class="kpi-card-modern glass-vibrant h-100">
                    <div class="kpi-content">
                        <span class="kpi-label">Sesiones Cerradas</span>
                        <h3 class="kpi-value text-white">{{ $kpis->closed_cash_sessions ?? 0 }}</h3>
                    </div>
                    <div class="kpi-icon-box danger shadow-glow-danger">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. GRÁFICO PRINCIPAL --}}
        <div class="chart-container-glass shadow-lg">
            <div class="panel-header-modern p-4 border-bottom border-white border-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-chart-area text-accent"></i>
                    <h5 class="fw-bold mb-0 text-white small text-uppercase tracking-wider">Flujo de Ingresos Temporales</h5>
                </div>
            </div>
            <div class="p-4">
                <div style="height: 450px; position: relative;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($charts);
        const ctx = document.getElementById('salesChart').getContext('2d');

        // Gradiente para el área del gráfico
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(r => r.chart_date),
                datasets: [{
                    label: 'Ingresos Diarios',
                    data: chartData.map(r => r.total_amount),
                    borderColor: '#3b82f6',
                    borderWidth: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: 'rgba(255,255,255,0.2)',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4 // Curva suave
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(10, 14, 23, 0.95)',
                        padding: 15,
                        cornerRadius: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Total: $' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.03)' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: v => '$' + v.toLocaleString()
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
