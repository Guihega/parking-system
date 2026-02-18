
    console.log('roles.js cargado');

    let PERMISSIONS_MAP = {};
    let currentRoleId = null;

    let ORIGINAL_PERMISSIONS = new Set();   // snapshot al cargar el rol
    let CURRENT_PERMISSIONS  = new Set();  // estado actual en UI

    function can(permission) {
        return Array.isArray(window.__UI_PERMS__) &&
            window.__UI_PERMS__.includes(permission);
    }

    // Si no puede asignar, removemos el formulario
    //const canAssign = can('roles.assign');

    async function loadRoles() {
        try {
            const res = await fetch('/api/roles');
            if (!res.ok) throw new Error('HTTP error');

            const data = await res.json();
            console.log(data);

            const list = document.getElementById('rolesList');

            if (!list) {
                console.error('rolesList NO existe en el DOM');
                return;
            }

            list.innerHTML = '';

            data.roles.forEach(role => {
                const li = document.createElement('li');
                li.className = `
                    px-4 py-3 rounded-xl
                    cursor-pointer
                    transition-all duration-200
                    bg-slate-900
                    hover:bg-slate-800
                    flex items-center justify-between
                `;
                li.innerHTML = `
                    <span class="text-xs text-slate-500">Rol</span>
                `;

                li.textContent = role.name;

                li.onclick = () => {
                    document.querySelectorAll('#rolesList li').forEach(el => {
                        el.classList.remove('bg-slate-800', 'ring-1', 'ring-indigo-500/40');
                    });

                    li.classList.add(
                        'bg-slate-800',
                        'ring-1',
                        'ring-indigo-500/40',
                        'relative'
                    );

                    loadPermissions(role.id, role.name);
                };

                list.appendChild(li);
            });

        } catch (e) {
            console.error(e);
            alert('Error al cargar roles');
        }
    }


    async function loadPermissions(roleId, roleName) {
        currentRoleId = roleId;
        document.getElementById('roleLabel').textContent =
            `Editando permisos del rol: ${roleName}`;

        try {
            const res = await fetch(`/api/roles/${roleId}/permissions`);
            if (!res.ok) throw new Error();

            const payload = await res.json();
            console.log(payload);

            const { data } = payload;

            const container = document.getElementById('permissionsContainer');
            container.innerHTML = '';

            Object.entries(data).forEach(([module, permissions]) => {

                const assignedCount = permissions.filter(p => p.assigned).length;
                    const statusColor =
                            assignedCount === 0
                                ? 'bg-slate-500'
                                : 'bg-indigo-400';

                const wrapper = document.createElement('div');
                wrapper.className = `
                    bg-slate-800/40
                    border border-slate-700
                    rounded-xl
                    overflow-hidden
                `;

                if (assignedCount === 0) {
                    wrapper.classList.add('opacity-60');
                }

                const header = document.createElement('button');

                header.style.background = 'linear-gradient(90deg, #020617, #020617, #020617)'; // slate-950 aprox

                header.type = 'button';
                header.className = `
                      w-full flex items-center justify-between
                        px-5 py-3
                        text-left
                        bg-gradient-to-r
                        from-slate-900 via-slate-900 to-slate-800
                        border-b border-slate-700
                        transition-all duration-300
                `;

                header.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="w-1 h-8 rounded-full ${statusColor} flex-shrink-0"></div>

                        <div>
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-white">
                                ${module}
                            </h4>
                            <span class="text-xs text-slate-400">
                                ${assignedCount} / ${permissions.length} activos
                            </span>
                        </div>
                    </div>

                    <svg
                        class="w-5 h-5 text-slate-400 transform transition-transform duration-300"
                        data-chevron
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7"/>
                    </svg>
                `;

                const body = document.createElement('div');
                body.className = `
                    px-4 pb-4
                    grid grid-cols-1 md:grid-cols-2 gap-4
                `;
                body.classList.add('permission-body');
                let open = false;
                body.style.display = 'none';

                permissions.forEach(p => {
                    body.innerHTML += `
                        <label class="
                            group flex items-start gap-3
                            p-3 rounded-lg
                            border border-slate-700
                            hover:border-indigo-500/50
                            transition
                        ">
                            <input
                                type="checkbox"
                                class="mt-1 permission-checkbox"
                                value="${p.id}"
                                ${p.assigned ? 'checked' : ''}
                                ${can('roles.assign') ? '' : 'disabled'}
                            />

                            <div>
                                <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-900 text-indigo-400">
                                    ${p.code}
                                </span>
                                <p class="text-sm text-slate-300 mt-1">
                                    ${p.name}
                                </p>
                            </div>
                        </label>
                    `;
                });

                header.addEventListener('click', () => {
                    open = !open;
                    body.style.display = open ? 'grid' : 'none';

                    const chevron = header.querySelector('[data-chevron]');
                    chevron.classList.toggle('rotate-180', open);
                    chevron.classList.toggle('text-indigo-400', open);
                    chevron.classList.toggle('text-slate-400', !open);

                    if (open) {
                        // 🔥 estado EXPANDIDO
                        header.style.background =
                        'linear-gradient(90deg, #1e293b, #334155, #475569)'; // slate-800 → 600
                        header.style.boxShadow =
                        'inset 0 0 0 1px rgba(99,102,241,.45)';
                        wrapper.style.boxShadow = '-4px 0 0 rgb(99,102,241)';
                        wrapper.style.transition = 'box-shadow 200ms ease';
                    } else {
                        // 🔹 estado BASE
                        header.style.background =
                        'linear-gradient(90deg, #020617, #020617, #020617)';
                        header.style.boxShadow = 'none';
                        wrapper.style.boxShadow = 'none';
                    }
                });


                wrapper.appendChild(header);
                wrapper.appendChild(body);
                container.appendChild(wrapper);
            });

            // ✅ al final de loadPermissions()
            ORIGINAL_PERMISSIONS = new Set(
                [...document.querySelectorAll('#permissionsContainer .permission-checkbox')]
                    .filter(cb => cb.checked)
                    .map(cb => Number(cb.value))
            );

            CURRENT_PERMISSIONS = new Set(ORIGINAL_PERMISSIONS);
            updateSaveButtonState();

            loadAudit(roleId);
        } catch {
            alert('Error al cargar permisos');
        }
    }

    const form = document.getElementById('permissionsForm');
    if (form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            if (!currentRoleId) {
                alert('Selecciona un rol primero');
                return;
            }

            const permissions = [...document.querySelectorAll(
                '#permissionsContainer input:checked'
            )].map(i => parseInt(i.value));

            try {
                const res = await fetch(
                    `/admin/roles/${currentRoleId}/permissions`,
                    {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .content
                        },
                        body: JSON.stringify({ permissions })
                    }
                );
                console.log(res);
                //if (!res.ok) throw new Error();

                showAlert('success', 'Permisos actualizados correctamente');

                // 🔥 RESET del baseline
                ORIGINAL_PERMISSIONS = new Set(
                    [...document.querySelectorAll('#permissionsContainer .permission-checkbox')]
                        .filter(cb => cb.checked)
                        .map(cb => Number(cb.value))
                );

                CURRENT_PERMISSIONS = new Set(ORIGINAL_PERMISSIONS);

                // 🔄 Re-evaluar botón
                updateSaveButtonState();

                // 🔄 SI EL ROL EDITADO ES DEL USUARIO ACTUAL → recargar
                if (
                    window.__CURRENT_ROLE_ID__ &&
                    currentRoleId === window.__CURRENT_ROLE_ID__
                ) {
                    location.reload();
                }
            } catch {
                //alert('No se pudieron guardar los permisos');
                showAlert('error', 'No se pudieron guardar los permisos');
            }
        });
    }

    async function loadAudit(roleId) {
        const container = document.getElementById('auditContainer');
        if (!container) return;

        container.innerHTML = '<p class="text-gray-500">Cargando historial…</p>';

        try {
            const res = await fetch(`/api/roles/${roleId}/audit`);
            if (!res.ok) throw new Error();

            const { audit } = await res.json();

            if (!audit.length) {
                container.innerHTML =
                    '<p class="text-gray-500">No hay cambios registrados.</p>';
                return;
            }

            container.innerHTML = audit.map(log => {
                const beforeIds = JSON.parse(log.permissions_before || '[]').map(Number);
                const afterIds  = JSON.parse(log.permissions_after || '[]').map(Number);

                const beforeSet = new Set(beforeIds);
                const afterSet  = new Set(afterIds);

                const added   = afterIds.filter(id => !beforeSet.has(id));
                const removed = beforeIds.filter(id => !afterSet.has(id));
                const same    = afterIds.filter(id => beforeSet.has(id));

                const render = (ids, cls) => {
                    if (!ids.length) return '<span class="text-gray-500">—</span>';
                    return ids.map(id => `
                        <div class="${cls} text-xs">
                            ${PERMISSIONS_MAP[id] || `#${id}`}
                        </div>
                    `).join('');
                };

                return `
                    <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
                        <div class="flex justify-between text-xs text-gray-400 mb-3">
                            <span>${log.actor_name} (${log.actor_email})</span>
                            <span>${log.created_at}</span>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="font-semibold text-gray-400 mb-1">Agregados</p>
                                ${render(added, 'text-emerald-400')}
                            </div>

                            <div>
                                <p class="font-semibold text-gray-400 mb-1">Removidos</p>
                                ${render(removed, 'text-red-400 line-through')}
                            </div>

                            <div>
                                <p class="font-semibold text-gray-400 mb-1">Sin cambio</p>
                                ${render(same, 'text-gray-300')}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

        } catch {
            container.innerHTML =
                '<p class="text-red-400">No se pudo cargar el historial.</p>';
        }
    }

    function diffPermissions(beforeIds = [], afterIds = []) {
        const before = new Set(beforeIds.map(Number));
        const after  = new Set(afterIds.map(Number));

        const added   = [...after].filter(id => !before.has(id));
        const removed = [...before].filter(id => !after.has(id));
        const same    = [...after].filter(id => before.has(id));

        return { added, removed, same };
    }

    function renderPermissionList(ids, style = 'neutral') {
        if (!ids.length) return '<span class="text-gray-500">—</span>';

        const cls = {
            added:   'text-emerald-400',
            removed: 'text-red-400 line-through',
            neutral: 'text-gray-300'
        }[style];

        return ids.map(id => `
            <div class="${cls} text-xs">
                ${PERMISSIONS_MAP[id] || `#${id}`}
            </div>
        `).join('');
    }

    function computeDiff() {
        const before = ORIGINAL_PERMISSIONS;
        const after  = CURRENT_PERMISSIONS;

        const added   = [...after].filter(id => !before.has(id));
        const removed = [...before].filter(id => !after.has(id));

        return { added, removed };
    }

    function updateSaveButtonState() {
        const btn = document.querySelector('#permissionsForm button[type="submit"]');
        const summary = document.getElementById('changesSummary');
        if (!btn || !summary) return;

        if (!can('roles.assign')) {
            btn.disabled = true;
            summary.classList.add('hidden');
            return;
        }

        const { added, removed } = computeDiff();
        const hasChanges = added.length || removed.length;

        btn.disabled = !hasChanges;

        if (!hasChanges) {
            summary.classList.add('hidden');
            return;
        }

        summary.classList.remove('hidden');
        summary.innerHTML = `
            <div class="flex gap-6">
                <span class="text-emerald-400">+ ${added.length} agregados</span>
                <span class="text-red-400">− ${removed.length} removidos</span>
            </div>
        `;
    }



    // ===============================
    // 11.3 — Sync CURRENT_PERMISSIONS
    // ===============================
    const permissionsContainer = document.getElementById('permissionsContainer');

    if (permissionsContainer) {
        permissionsContainer.addEventListener('change', e => {
            if (!e.target.classList.contains('permission-checkbox')) return;

            const label = e.target.closest('label');
            label.classList.toggle('bg-indigo-500/5', e.target.checked);

            const id = Number(e.target.value);

            if (e.target.checked) {
                CURRENT_PERMISSIONS.add(id);
            } else {
                CURRENT_PERMISSIONS.delete(id);
            }

            updateSaveButtonState();
        });
    }

/*         async function loadPermissionsCatalog() {
        const res = await fetch('/api/permissions');
        if (!res.ok) throw new Error();

        const { permissions } = await res.json();

        permissions.forEach(p => {
            PERMISSIONS_MAP[p.id] = `${p.code}`;
        });
    }

    await loadPermissionsCatalog(); */
    window.addEventListener('load', () => {
        const rolesList = document.getElementById('rolesList');
        if (!rolesList) return;

        loadRoles();
    });

    function showAlert(type, message) {
        const box = document.getElementById('alertBox');
        if (!box) return;

        box.className = `ua-alert alert-${type}`;
        box.textContent = message;
        box.classList.remove('d-none');

        clearTimeout(box._timer);

        box._timer = setTimeout(() => {
            box.classList.add('d-none');
        }, 4500);
    }

console.log('UI PERMS:', window.__UI_PERMS__);
console.log('CAN ASSIGN:', can('roles.assign'));
