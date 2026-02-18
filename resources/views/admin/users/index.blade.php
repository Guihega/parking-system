@extends('layouts.app')

@section('content')
@php
    $uiPerms = session('permissions', []);
@endphp

<div class="container-fluid ua-page">
    {{-- HEADER --}}
    <div class="ua-header">
        <div class="header-titles">
            <h1 class="h3 fw-bold text-white mb-0">Administración de Usuarios</h1>
        </div>
        <div class="ua-toolbar">
            <button class="btn-primary-action" id="btnReload">
                🔄
            </button>
            @if(in_array('users.create', $uiPerms))
                <button class="btn-primary-action" onclick="openModal('modalCreateUser')">
                    <i class="fas fa-plus-circle me-2 px-1"></i> Nuevo usuario
                </button>
            @endif
        </div>
    </div>

    {{-- ALERT --}}
    <div id="alertBox" class="ua-alert d-none"></div>

    {{-- FILTROS --}}
    <div class="ua-card mb-4">
        <div class="card-body ua-filters">
            <input id="txtSearch" class="ua-input" placeholder="Buscar por nombre o email">
            <select id="selStatus" class="ui-select">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
            <div class="ua-kpi">
                <strong id="lblCount">0</strong> usuarios
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="ua-card">
        <table class="ua-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th class="text-center">Rol</th>
                    <th class="text-center">Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTbody">
                <tr>
                    <td colspan="5" class="ua-muted text-center py-4">
                        <span class="ua-spinner"></span> Cargando usuarios...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- =========================
   MODAL CREAR USUARIO
========================= --}}
<div id="modalCreateUser" class="modal-overlay" data-modal>
    <div class="modal-card" role="dialog" aria-modal="true">
        <!-- HEADER -->
        <div class="modal-header">
            <div class="modal-title-wrap">
                <i class="fas fa-parking text-primary me-2"></i>
                <div>
                    <h3 class="modal-title">Nuevo usuario </h3>
                </div>
            </div>
            <button class="ua-close" onclick="closeModal('modalCreateUser')">✕</button>
        </div>
        <!-- BODY -->
        <div class="modal-body">
            <form id="frmCreate" class="modal-form" novalidate>

                <!-- ERROR GENERAL -->
                <div id="createFormError" class="form-error d-none">
                    ❌ Por favor corrige los errores marcados
                </div>

                <!-- NOMBRE -->
                <div class="form-group">
                    <label>Nombre</label>
                    <input
                        type="text"
                        name="name"
                        class="ua-input"
                        placeholder="Ej. Juan Pérez"
                    >
                    <div class="field-error" data-error-for="name"></div>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        class="ua-input"
                        placeholder="correo@empresa.com"
                    >
                    <div class="field-error" data-error-for="email"></div>
                </div>

                <!-- ROL -->
                <div class="form-group">
                    <label>Rol del usuario</label>
                    <select name="role" id="createRole" class="ui-select">
                        <option value="">Selecciona un rol…</option>
                    </select>
                    <small class="form-hint">
                        El usuario tendrá un solo rol activo.
                    </small>
                    <div class="field-error" data-error-for="role"></div>
                </div>

                <!-- INFO -->
                <div class="modal-info">
                    🔐 La contraseña generada deberá compartirse por un canal seguro.
                </div>

            </form>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
            <button
                type="button"
                class="btn-cancel"
                onclick="closeModal('modalCreateUser')"
            >
                Cancelar
            </button>

            <button
                type="button"
                class="btn-primary-action"
                id="btnCreateSubmit"
            >
                <span id="spCreate" class="ua-spinner d-none"></span>
                Crear usuario
            </button>
        </div>

    </div>
</div>

{{-- =========================
   MODAL CAMBIAR ROL (CUSTOM)
========================= --}}
<div id="modalRole" class="modal-overlay" data-modal>
    <div class="modal-card" role="dialog" aria-modal="true">

        <!-- HEADER -->
        <div class="modal-header">
            <div class="modal-title-wrap">
                <i class="fas fa-parking text-primary me-2"></i>
                <div>
                    <h3 class="modal-title">Cambiar rol</h3>
                    <p class="modal-subtitle" id="roleUserLabel"></p>
                </div>
            </div>
            <button class="ua-close" onclick="closeModal('modalRole')">✕</button>
        </div>
        <!-- BODY -->
        <div class="modal-body">
            <form id="frmRole" class="modal-form" novalidate>

                <div id="roleFormError" class="form-error d-none">
                    ❌ No se pudo actualizar el rol
                </div>

                <div class="form-group">
                    <label>Nuevo rol</label>
                    <select id="roleSelect" class="ui-select">
                        <option value="">Selecciona un rol…</option>
                    </select>
                    <div class="field-error" data-error-for="role"></div>
                </div>

            </form>
        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('modalRole')">
                Cancelar
            </button>

            <button type="button" class="btn btn-warning" id="btnRoleSubmit">
                <span id="spRole" class="ua-spinner d-none"></span>
                Guardar cambios
            </button>
        </div>

    </div>
</div>

{{-- =========================
   MODAL RESET PASSWORD (IMPROVED)
========================= --}}
<div id="modalReset" class="modal-overlay" data-modal>
    <div class="modal-card modal-reset" role="dialog" aria-modal="true">
        <!-- HEADER -->
        <div class="modal-header">
            <div class="modal-title-wrap">
                <i class="fas fa-parking text-primary me-2"></i>
                <div>
                    <h3 class="modal-title">Contraseña generada</h3>
                    <p class="modal-subtitle">
                        Compártela de forma segura con el usuario
                    </p>
                </div>
            </div>
            <button class="ua-close" onclick="closeModal('modalReset')">✕</button>
        </div>
        <!-- BODY -->
        <div class="modal-body">
            <div class="reset-box">
                <label class="reset-label">Contraseña temporal</label>
                <div class="reset-field">
                    <input
                        id="resetPasswordValue"
                        class="ua-input reset-input"
                        readonly
                        spellcheck="false"
                    >
                    <button
                        type="button"
                        class="btn btn-copy"
                        id="btnCopyPassword"
                        title="Copiar contraseña"
                    >
                        📋 Copiar
                    </button>
                </div>
                <div id="copyFeedback" class="reset-feedback d-none">
                    ✅ Copiada al portapapeles
                </div>
            </div>
            <div class="modal-info modal-info--danger">
                ⚠️ Esta contraseña es <strong>temporal</strong>.
                El usuario deberá cambiarla en su primer acceso.
            </div>
        </div>
        <!-- FOOTER -->
        <div class="modal-footer">
            <button
                type="button"
                class="btn-primary-action"
                onclick="closeModal('modalReset')"
            >
                Listo
            </button>
        </div>
    </div>
</div>

<div id="modalConfirm" class="modal-overlay" data-modal>
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <i class="fas fa-exclamation-triangle text-warning me-3"></i>
                <h3 id="confirmTitle" class="modal-title mb-0">Confirmar acción</h3>
            </div>
            <button class="ua-close" onclick="closeModal('modalConfirm')">✕</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage" class="ua-muted"></p>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal('modalConfirm')">
                Cancelar
            </button>
            <button class="btn btn-danger " id="confirmAccept">
                Confirmar
            </button>
        </div>
    </div>
</div>

{{-- Contexto permisos para UI --}}
<script>
    window.__UI_PERMS__ = @json($uiPerms);
</script>

@endsection

<script>
    const API = {
    usersIndex: '/api/users',
    usersStore: '/api/users',
    rolesIndex: '/api/roles',
    userToggleStatus: (id) => `/api/users/${id}/status`,
    userUpdateRole: (id) => `/api/users/${id}/role`,
    userResetPassword: (id) => `/api/users/${id}/reset-password`,
    };

    const can = (perm) => {
        return Array.isArray(window.__UI_PERMS__) &&
            window.__UI_PERMS__.includes(perm);
    };

    let USERS = [];
    let ROLES = [];
    let selectedUserIdForRole = null;


    function csrf() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
    }

    function showAlert(type, message) {
        const box = document.getElementById('alertBox');
        box.className = `alert ua-alert alert-${type}`;
        box.textContent = message;
        box.classList.remove('d-none');

        setTimeout(() => box.classList.add('d-none'), 4500);
    }

    function setLoading(btnId, spinnerId, loading) {
    const btn = document.getElementById(btnId);
    const sp  = document.getElementById(spinnerId);
    if (!btn || !sp) return;

    btn.disabled = loading;
    sp.classList.toggle('d-none', !loading);
    }

    function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
    }


    async function fetchJson(url, opts = {}) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                ...(opts.headers || {})
            },
            credentials: 'same-origin',
            ...opts
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            throw {
                status: res.status,
                message: data.message || 'Error de servidor',
                data
            };
        }

        return data;
    }

    async function loadRoles() {
    const data = await fetchJson(API.rolesIndex);
    ROLES = data.roles || [];

    const createSel = document.getElementById('createRole');
    const roleSel   = document.getElementById('roleSelect');

    const options = ['<option value="">Selecciona...</option>']
        .concat(ROLES.map(r => `<option value="${escapeHtml(r.code)}">${escapeHtml(r.name)} (${escapeHtml(r.code)})</option>`))
        .join('');

    if (createSel) createSel.innerHTML = options;
    if (roleSel) roleSel.innerHTML = options;
    }

    async function loadUsers() {
    const data = await fetchJson(API.usersIndex);
    USERS = data.users || [];
    applyFiltersAndRender();
    }

    function applyFiltersAndRender() {
    const q = (document.getElementById('txtSearch')?.value || '').trim().toLowerCase();
    const st = document.getElementById('selStatus')?.value ?? '';

    let filtered = USERS;

    if (q) {
        filtered = filtered.filter(u =>
        (u.name || '').toLowerCase().includes(q) ||
        (u.email || '').toLowerCase().includes(q)
        );
    }

    if (st !== '') {
        filtered = filtered.filter(u => String(u.is_active ? 1 : 0) === String(st));
    }

    document.getElementById('lblCount').textContent = filtered.length;
    renderTable(filtered);
    }

    function renderTable(list) {
        console.log('Rendering users:', list.length);
    const tbody = document.getElementById('usersTbody');

    if (!list.length) {
        tbody.innerHTML = `
        <tr>
            <td colspan="5" class="text-center py-4 ua-muted">Sin usuarios para mostrar</td>
        </tr>
        `;
        return;
    }

    tbody.innerHTML = list.map(u => {
        const statusBadge = u.is_active
        ? `<span class="ua-pill ua-pill--success"><i class="bi bi-check-circle"></i>Activo</span>`
        : `<span class="ua-pill ua-pill--danger"><i class="bi bi-x-circle"></i>Inactivo</span>`;

        const role = u.role
        ? `<span class="ua-pill"><i class="bi bi-shield"></i>${escapeHtml(u.role)}</span>`
        : `<span class="ua-muted">—</span>`;

        const btnRole = can('users.assign')
        ? `<button class="btn btn-sm btn-warning ua-iconbtn" data-action="role" data-id="${u.id}" title="Cambiar rol">
                <i class="bi bi-shield-lock"></i>Rol
            </button>`
        : '';

        const btnReset = can('users.reset')
        ? `<button class="btn btn-sm btn-outline-info ua-iconbtn" data-action="reset" data-id="${u.id}" title="Restablecer contraseña">
                <i class="bi bi-key"></i>Reset
            </button>`
        : '';

        const btnStatus = can('users.status')
        ? `<button class="btn btn-sm ${u.is_active ? 'btn-outline-danger' : 'btn-outline-success'} ua-iconbtn" data-action="status" data-id="${u.id}" title="Cambiar estado">
                <i class="bi ${u.is_active ? 'bi-person-x' : 'bi-person-check'}"></i>${u.is_active ? 'Desactivar' : 'Activar'}
            </button>`
        : '';

        return `
        <tr>
            <td>
                <div class="fw-bold">${escapeHtml(u.name)}</div>
                <div class="ua-muted small">ID #${escapeHtml(u.id)}</div>
            </td>
            <td>
                <div class="fw-semibold">${escapeHtml(u.email)}</div>
            </td>
            <td>${role}</td>
            <td>${statusBadge}</td>
            <td class="ua-actions-cell">
                <div class="ua-actions">
                ${btnRole}
                ${btnReset}
                ${btnStatus}
            </div>
            </td>
        </tr>
        `;
    }).join('');
    }

    async function createUser() {
        clearCreateErrors();
        const form = document.getElementById('frmCreate');
        const fd = new FormData(form);
        setLoading('btnCreateSubmit', 'spCreate', true);

        try {
            const data = await fetchJson(API.usersStore, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: fd.get('name'),
                    email: fd.get('email'),
                    role: fd.get('role'),
                }),
            });

            closeModal('modalCreateUser');
            showAlert('success', `Usuario creado. Contraseña: ${data.password}`);
            form.reset();
            await loadUsers();
        } catch (e) {
            if (e.status === 422) {
                showCreateErrors(e.data.errors || {});
                return;
            }
            showAlert('danger', e.message);
        } finally {
            setLoading('btnCreateSubmit', 'spCreate', false);
        }
    }

    function toggleStatus(userId) {
    const u = USERS.find(x => x.id == userId);
    if (!u) return;

    confirmAction({
        title: 'Cambiar estado',
        message: `¿Deseas ${u.is_active ? 'desactivar' : 'activar'} a ${u.name}?`,
        onConfirm: async () => {
        try {
            await fetchJson(API.userToggleStatus(userId), { method: 'PUT' });
            showAlert('success', 'Estado actualizado');
            await loadUsers();
        } catch (e) {
            showAlert('danger', e.message);
        }
        }
    });
    }

    function resetPassword(userId) {
    const u = USERS.find(x => x.id == userId);
    if (!u) return;

    confirmAction({
        title: 'Restablecer contraseña',
        message: `Se generará una nueva contraseña para ${u.email}. ¿Continuar?`,
        onConfirm: async () => {
        try {
            const data = await fetchJson(
            API.userResetPassword(userId),
            { method: 'PUT' }
            );

            // ✅ ALERTA GLOBAL (igual que activar/desactivar)
            showAlert(
            'success',
            `Contraseña restablecida para ${u.email}`
            );

            // Modal con la contraseña
            document.getElementById('resetPasswordValue').value = data.password;
            openModal('modalReset');

        } catch (e) {
            showAlert('danger', e.message);
        }
        }
    });
    }


    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('btnReload')?.addEventListener('click', loadUsers);
        document.getElementById('btnReloadBottom')?.addEventListener('click', loadUsers);

        document.getElementById('txtSearch')?.addEventListener('input', applyFiltersAndRender);
        document.getElementById('selStatus')?.addEventListener('change', applyFiltersAndRender);

        document.getElementById('btnOpenCreate')?.addEventListener('click', () => {
        new bootstrap.Modal(document.getElementById('modalCreate')).show();
        });

        document.getElementById('btnCreateSubmit')?.addEventListener('click', createUser);
        document.getElementById('btnRoleSubmit')?.addEventListener('click', saveRole);

        document.getElementById('btnCopyPassword')?.addEventListener('click', async () => {
            const input = document.getElementById('resetPasswordValue');
            const feedback = document.getElementById('copyFeedback');

            if (!input) return;

            try {
                await navigator.clipboard.writeText(input.value);

                feedback?.classList.remove('d-none');

                setTimeout(() => {
                    feedback?.classList.add('d-none');
                }, 2000);

            } catch {
                showAlert('danger', 'No se pudo copiar la contraseña');
            }
        });


        document.getElementById('usersTbody')?.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;

            const action = btn.getAttribute('data-action');
            const id = btn.getAttribute('data-id');

            if (action === 'status') return toggleStatus(id);
            if (action === 'role') return openRoleModal(id);
            if (action === 'reset') return resetPassword(id);
        });

        try {
            await loadRoles();
            await loadUsers();
        } catch (e) {
            showAlert('danger', e.message);
        }
    });

    let activeModalId = null;

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('is-open');
        activeModalId = id;
        // Focus al primer input
        const firstInput = modal.querySelector('input, select, textarea, button');
        firstInput?.focus();
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('is-open');
        activeModalId = null;
        document.body.style.overflow = '';

        if (id === 'modalReset') {
            document.getElementById('resetPasswordValue').value = '';
        }
    }

    /* ESC para cerrar */
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && activeModalId) {
            closeModal(activeModalId);
        }
    });

    /* Click fuera para cerrar */
    document.addEventListener('click', (e) => {
        const overlay = e.target.closest('[data-modal]');
        if (!overlay) return;
        if (e.target === overlay) {
            closeModal(overlay.id);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (!activeModalId || e.key !== 'Tab') return;
        const modal = document.getElementById(activeModalId);
        const focusable = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            last.focus();
            e.preventDefault();
        }
        if (!e.shiftKey && document.activeElement === last) {
            first.focus();
            e.preventDefault();
        }
    });

    function clearCreateErrors() {
        document.getElementById('createFormError')?.classList.add('d-none');

        document.querySelectorAll('#frmCreate .field-error').forEach(el => {
            el.textContent = '';
        });

        document.querySelectorAll('#frmCreate .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }

    function showCreateErrors(errors = {}) {
        document.getElementById('createFormError')?.classList.remove('d-none');

        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.querySelector(`#frmCreate [name="${field}"]`);
            const errorBox = document.querySelector(`#frmCreate [data-error-for="${field}"]`);

            if (input) input.classList.add('is-invalid');
            if (errorBox) errorBox.textContent = messages[0];
        });
    }

    function openRoleModal(userId) {
        const u = USERS.find(x => String(x.id) === String(userId));
        if (!u) return;

        selectedUserIdForRole = userId;

        document.getElementById('roleUserLabel').textContent =
            `${u.name} (${u.email})`;

        const sel = document.getElementById('roleSelect');
        sel.value = u.role || '';

        clearRoleErrors();
        openModal('modalRole');
    }

    async function saveRole() {
        clearRoleErrors();
        const role = document.getElementById('roleSelect').value;
        if (!role) {
            showRoleErrors({ role: ['Selecciona un rol'] });
            return;
        }

        setLoading('btnRoleSubmit', 'spRole', true);

        try {
            const data = await fetchJson(
                API.userUpdateRole(selectedUserIdForRole),
                {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ role })
                }
            );

            closeModal('modalRole');
            showAlert('success', data.message || 'Rol actualizado');
            await loadUsers();

        } catch (e) {
            if (e.status === 422) {
                showRoleErrors(e.data.errors || {});
                return;
            }

            document.getElementById('roleFormError')?.classList.remove('d-none');
        } finally {
            setLoading('btnRoleSubmit', 'spRole', false);
        }
    }

    function clearRoleErrors() {
        document.getElementById('roleFormError')?.classList.add('d-none');
        document.querySelectorAll('#frmRole .field-error').forEach(el => {
            el.textContent = '';
        });
        document.querySelectorAll('#frmRole .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }

    function showRoleErrors(errors = {}) {
        document.getElementById('roleFormError')?.classList.remove('d-none');
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.querySelector(`#frmRole [name="${field}"], #frmRole #roleSelect`);
            const errorBox = document.querySelector(`#frmRole [data-error-for="${field}"]`);

            if (input) input.classList.add('is-invalid');
            if (errorBox) errorBox.textContent = messages[0];
        });
    }

    let confirmCallback = null;

    function confirmAction({ title, message, onConfirm }) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        confirmCallback = onConfirm;
        openModal('modalConfirm');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const confirmBtn = document.getElementById('confirmAccept');
        if (!confirmBtn) {
            console.warn('[confirmAccept] no existe en DOM');
            return;
        }
        confirmBtn.addEventListener('click', () => {
            if (typeof confirmCallback === 'function') {
                confirmCallback();
            }
            closeModal('modalConfirm');
        });
    });

</script>
