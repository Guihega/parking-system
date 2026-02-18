@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="container-fluid px-4 py-4">
        {{-- Tabla --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Gestión de Tarifas</h1>
            </div>
            {{-- <button onclick="openCreateTariff()" --}}
            <div class="header-actions">
                <a href="javascript:void(0)"
                    class="btn-primary-action"
                    onclick="openCreateTariff()">
                    <i class="fas fa-plus-circle me-2 px-1"></i> Nueva Tarifa
                </a>
            </div>
        </header>
        <div class="glass-toolpanel mb-4">
            <div class="row g-3 align-items-center">
                <div class="card-body">
                    {{-- Filtro por sucursal --}}
                    <form class="m-0">
                        <div class="input-group-custom" >
                            <i class="fas fa-filter icon-muted me-2 px-2"></i>
                            <select name="branch_id" onchange="this.form.submit()" class="ui-select">
                                <option value="" class="bg-dark">Todas las sucursales</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }} class="bg-dark">
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="glass-table-container shadow-lg">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Sucursal</th>
                            <th>Vehículo</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Prioridad</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tariffs as $t)
                        <tr>
                            <td class="fw-bold text-accent">
                                {{ $t->name }}
                                @if($t->description)
                                    <div class="text-slate-300 small mt-1 opacity-75">
                                        {{ $t->description }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $t->branch->name ?? '---' }}</td>

                            <td>{{ $t->vehicleType->name ?? '---' }}</td>

                            <td>
                                <span class="badge-status {{ $t->calc_type === 'hourly' ? 'status-open' : 'status-closed' }}">
                                    <i class="fas {{ $t->calc_type === 'hourly' ? 'fa-clock' : 'fa-dollar-sign' }} me-1"></i>
                                    {{ $t->calc_type === 'hourly' ? 'Por Hora' : 'Monto Fijo' }}
                                </span>
                            </td>

                            <td>
                                @if($t->calc_type === 'hourly')
                                    ${{ number_format($t->price_per_hour,2) }} / hr
                                @else
                                    ${{ number_format($t->flat_amount,2) }}
                                @endif

                                @if($t->grace_minutes > 0)
                                    <div class="small text-slate-300 opacity-75">
                                        {{ $t->grace_minutes }} min gracia
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="fw-bold">{{ $t->priority }}</span>
                            </td>

                            <td class="text-center">
                                <span class="badge-status {{ $t->is_active ? 'status-open' : 'status-closed' }}">
                                    <i class="fas {{ $t->is_active ? 'fa-check' : 'fa-times' }} me-1"></i>
                                    {{ $t->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="table-actions">
                                    <button onclick="openEditTariff({{ $t->id }})"
                                            class="btn-table-action btn-edit"
                                            title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    {{-- <button onclick="duplicateTariff({{ $t->id }})"
                                            class="btn-table-action btn-duplicate"
                                            title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button> --}}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="editModal" class="modal-overlay">
    <button class="modal-close" onclick="closeEditModal()">×</button>
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

    /* Estilos de tabla y badges de tu diseño previo */
    .table-custom thead {
        background: linear-gradient(
            to right,
            rgba(59,130,246,0.08),
            rgba(59,130,246,0.02)
        );
    }

    .table-custom { width: 100%; border-collapse: collapse; }
    .table-custom th { padding: 1.25rem 1.5rem; background: rgba(0, 0, 0, 0.2); color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; }
    .table-custom td { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-glass); vertical-align: middle; }
    .badge-status { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; }
    .status-open { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-closed { background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }
    .btn-action-view {
        background: rgba(59,130,246,0.12);
        color: #3b82f6;
        padding: 8px 16px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: 0.25s;
        border: 1px solid rgba(59,130,246,0.2);
    }

    .btn-action-view:hover {
        background: #3b82f6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(59,130,246,.25);
    }

    .table-custom tbody tr {
        transition: 0.25s ease;
    }

    .table-custom tbody tr:hover {
        background: rgba(255,255,255,0.04);
        transform: translateX(2px);
    }

    .glass-table-container{
        background: #ffffff0f;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 22px;
        box-shadow: 0 10px 30px #0003;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

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

    /* ACCIONES */
    .btn-slot-edit {
        width: 100%;
        padding: 10px;
        border-radius: 14px;
        font-size: 0.8rem;
        font-weight: 700;
        background: rgba(59,130,246,.08);
        border: 1px solid rgba(59,130,246,.2);
        color: #3b82f6;
        transition: .25s ease;
    }

    .btn-slot-edit:hover {
        background: #3b82f6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(59,130,246,.25);
    }


    /* ESTILO AFINADO PARA EL BOTÓN DE AGREGAR */
    .btn-new-space {
        background: var(--accent);
        color: white;
        padding: 10px 22px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.1);
        white-space: nowrap; /* Evita que el texto se rompa */
    }

    .btn-new-space:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
        color: white;
        filter: brightness(1.1);
    }

    @media (max-width: 768px) {
        .stats-row { justify-content: center; margin-top: 10px; }
        header { flex-direction: column; text-align: center; gap: 15px; }
    }

    /* Estilo del botón para que resalte en el espacio derecho */
    .btn-new-space-vibrant {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white !important;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        transition: 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-new-space-vibrant:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
        filter: brightness(1.1);
    }

    /* Forzar que el header ocupe todo el ancho real */
    header {
        width: 100%;
    }

    /* El contenedor padre ahora fuerza la separación total */
    .header-distribuido {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        /* Evita que elementos internos se estiren */
    }

    /* Forzamos que los títulos solo ocupen el espacio de su texto */
    .header-titles {
        flex: 0 1 auto !important;
        text-align: left;
    }

    /* Forzamos que el contenedor de acciones se pegue a la derecha */
    .header-actions {
        flex: 0 1 auto !important;
        display: flex;
        justify-content: flex-end;
    }

    /* Estilo para el contenedor del select */
    .select-clean {
        background: rgba(0, 0, 0, 0.4); /* Fondo semi-transparente oscuro */
        border: 1px solid var(--border-glass);
        color: white;
        padding: 10px 15px;
        width: 100%;
        outline: none;
        font-size: 0.9rem;
        border-radius: 10px;
        cursor: pointer;
        appearance: none; /* Elimina la flecha nativa para un look más limpio */
    }

    /* Estilo para las opciones (Dropdown) */
    .select-clean option {
        background-color: #1a202c; /* Color sólido oscuro para el fondo de la lista */
        color: white;
        padding: 10px;
    }

    /* Ajuste para el foco */
    .select-clean:focus {
        border-color: var(--accent);
        background: rgba(0, 0, 0, 0.6);
    }

    /* MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.65);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-overlay.is-open {
        display: flex;
    }

    .modal-content {
        background: rgba(22,27,44,.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 28px;
        width: 90%;
        max-width: 650px;
        padding: 2rem;
        position: relative;
        box-shadow: 0 30px 80px rgba(0,0,0,.55);
        animation: fadeScale .2s ease;
    }

    @keyframes fadeScale {
        from { opacity: 0; transform: scale(.96); }
        to   { opacity: 1; transform: scale(1); }
    }

    .modal-close {
        position: absolute;
        top: 18px;
        right: 22px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.1);
        color: #94a3b8;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        cursor: pointer;
        transition: .25s;
    }

    .modal-close:hover {
        background: rgba(239,68,68,.15);
        color: #ef4444;
        transform: rotate(90deg);
    }

    /* TOOLPANEL COMPACTO */
    .glass-toolpanel {
        background: #ffffff0f;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 22px;
        box-shadow: 0 10px 30px #0003;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

/* CONTENEDOR */
.table-actions{
    display: inline-flex;
    gap: 8px;
    justify-content: flex-end;
}

/* BASE */
.btn-table-action{
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,.06);
    background: rgba(255,255,255,.02);
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all .2s ease;
    cursor: pointer;
    backdrop-filter: blur(6px);
}

/* MICRO INTERACCIÓN */
.btn-table-action:hover{
    transform: translateY(-2px);
}

/* EDITAR */
.btn-edit{
    color: #3b82f6;
    border-color: rgba(59,130,246,.25);
    background: rgba(59,130,246,.08);
}

.btn-edit:hover{
    background: #3b82f6;
    color: #fff;
    box-shadow: 0 6px 18px rgba(59,130,246,.35);
}

/* DUPLICAR */
.btn-duplicate{
    color: #10b981;
    border-color: rgba(16,185,129,.25);
    background: rgba(16,185,129,.08);
}

.btn-duplicate:hover{
    background: #10b981;
    color: #fff;
    box-shadow: 0 6px 18px rgba(16,185,129,.35);
}


</style>

<script>
    const editModal = document.getElementById('editModal');

    async function openEditTariff(id)
    {
        try {
            const res = await fetch(`/admin/tariffs/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!res.ok) {
                throw new Error('Error cargando formulario');
            }

            const html = await res.text();

            const modal = document.getElementById('editModal');
            modal.innerHTML = html;
            modal.classList.add('is-open');

        } catch (e) {
            alert('No se pudo cargar el formulario');
            console.error(e);
        }
    }

    async function openCreateTariff()
    {
        try {
            const res = await fetch(`/admin/tariffs/create`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!res.ok) {
                throw new Error('Error cargando formulario');
            }

            const html = await res.text();

            const modal = document.getElementById('editModal');
            modal.innerHTML = html;
            modal.classList.add('is-open');

        } catch (e) {
            alert('No se pudo cargar el formulario');
            console.error(e);
        }
    }


    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });

    editModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    function closeEditModal()
    {
        const modal = document.getElementById('editModal');
        modal.classList.remove('is-open');
        modal.innerHTML = '';
    }

    function duplicateTariff(id)
    {
        if (!confirm('¿Deseas duplicar esta tarifa?')) return;
        window.location.href = `/admin/tariffs/${id}/duplicate`;
    }

    function increment(btn) {
        const input = btn.parentElement.querySelector('input');
        input.stepUp();
    }

    function decrement(btn) {
        const input = btn.parentElement.querySelector('input');
        input.stepDown();
    }
</script>


@endsection
