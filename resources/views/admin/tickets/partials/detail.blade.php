
<div class="modal-card" role="dialog" aria-modal="true">
    <!-- HEADER -->
    <div class="modal-header">
        <div class="modal-title-wrap">
            <i class="fas fa-tags text-primary me-2"></i>
            <div>
                <h4 class="modal-title mb-1 text-white fw-bold">
                    Ticket <span class="text-accent">{{ $ticket->folio }}</span>
                </h4>
                <small class="text-muted opacity-75">
                    Token: {{ $ticket->token }}
                </small>
            </div>
        </div>
        <button class="ua-close" @click="modalOpen=false">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- BODY -->
    <div class="modal-body">
{{--         <div class="status-chip-enhanced
            {{ $ticket->status_code == 'open' ? 'success' :
               ($ticket->status_code == 'closed' ? 'warning' : 'danger') }}">

            <i class="fas
                {{ $ticket->status_code == 'open' ? 'fa-unlock' :
                   ($ticket->status_code == 'closed' ? 'fa-check-circle' : 'fa-ban') }} me-2"></i>

            {{ strtoupper($ticket->status_code) }}
        </div> --}}

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="kpi-mini-card glass-vibrant">
                    <div class="kpi-icon-bg"><i class="fas fa-clock"></i></div>
                    <span class="kpi-label">Tiempo Estacionado</span>
                    <h5 class="kpi-value text-white">
                        {{ $ticket->exit_time
                            ? \Carbon\Carbon::parse($ticket->entry_time)
                                ->diff(\Carbon\Carbon::parse($ticket->exit_time))
                                ->format('%h h %i min')
                            : 'En curso' }}
                    </h5>
                </div>
            </div>

            <div class="col-md-4">
                <div class="kpi-mini-card glass-vibrant">
                    <div class="kpi-icon-bg"><i class="fas fa-dollar-sign"></i></div>
                    <span class="kpi-label">Total Cobrado</span>
                    <h5 class="kpi-value text-accent">
                        ${{ number_format($ticket->total_amount,2) }}
                    </h5>
                </div>
            </div>

            <div class="col-md-4">
                <div class="kpi-mini-card glass-vibrant">
                    <div class="kpi-icon-bg"><i class="fas fa-user"></i></div>
                    <span class="kpi-label">Operador</span>
                    <h5 class="kpi-value text-white">
                        {{ $ticket->user_name }}
                    </h5>
                </div>
            </div>

        </div>


        {{-- INFORMACIÓN DETALLADA --}}
        <div class="glass-table-inner shadow-sm p-4 mb-4">

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Placa</label>
                        <span>{{ $ticket->plate }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Tipo de Vehículo</label>
                        <span>{{ $ticket->vehicle_type }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Sucursal</label>
                        <span>{{ $ticket->branch_name }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Cajón</label>
                        <span>{{ $ticket->parking_space_code }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Entrada</label>
                        <span>{{ \Carbon\Carbon::parse($ticket->entry_time)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <label>Salida</label>
                        <span>
                            {{ $ticket->exit_time
                                ? \Carbon\Carbon::parse($ticket->exit_time)->format('d/m/Y H:i')
                                : '---' }}
                        </span>
                    </div>
                </div>

            </div>

        </div>


        {{-- TIMELINE --}}
        <div class="section-header-compact mb-3">
            <span class="text-uppercase tracking-wider fw-bold small text-muted">
                Historial del Ticket
            </span>
        </div>

        <div class="timeline-container">

            <div class="timeline-item success">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <strong>Entrada registrada</strong>
                    <small>{{ \Carbon\Carbon::parse($ticket->entry_time)->format('d/m/Y H:i') }}</small>
                </div>
            </div>

            @if($ticket->exit_time)
            <div class="timeline-item warning">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <strong>Salida procesada</strong>
                    <small>{{ \Carbon\Carbon::parse($ticket->exit_time)->format('d/m/Y H:i') }}</small>
                </div>
            </div>
            @endif

            @if($ticket->status_code == 'canceled')
            <div class="timeline-item danger">
                <div class="timeline-dot"></div>
                <div class="timeline-content">
                    <strong>Ticket cancelado</strong>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- FOOTER -->
{{--     <div class="modal-footer">
        <div class="form-actions">
            <button type="button"
                    @click="modalOpen=false"
                    class="btn-cancel">
                Salir
            </button>
        </div>
    </div> --}}

</div>



<style>

.glass-table-inner{
    background:rgba(255,255,255,.03);
    border-radius:20px;
    border:1px solid rgba(255,255,255,.06);
}

.info-item{
    padding:12px 0;
    border-bottom:1px solid rgba(255,255,255,.05);
}

.info-item:last-child{
    border-bottom:none;
}

.info-item label{
    font-size:.65rem;
    text-transform:uppercase;
    letter-spacing:1px;
    color:#64748b;
    font-weight:700;
}

.info-item span{
    font-weight:600;
    font-size:.95rem;
    color:white;
}


.info-item{
    display:flex;
    flex-direction:column;
    font-size:.85rem;
}

.info-item label{
    font-size:.65rem;
    text-transform:uppercase;
    letter-spacing:1px;
    color:#94a3b8;
    font-weight:700;
}

.info-item span{
    font-weight:600;
    color:white;
}
.timeline-container{
    position:relative;
    padding-left:30px;
}

.timeline-container::before{
    content:"";
    position:absolute;
    left:10px;
    top:0;
    bottom:0;
    width:2px;
    background:linear-gradient(to bottom, #3b82f6, rgba(255,255,255,.1));
}

.timeline-item{
    position:relative;
    margin-bottom:25px;
    padding-left:20px;
}

.timeline-dot{
    position:absolute;
    left:-5px;
    top:5px;
    width:14px;
    height:14px;
    border-radius:50%;
    box-shadow:0 0 15px currentColor;
}

.timeline-item.success .timeline-dot{
    background:#10b981;
    color:#10b981;
}

.timeline-item.warning .timeline-dot{
    background:#f59e0b;
    color:#f59e0b;
}

.timeline-item.danger .timeline-dot{
    background:#ef4444;
    color:#ef4444;
}

.timeline-content{
    background:rgba(255,255,255,.04);
    padding:16px 20px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.08);
    transition:all .2s ease;
}

.timeline-content:hover{
    transform:translateX(4px);
}

.glow-box{
    width:48px;
    height:48px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.05);
    box-shadow:0 0 25px rgba(59,130,246,.15);
}

.status-chip-enhanced{
    padding:8px 16px;
    border-radius:999px;
    font-size:.75rem;
    font-weight:700;
    letter-spacing:.5px;
    display:flex;
    align-items:center;
    backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.1);
}

.status-chip-enhanced.success{
    background:rgba(16,185,129,.15);
    color:#10b981;
}

.status-chip-enhanced.warning{
    background:rgba(245,158,11,.15);
    color:#f59e0b;
}

.status-chip-enhanced.danger{
    background:rgba(239,68,68,.15);
    color:#ef4444;
}

.kpi-mini-card{
    background:linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
    border-radius:18px;
    padding:20px;
    border:1px solid rgba(255,255,255,.06);
    transition:all .25s ease;
}

.kpi-mini-card:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 30px rgba(0,0,0,.25);
}

.kpi-mini-card::before{
    content:"";
    position:absolute;
    inset:0;
    background:radial-gradient(circle at top left, rgba(59,130,246,.15), transparent 70%);
    opacity:.4;
    pointer-events:none;
}

.kpi-icon-bg{
    width:40px;
    height:40px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.06);
    margin-bottom:10px;
}

.modal-title span.text-accent{
    font-size:1.1rem;
    font-weight:800;
    letter-spacing:.5px;
}

.glass-table-inner{
    background:linear-gradient(
        145deg,
        rgba(255,255,255,.05),
        rgba(255,255,255,.02)
    );
    border-radius:22px;
    padding:30px;
    box-shadow:0 20px 40px rgba(0,0,0,.25);
}

.info-item{
    padding:18px 0;
}

</style>
