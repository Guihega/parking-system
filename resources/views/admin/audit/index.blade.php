@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Auditoría</h1>
    <div class="mb-3 d-flex gap-3 align-items-end">
        <div>
            <label>Desde</label>
            <input type="date" id="filter-start" class="form-control">
        </div>

        <div>
            <label>Hasta</label>
            <input type="date" id="filter-end" class="form-control">
        </div>

        <div>
            <label>Acción</label>
            <select id="filter-action" class="form-control">
                <option value="">Todas</option>
                <option value="entry">Ingreso</option>
                <option value="charge">Consulta de cobro</option>
                <option value="payment">Pago</option>
                <option value="cancel">Cancelación</option>
                <option value="reprint">Reimpresión</option>
            </select>
        </div>

        <button id="applyFiltersBtn">Aplicar</button>
        <span id="loadingIndicator" style="display:none;">
            Cargando auditoría…
        </span>
    </div>
    <table id="audit-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Placa</th>
                <th>Sucursal</th>
            </tr>
        </thead>
        <tbody id="audit-table-body">
            <!-- JS va a pintar aquí -->
        </tbody>
    </table>
</div>

<script>

    let isLoading = false;

    const AUDIT_ACTION_LABELS = {
        entry:   'Ingreso de vehículo',
        charge:  'Consulta de cobro',
        payment: 'Pago registrado',
        cancel:  'Cancelación de ticket',
        reprint: 'Reimpresión de comprobante',
    };

    function saveAuditFilters(filters) {
        localStorage.setItem('audit_filters', JSON.stringify(filters));
    }

    function loadAuditFilters() {
        const raw = localStorage.getItem('audit_filters');
        return raw ? JSON.parse(raw) : {};
    }

    document.addEventListener('DOMContentLoaded', () => {
        const applyBtn = document.getElementById('applyFiltersBtn');

        if (!applyBtn) {
            console.error('Botón aplicar no encontrado');
            return;
        }

        applyBtn.addEventListener('click', async () => {

            const start  = document.getElementById('filter-start').value;
            const end    = document.getElementById('filter-end').value;
            const action = document.getElementById('filter-action').value;

            const params = {};
            if (start)  params.start = start;
            if (end)    params.end = end;
            if (action) params.action = action;

            saveAuditFilters(params);
            await loadAuditLogs(params);
        });

        // ✅ FASE 3.1 — carga inicial automática
        const savedFilters = loadAuditFilters();

        // Restaurar inputs
        if (savedFilters.start)  document.getElementById('filter-start').value = savedFilters.start;
        if (savedFilters.end)    document.getElementById('filter-end').value   = savedFilters.end;
        if (savedFilters.action) document.getElementById('filter-action').value = savedFilters.action;

        // Cargar auditoría con filtros persistidos
        loadAuditLogs(savedFilters);
    });

    function formatAuditDate(dateString) {
        if (!dateString) return '-';

        const date = new Date(dateString.replace(' ', 'T'));

        if (isNaN(date)) return dateString;

        return date.toLocaleString('es-MX', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function renderAuditTable(logs) {
        const tbody = document.getElementById('audit-table-body');
        tbody.innerHTML = '';

        if (!logs || logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Sin resultados</td>
                </tr>
            `;
            return;
        }

        logs.forEach(log => {
            const row = document.createElement('tr');

            row.innerHTML = `
                <td>${formatAuditDate(log.created_at)}</td>
                <td>${formatAuditAction(log.action)}</td>
                <td>${formatAuditPayload(log)}</td>
                <td>${log.plate ?? '-'}</td>
                <td>${log.branch ?? '-'}</td>
            `;

            tbody.appendChild(row);
        });
    }


    function setLoading(state) {
        isLoading = state;

        document.getElementById('applyFiltersBtn').disabled = state;
        document.getElementById('loadingIndicator').style.display =
            state ? 'inline' : 'none';
    }

    async function loadAuditLogs(filters = {}) {

        if (isLoading) return; // 🔒 defensa crítica

        setLoading(true);

        try {
            const response = await fetch('/admin/audit/logs?' + new URLSearchParams(filters));
            const data = await response.json();

            if (data.status !== 'success') {
                throw new Error('Respuesta inválida');
            }

            renderAuditTable(data.logs);

        } catch (error) {
            console.error('AUDIT LOAD ERROR:', error);
            alert('Error al cargar auditoría');
        } finally {
            setLoading(false);
        }
    }

    function formatAuditAction(action) {
        return AUDIT_ACTION_LABELS[action] ?? action ?? '-';
    }

    function formatAuditPayload(log) {
        let payload = {};

        try {
            payload = typeof log.payload === 'string'
                ? JSON.parse(log.payload)
                : log.payload ?? {};
        } catch {
            return '-';
        }

        switch (log.action) {

            case 'entry':
                return `Placa: ${payload.plate ?? '-'} · Cajón: ${payload.parking_space ?? '-'}`;

            case 'charge':
                return `Tiempo: ${payload.minutes ?? '-'} min · Monto: $${payload.amount ?? '-'}`;

            case 'payment':
                return `Monto: $${payload.amount ?? '-'} · Método: ${payload.payment_code ?? '-'}`;

            case 'cancel':
            case 'reprint':
                return `Motivo: ${payload.reason ?? '-'}`;

            default:
                return '-';
        }
    }

</script>

@endsection
