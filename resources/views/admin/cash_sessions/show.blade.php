{{-- CABECERA DEL DETALLE REDISEÑADA EN UNA SOLA FILA --}}
<div class="modal-premium-header p-3 border-bottom border-white border-opacity-10">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">

        {{-- Lado Izquierdo: Título e ID --}}
        <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>
                <h4 class="modal-title mb-0 text-white fw-900 tracking-tight">
                    Corte <span class="text-accent">#{{ str_pad($summary->cash_session_id, 5, '0', STR_PAD_LEFT) }}</span>
                </h4>
            </div>
        </div>

        {{-- Centro: Chips de Datos (Apertura y Estado) --}}
        <div class="d-flex align-items-center gap-2">
            <div class="chip-glass">
                <i class="fas fa-calendar-alt text-accent me-2"></i>
                <span class="d-none d-md-inline">Apertura: </span><b>{{ \Carbon\Carbon::parse($summary->opened_at)->format('d/m/Y H:i') }}</b>
            </div>
            @if($summary->closed_at)
                <div class="chip-glass warning">
                    <i class="fas fa-check-circle text-warning me-1"></i>
                    <span class="d-none d-md-inline">Estado: </span><b>Cerrado</b>
                </div>
            @else
                <div class="chip-glass success ">
                    <i class="fas fa-unlock text-success me-1"></i>
                    <span class="d-none d-md-inline">Estado: </span><b>Abierto</b>
                </div>
            @endif
        </div>

        {{-- Lado Derecho: Botón de Descarga Compacto --}}
        <div class="header-actions">
            <a href="{{ route('admin.cash-sessions.pdf', $summary->cash_session_id) }}"
               class="btn-download-premium-compact shadow-lg">
                <i class="fas fa-file-pdf me-2"></i>
                <span>DESCARGAR PDF</span>
            </a>
        </div>
    </div>
</div>

<div class="edit-card-body p-4">

    {{-- 1. BLOQUE DE MÉTRICAS PRINCIPALES (KPIs) --}}
    @if($summary)
    <div class="row g-3 mb-4">
        <div class="col-md-4 mb-4">
            <div class="kpi-mini-card glass-vibrant">
                <div class="kpi-icon-bg"><i class="fas fa-wallet"></i></div>
                <span class="kpi-label">Monto Inicial</span>
                <h5 class="kpi-value text-white">${{ number_format($summary->opening_amount,2) }}</h5>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="kpi-mini-card glass-vibrant">
                <div class="kpi-icon-bg"><i class="fas fa-calculator"></i></div>
                <span class="kpi-label">Esperado en Caja</span>
                <h5 class="kpi-value text-white">${{ number_format($summary->expected_amount,2) }}</h5>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            {{-- Tarjeta de Diferencia con Gradiente dinámico --}}
            <div class="kpi-mini-card diff-card {{ $summary->difference < 0 ? 'bg-grad-danger' : 'bg-grad-success' }}">
                <div class="kpi-icon-bg"><i class="fas {{ $summary->difference < 0 ? 'fa-arrow-trend-down' : 'fa-check-double' }}"></i></div>
                <span class="kpi-label text-white-50">Diferencia Final</span>
                <h5 class="kpi-value text-white">
                    ${{ number_format($summary->difference, 2) }}
                </h5>
            </div>
        </div>
    </div>
    @endif

    {{-- 2. DESGLOSE DE PAGOS --}}
    <div class="section-header-compact d-flex align-items-center gap-3 mb-3">
        <span class="text-uppercase tracking-wider fw-bold small text-muted">Desglose por Métodos de Pago</span>
        <div class="flex-grow-1 border-bottom border-white border-opacity-10"></div>
    </div>

    <div class="glass-table-inner shadow-sm">
        <table class="table-custom border-0">
            <thead>
                <tr>
                    <th class="ps-4">Método de Pago</th>
                    <th class="text-center">Cant. Tickets</th>
                    <th class="text-end pe-4">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($byPayment as $p)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="payment-dot me-2"></div>
                            <span class="fw-bold text-slate-200">{{ $p->payment_type_code }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge-tickets-pill">{{ $p->payments_count }}</span>
                    </td>
                    <td class="text-end pe-4 fw-bold text-accent">
                        ${{ number_format($p->total_amount, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- BOTÓN DE CIERRE --}}

    <div class="form-actions mt-3">
        <button type="button" @click="modalOpen = false" class="btn-close-modal">
            Finalizar Revisión
        </button>
        @if(!$summary->closed_at)
            <button
                type="button"
                class="btn-action-view w-100"
                onclick="switchToCloseMode({{ $summary->cash_session_id }})">
                <i class="fas fa-lock me-1"></i> Cerrar Caja
            </button>
        @endif
    </div>
</div>

<style>
    /* AJUSTES PARA CABECERA EN UNA FILA */
    .modal-premium-header {
        background: rgba(255, 255, 255, 0.02);
    }

    .fw-900 { font-weight: 900; }

    .header-icon-box {
        width: 38px;
        height: 38px;
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
    }

    .chip-glass {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        color: #94a3b8;
        white-space: nowrap;
    }
    .chip-glass b { color: #fff; margin-left: 3px; }
    .chip-glass.success { border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.05); }
    .chip-glass.warning { border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.05); }

    /* BOTÓN COMPACTO */
    .btn-download-premium-compact {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        padding: 8px 16px;
        border-radius: 10px;
        color: white;
        text-decoration: none !important;
        font-weight: 800;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        transition: 0.3s;
    }

    .btn-download-premium-compact:hover {
        transform: translateY(-2px);
        filter: brightness(1.1);
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
    }

    .btn-download-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 20px -3px rgba(153, 27, 27, 0.5);
        filter: brightness(1.1);
    }

    .btn-download-premium .icon-side {
        padding: 12px 15px;
        background: rgba(0, 0, 0, 0.2);
        font-size: 1.3rem;
    }

    .btn-download-premium .text-side {
        padding: 0 15px;
        display: flex;
        flex-direction: column;
        line-height: 1.1;
    }

    .btn-download-premium .text-side span { font-weight: 800; font-size: 0.8rem; letter-spacing: 1px; }
    .btn-download-premium .text-side small { font-size: 0.6rem; opacity: 0.8; font-weight: 500; }

    /* Ajuste de scroll para modales largos */
    .modal-content-wrapper {
        max-height: 95vh;
        overflow-y: auto;
    }

    /* Estilos Premium para el Modal */
    .glass-vibrant {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

    .kpi-mini-card {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 20px;
        transition: 0.3s;
    }

    .kpi-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 3rem;
        opacity: 0.05;
        color: white;
    }

    .bg-grad-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(239, 68, 68, 0.05) 100%);
        border: 1px solid rgba(239, 68, 68, 0.3) !important;
    }

    .bg-grad-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
    }

    .badge-tickets-pill {
        background: rgba(255, 255, 255, 0.05);
        color: var(--accent);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 800;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .payment-dot {
        width: 8px;
        height: 8px;
        background: var(--accent);
        border-radius: 50%;
        box-shadow: 0 0 8px var(--accent);
    }

    .btn-close-modal {
        width: 100%;
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 14px;
        border-radius: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: 0.3s;
    }

    .btn-close-modal:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }

    .tracking-wider { letter-spacing: 0.05em; }
</style>
