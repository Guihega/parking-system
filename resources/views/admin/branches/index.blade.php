@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="container-fluid px-4 py-4">
        {{-- HEADER HOMOLOGADO --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Gestión de Sucursales</h1>
            </div>
            <div class="header-actions">
                <a href="javascript:void(0)"
                   class="btn-primary-action"
                   onclick="openCreateBranchModal()">
                    <i class="fas fa-plus-circle me-2 px-1"></i> Sucursal
                </a>
            </div>
        </header>
        {{-- GRID HOMOLOGADO --}}
        <div class="glass-toolpanel parking-matrix px-3 py-3">
            @foreach($branches as $branch)
                <div class="slot-card {{ $branch->is_active ? 'border-free' : 'border-busy' }}">
                    <div class="slot-body">

                        <div class="slot-main">
                            <div class="slot-identity">
                                <span class="slot-code-text">
                                    {{ $branch->name }}
                                </span>
                                <span class="branch-name-text">
                                    <i class="fas fa-map-marked-alt me-1 px-1"></i>
                                    Sucursal Registrada
                                </span>
                            </div>
                            <div class="status-indicator-modern {{ $branch->is_active ? 'is-free' : 'is-busy' }}">
                                {{ $branch->is_active ? 'Activa' : 'Inactiva' }}
                            </div>
                        </div>
                        <div class="slot-actions mt-3">
                            <a href="javascript:void(0)"
                               class="btn-slot-edit"
                               onclick="openEditBranchModal({{ $branch->id }})">
                                <i class="fas fa-pencil-alt me-2 px-1"></i> Editar sucursal
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="branchModal" class="modal-overlay">
            <button class="modal-close" onclick="closeBranchModal()">×</button>
        </div>
    </div>
</div>

<style>
    /* VARIABLES Y FONDO */
    :root {
        --bg-deep: #0a0e17;
        --card-bg: rgba(22, 27, 44, 0.7);
        --accent: #3b82f6;
        --border-glass: rgba(255, 255, 255, 0.06);
    }

    .dashboard-wrapper {
        min-height: 100vh;
    }

    /* HEADER DISTRIBUIDO */
    .header-distribuido {
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
    }

    .glass-toolpanel {
        background: #ffffff0f;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 22px;
        box-shadow: 0 10px 30px #0003;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

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
    }

    /* GRID Y CARDS */
    .parking-matrix {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .slot-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid var(--border-glass);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .slot-card:hover {
        transform: translateY(-5px);
        background: rgba(28, 35, 58, 0.9);
        border-color: var(--accent);
    }

    .border-free { border-left: 4px solid #10b981; }
    .border-busy { border-left: 4px solid #ef4444; }

    .slot-body { padding: 1.5rem; }

    .slot-main {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .slot-code-text {
        font-size: 1.3rem;
        font-weight: 800;
        display: block;
        line-height: 1.2;
        color: white;
    }

    .branch-name-text {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 6px;
        display: block;
    }

    .status-indicator-modern {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        text-transform: uppercase;
    }

    .is-free { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .is-busy { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    /* BOTÓN EDITAR */
    .btn-slot-edit {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 8px;
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--border-glass);
        border-radius: 10px;
        color: #94a3b8;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-slot-edit:hover {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    /* MODAL */
    .modal-overlay {
        position: fixed;
        inset: 0;
        backdrop-filter: blur(6px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-overlay.is-open { display: flex; }

    .modal-content {
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        max-width: 500px;
        width: 90%;
    }

    .modal-close {
        margin-top: 18px;
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: none;
        background: #1f6cff;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .header-distribuido { flex-direction: column; text-align: center; gap: 15px; }
    }
</style>

<script>
    async function openCreateBranchModal() {
        try {
            const res = await fetch(`/admin/branches/create`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            if (!res.ok) throw new Error();
            document.getElementById('branchModal').innerHTML = await res.text();
            document.getElementById('branchModal').classList.add('is-open');
        } catch (e) {
            alert('No se pudo cargar el formulario');
        }
    }

    async function openEditBranchModal(branchId) {
        try {
            const res = await fetch(`/admin/branches/${branchId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            if (!res.ok) throw new Error();
            document.getElementById('branchModal').innerHTML = await res.text();
            document.getElementById('branchModal').classList.add('is-open');
        } catch (e) {
            alert('No se pudo cargar el formulario');
        }
    }

    function closeBranchModal() {
        document.getElementById('branchModal').classList.remove('is-open');
        document.getElementById('branchModal').innerHTML = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeBranchModal();
        }
    });

    const editModal = document.getElementById('branchModal');

    editModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeBranchModal();
        }
    });
</script>
@endsection
