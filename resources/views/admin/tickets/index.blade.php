@extends('layouts.app')

@section('content')

<div class="dashboard-wrapper" x-data="{ modalOpen:false }" @open-ticket-modal.window="modalOpen = true" id="tickets-root">
    <div class="container-fluid px-4 py-4">

        {{-- HEADER --}}
        <header class="header-distribuido mb-4">
            <div class="header-titles">
                <h1 class="h3 fw-bold text-white mb-0">Auditoría de Tickets</h1>
                <p class="text-muted small mb-0">Listado general de ingresos al estacionamiento</p>
            </div>
        </header>

        {{-- FILTROS --}}
        <div class="glass-filter-container mb-4 p-3">
            <form method="GET" class="filter-row">

                <div class="filter-item small">
                    <label class="filter-label">Desde</label>
                    <input type="date" name="from" class="filter-input">
                </div>

                <div class="filter-item small">
                    <label class="filter-label">Hasta</label>
                    <input type="date" name="to" class="filter-input">
                </div>

                <div class="filter-item">
                    <label class="filter-label">Sucursal</label>
                    <select name="branch_id" class="filter-input">
                        <option value="">Todas</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item">
                    <label class="filter-label">Estado</label>
                    <select name="status" class="filter-input">
                        <option value="">Todos</option>
                        <option value="open">Abierto</option>
                        <option value="closed">Cerrado</option>
                        <option value="canceled">Cancelado</option>
                    </select>
                </div>

                @if($canSeeAll)
                <div class="filter-item">
                    <label class="filter-label">Usuario</label>
                    <select name="user_id" class="filter-input">
                        <option value="">Todos</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="filter-item search-block">
                    <label class="filter-label">Buscar</label>
                    <div class="search-wrapper">
                        <input type="text" name="q"
                            placeholder="Folio / Placa / Token"
                            class="filter-input search-input">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

            </form>

        </div>


        {{-- TABLA GLASS --}}
        <div class="glass-table-container shadow-lg">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Placa</th>
                            <th>Sucursal</th>
                            <th>Usuario</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $t)
                        <tr>
                            {{-- Folio --}}
                            <td class="fw-bold text-accent">
                                {{ $t->folio }}
                            </td>

                            {{-- Placa --}}
                            <td class="text-slate-300">
                                <i class="fas fa-car me-2 opacity-50 text-info"></i>
                                {{ $t->plate }}
                            </td>

                            {{-- Sucursal --}}
                            <td class="text-slate-300">
                                <i class="fas fa-building me-2 opacity-50 text-primary"></i>
                                {{ $t->branch?->name ?? '—' }}
                            </td>

                            {{-- Usuario --}}
                            <td class="text-slate-300">
                                <i class="fas fa-user me-2 opacity-50 text-warning"></i>
                                {{ $t->user?->name ?? '—' }}
                            </td>

                            {{-- Entrada --}}
                            <td>
                                <div class="d-flex align-items-center text-slate-300">
                                    <i class="fas fa-sign-in-alt text-success me-2 opacity-50"></i>
                                    {{ $t->entry_time?->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            {{-- Salida --}}
                            <td>
                                <div class="d-flex align-items-center text-slate-300">
                                    <i class="fas fa-sign-out-alt text-danger me-2 opacity-50"></i>
                                    {{ $t->exit_time
                                        ? $t->exit_time->format('d/m/Y H:i')
                                        : '---' }}
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="text-center">
                                @php
                                    $statusCode = $t->status?->code;
                                @endphp

                                <span class="badge-status
                                    {{ $statusCode === 'open' ? 'status-open' : '' }}
                                    {{ $statusCode === 'closed' ? 'status-closed' : '' }}
                                    {{ $statusCode === 'canceled' ? 'status-canceled' : '' }}">

                                    <i class="fas
                                        {{ $statusCode === 'open' ? 'fa-unlock' : '' }}
                                        {{ $statusCode === 'closed' ? 'fa-lock' : '' }}
                                        {{ $statusCode === 'canceled' ? 'fa-ban' : '' }}
                                        me-1">
                                    </i>

                                    {{ $t->status?->name ?? '—' }}
                                </span>
                            </td>

                            {{-- Total --}}
                            <td class="fw-bold text-accent text-end">
                                ${{ number_format($t->total_amount,2) }}
                            </td>

                            {{-- Acciones --}}
                            <td class="text-end">
                                <button onclick="loadTicketDetail('{{ $t->token }}')"
                                    class="btn-action-view border-0 bg-transparent">
                                    <i class="fas fa-eye me-1"></i> Detalle
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>

                </table>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-4 ">
            <div class="paginacion glass-pagination-wrapper">
                {{ $tickets->links() }}
            </div>
        </div>

    </div>

    {{-- MODAL DETALLE --}}
    <div class="modal-overlay"
         x-cloak
         x-show="modalOpen"
         :class="{ 'is-open': modalOpen }"
         @click.self="modalOpen=false">

        <div id="ticketModalBody"></div>
    </div>

</div>
<style>

    .glass-filter-container{
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 18px;
        backdrop-filter: blur(10px);
    }

    .filter-label{
        font-size:.7rem;
        text-transform:uppercase;
        letter-spacing:1px;
        color:#94a3b8;
        font-weight:700;
    }

    .filter-input{
        width:100%;
        padding:10px 12px;
        border-radius:12px;
        border:1px solid rgba(255,255,255,.12);
        background:rgba(0,0,0,.25);
        color:white;
    }

    .status-canceled{
        background:rgba(239,68,68,.1);
        color:#ef4444;
        border:1px solid rgba(239,68,68,.2);
    }

</style>
<script>
    async function loadTicketDetail(token){

        const container = document.getElementById('ticketModalBody');

        container.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary"></div>
                <p class="text-muted mt-3">Cargando detalle...</p>
            </div>
        `;

        const rootEl = document.getElementById('tickets-root');
        if(rootEl && rootEl.__x){
            rootEl.__x.$data.modalOpen = true;
        }

        try{
            const res = await fetch(`/admin/tickets/${token}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const html = await res.text();
            container.innerHTML = html;

        }catch(e){
            container.innerHTML = `
                <div class="p-4 text-danger text-center">
                    Error al cargar el detalle.
                </div>`;
        }

        window.dispatchEvent(new CustomEvent('open-ticket-modal'));

    }


</script>

@endsection
