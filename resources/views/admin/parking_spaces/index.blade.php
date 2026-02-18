@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="container-fluid px-4 py-4">
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Gestión de Espacios</h1>
            </div>
            {{-- <a href="{{ route('admin.parking-spaces.create') }}" class="btn-new-space-vibrant"> <i class="fas fa-plus-circle me-2 px-1"></i> Espacio </a> --}}
            <div class="header-actions">
                <a href="javascript:void(0)"
                    class="btn-primary-action"
                    onclick="openCreateModal()">
                    <i class="fas fa-plus-circle me-2 px-1"></i> Espacio
                </a>
            </div>
        </header>
        <div class="glass-toolpanel mb-4">
            <div class="row g-3 align-items-center card-body">
                <div class="col-md-9 col-lg-10">
                    <form class="m-0">
                        <div class="input-group-custom" >
                            <i class="fas fa-filter icon-muted me-2"></i>
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
                <div class="col-md-3 col-lg-2">
                    <div class="stats-row">
                        <div class="stat-pill">
                            <span class="label">Total:</span>
                            <span class="value text-white">{{ count($spaces) }}</span>
                        </div>
                        <div class="stat-pill available">
                            <span class="label">Libres:</span>
                            <span class="value text-success">{{ count($spaces->where('status_id', 1)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID -->
        <div class="glass-toolpanel parking-matrix px-3 py-3">
            @foreach($spaces as $s)
                <div class="slot-card {{ $s->status->name == 'Disponible' ? 'border-free' : 'border-busy' }}">
                    <div class="slot-body">
                        <div class="slot-main">
                            <div class="slot-identity">
                                <span class="slot-code-text">{{ $s->code }}</span>
                                <span class="branch-name-text">
                                    <i class="fas fa-map-marker-alt me-1 px-1"></i>
                                    {{ $s->branch->name ?? 'Principal' }}
                                </span>
                                <span class="branch-name-text">
                                    <i class="fas fa-car-side me-1 px-1"></i>
                                    {{ $s->vehicleType->name ?? 'Sin tipo' }}
                                </span>
                            </div>
                            <div class="status-indicator-modern {{ $s->status->name == 'Disponible' ? 'is-free' : 'is-busy' }}">
                                {{ $s->status->name }}
                            </div>
                        </div>

                        <div class="slot-actions mt-3">
                            <a href="javascript:void(0)"
                               class="btn-slot-edit"
                               onclick="openEditModal({{ $s->id }})">
                                <i class="fas fa-pencil-alt me-2 px-1"></i>Editar
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- MODAL EDIT (FUERA DEL LOOP Y DEL GRID) -->
<div id="editModal" class="modal-overlay">
    <button class="modal-close" onclick="closeEditModal()">×</button>
</div>

<style>
    :root {
        --bg-deep: #0a0e17;
        --card-bg: rgba(22, 27, 44, 0.7);
        --accent: #3b82f6;
        --border-glass: rgba(255, 255, 255, 0.06);
    }

    .dashboard-wrapper {
        min-height: 100vh;
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

    .stats-row {
        display: flex;
        gap: 20px;
        justify-content: flex-end;
    }

    .stat-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.03);
        padding: 6px 15px;
        border-radius: 100px;
        font-size: 0.85rem;
    }

    .stat-pill .label { color: #94a3b8; font-weight: 600; }
    .stat-pill .value { font-weight: 800; }

    /* PARKING GRID */
    .parking-matrix {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .slot-card {
        background: rgba(22, 27, 44, 0.75);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,.35);
        transition: all .25s ease;
        position: relative;
    }

    .slot-card:hover {
        transform: translateY(-4px);
        border-color: rgba(59,130,246,.4);
        box-shadow: 0 15px 40px rgba(0,0,0,.45);
    }


    .border-free {
        box-shadow: inset 4px 0 0 #10b981;
    }

    .border-busy {
        box-shadow: inset 4px 0 0 #ef4444;
    }


    .slot-body { padding: 1.5rem; }

    .slot-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .slot-code-text {
        font-size: 1.8rem;
        font-weight: 900;
        letter-spacing: -1px;
        color: #ffffff;
    }

    .branch-name-text {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 6px;
        display: flex;
        opacity: .8;
    }


    .status-indicator-modern {
        font-size: 0.7rem;
        font-weight: 800;
        padding: 6px 12px;
        border-radius: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }


    .is-free { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .is-busy { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

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

    /* Estilo del botón (Vibrante pero integrado) */
    .btn-new-space-vibrant {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white !important;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap; /* Imprescindible para que no se rompa */
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        transition: 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-new-space-vibrant:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
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


</style>

<script>
    async function openEditModal(spaceId)
    {
        try {
            const res = await fetch(`/admin/parking-spaces/${spaceId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!res.ok) {
                throw new Error('Error cargando formulario');
            }

            const html = await res.text();
            document.getElementById('editModal').innerHTML = html;
            document.getElementById('editModal').classList.add('is-open');
        } catch (e) {
            alert('No se pudo cargar el formulario');
            console.error(e);
        }
    }

    async function openCreateModal()
    {
        try {
            const res = await fetch(`/admin/parking-spaces/create`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!res.ok) {
                throw new Error('Error cargando formulario');
            }

            const html = await res.text();
            document.getElementById('editModal').innerHTML = html;
            document.getElementById('editModal').classList.add('is-open');
        } catch (e) {
            alert('No se pudo cargar el formulario');
            console.error(e);
        }
    }

    function closeEditModal()
    {
        const modal = document.getElementById('editModal');
        modal.classList.remove('is-open');
        document.getElementById('editModal').innerHTML = '';
    }


    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });

    const editModal = document.getElementById('editModal');

    editModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

</script>
@endsection
