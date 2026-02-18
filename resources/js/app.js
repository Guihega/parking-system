import './bootstrap';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Alpine from 'alpinejs';
import './admin/roles';

window.Alpine = Alpine;

Alpine.start();


// ========================================
// 🔐 Global Idle Session Timeout
// ========================================

// Solo activar si existe meta csrf (usuario autenticado)
if (document.querySelector('meta[name="csrf-token"]')) {

    let idleTimer;
    const MAX_IDLE_TIME = 15 * 60 * 1000; // 15 minutos

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(handleIdleLogout, MAX_IDLE_TIME);
    }

    async function handleIdleLogout() {
        try {
            await fetch('/logout', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content')
                }
            });
        } catch (e) {
            console.error('Idle logout failed:', e);
        }

        window.location.href = '/login';
    }

    ['click','mousemove','keydown','scroll','touchstart']
        .forEach(event => {
            window.addEventListener(event, resetIdleTimer);
        });

    resetIdleTimer();
}
