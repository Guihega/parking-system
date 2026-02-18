@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper" x-data="{ modalOpen: false }" id="sessions-root">
    <div class="container-fluid px-4 py-4">

        {{-- HEADER --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Cortes de Caja</h1>
            </div>
        </header>

        {{-- TABLA GLASS --}}
        <div class="glass-table-container shadow-lg">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Fecha de Apertura</th>
                            <th>Fecha de Cierre</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $s)
                        <tr>
                            <td class="fw-bold text-accent">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="d-flex align-items-center text-slate-300">
                                    <i class="fas fa-sign-in-alt text-success me-2 opacity-50"></i>
                                    {{ \Carbon\Carbon::parse($s->opened_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-slate-300">
                                    <i class="fas fa-sign-out-alt text-danger me-2 opacity-50"></i>
                                    {{ $s->closed_at ? \Carbon\Carbon::parse($s->closed_at)->format('d/m/Y H:i') : '---' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-status {{ $s->is_open ? 'status-open' : 'status-closed' }}">
                                    <i class="fas {{ $s->is_open ? 'fa-unlock' : 'fa-lock' }} me-1"></i>
                                    {{ $s->is_open ? 'Abierta' : 'Cerrado' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button onclick="loadSessionDetail({{ $s->id }})" class="btn-action-view border-0 bg-transparent">
                                    <i class="fas fa-eye me-1"></i> Ver Detalle
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL (Cash Sessions) --}}
    <div
        class="cash-modal-overlay"
        x-cloak
        x-show="modalOpen"
        x-transition.opacity
        :class="{ 'is-open': modalOpen }"
        @keydown.escape.window="modalOpen = false"
        @click.self="modalOpen = false"
    >
        <div class="cash-modal-card" x-show="modalOpen" x-transition.scale.95>
            <button class="cash-modal-close" type="button" @click="modalOpen = false">
                <i class="fas fa-times"></i>
            </button>

            <div id="modalBodyContent"></div>
        </div>
    </div>

</div>

<style>
    /* Estilos base mantenidos */
    :root {
        --bg-deep: #0a0e17;
        --card-bg: rgba(22, 27, 44, 0.75);
        --accent: #3b82f6;
        --border-glass: rgba(255, 255, 255, 0.08);
        --text-muted: #94a3b8;
    }

    .dashboard-wrapper {
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    /* --- ESTILOS DEL MODAL --- */
    .modal-overlay {
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center; padding: 1.5rem;
    }

    .modal-content-wrapper {
        background: rgba(22, 27, 44, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 28px;
        width: 100%;
        max-width: 1000px; /* ligeramente más amplio */
        position: relative;
        box-shadow: 0 30px 80px rgba(0,0,0,.55);
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-close-btn {
        position: absolute;
        top: 18px;
        right: 22px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: #94a3b8;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        transition: 0.3s;
    }

    .modal-close-btn:hover {
        background: rgba(239,68,68,0.15);
        color: #ef4444;
        transform: rotate(90deg);
    }

    .modal-close-btn:hover { background: #ef4444; transform: rotate(90deg); }

    [x-cloak]{ display:none !important; }

    .cash-modal-overlay{
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,.70);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);

        display:flex;
        align-items:center;
        justify-content:center;
        padding: 1.5rem;
        /* estado base (cerrado) */
        opacity: 0;
        pointer-events: none;
        transition: opacity .25s ease;
    }
    .cash-modal-overlay.is-open{
        opacity: 1;
        pointer-events: auto;
    }

    .cash-modal-card{
        width: 100%;
        max-width: 1000px;
        max-height: 90vh;
        overflow: auto;

        background: rgba(22,27,44,.85);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 28px;
        box-shadow: 0 30px 80px rgba(0,0,0,.55);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);

        position: relative;
    }

    .cash-modal-close{
        position:absolute;
        top: 18px;
        right: 22px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.10);
        color: #94a3b8;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        transition: .3s;
    }
    .cash-modal-close:hover{
        background: rgba(239,68,68,.15);
        color:#ef4444;
        transform: rotate(90deg);
    }

</style>

<script>
    async function loadSessionDetail(sessionId) {
        const container = document.getElementById('modalBodyContent');
        container.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-3 small text-uppercase fw-bold tracking-wider">Cargando reporte...</p>
            </div>`;

        // Fix para Alpine 3.x: Acceso seguro al estado global del componente
        const rootEl = document.getElementById('sessions-root');

        if (rootEl && rootEl.__x) {
            rootEl.__x.$data.modalOpen = true;
        } else if (window.Alpine) {
            Alpine.$data(rootEl).modalOpen = true;
        }

        try {
            const response = await fetch(`/admin/cash-sessions/${sessionId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = `<div class="p-5 text-center text-danger">Error al obtener los datos del servidor.</div>`;
        }
    }

    async function switchToCloseMode(sessionId) {
        const container = document.getElementById('modalBodyContent');
        container.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-3 small fw-bold">Preparando cierre...</p>
            </div>`;

        try {

            const response = await fetch(`/admin/cash-sessions/${sessionId}/close-preview`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const html = await response.text();
            container.innerHTML = html;
            attachCloseListeners();
        } catch (error) {
            container.innerHTML = `
                <div class="p-4 text-danger text-center">
                    Error al cargar el cierre.
                </div>`;
        }
    }


    async function confirmCloseCashSession(sessionId, expectedAmount) {

        const realInput = document.getElementById('real_amount');
        const obsInput  = document.getElementById('observations');

        const realAmount = parseFloat(realInput.value);

        if (isNaN(realAmount)) {
            alert('Debes ingresar el monto contado.');
            return;
        }

        if (!confirm('¿Confirmas el cierre de la caja? Esta acción no se puede deshacer.')) {
            return;
        }

        const button = event.target;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cerrando...';

        try {

            const response = await fetch('/api/cash-sessions/close', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cash_session_id: sessionId,
                    real_amount: realAmount,
                    observations: obsInput.value
                })
            });

            const data = await response.json();

            if (data.status === 'success') {

                alert('Caja cerrada correctamente.');

                // recargar detalle ya cerrado
                loadSessionDetail(sessionId);

            } else {

                alert(data.message || 'Error al cerrar caja.');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-lock me-1"></i> Confirmar Cierre';
            }

        } catch (error) {

            alert('Error inesperado.');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-lock me-1"></i> Confirmar Cierre';
        }
    }

    function attachCloseListeners() {

        const realInput = document.getElementById('real_amount');
        const diffLabel = document.getElementById('diff_label');

        if (!realInput) return;

        realInput.addEventListener('input', function() {

            const expected = parseFloat({{ json_encode($expectedAmount ?? 0) }}) || 0;
            const real = parseFloat(this.value) || 0;
            const diff = real - expected;

            diffLabel.innerText = '$' + diff.toFixed(2);

            diffLabel.style.color = diff < 0 ? '#ef4444' : '#10b981';
        });
    }

</script>
@endsection
