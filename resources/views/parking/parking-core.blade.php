@extends('layouts.app')

@section('content')

<div class="parking-core">
    <!-- OPERACIÓN (siempre en layout) -->
    <section id="parkingOperationPanel">
        <div class="operation-grid">
            @include('parking.partials.space-select')
        </div>
    </section>
</div>

{{-- ALERT --}}
<div id="alertBox" class="ua-alert d-none"></div>

<!-- MODAL BLOQUEO CAJA -->
@include('parking.partials.check-out-modal')

<!-- MODAL BLOQUEO CAJA -->
@include('parking.partials.cash-modal')

@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkout = document.querySelector('.checkout-wrapper');
        if(checkout){
            checkout.classList.remove('collapsed');
            checkout.classList.add('expanded');
        }
    });

    async function initCashPanel(){
        if(typeof loadBranches === 'function'){
            await loadBranches();
        }
    }

    async function checkCashSession(){
        try{
            const res = await fetch('/api/cash-sessions/current', {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            /*
            |--------------------------------------------------------------------------
            | 🔐 JWT inválido o sesión no autorizada
            |--------------------------------------------------------------------------
            */
            if (res.status === 401 || res.status === 403) {

                // 🔥 Intentar cerrar sesión web limpiamente
                await fetch('/logout', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content')
                    }
                });

                // Redirigir a login
                window.location.href = '/login';
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 📦 No hay caja abierta
            |--------------------------------------------------------------------------
            */
            if(res.status === 404){
                await loadCashModalContent();
                lockSystem();
                return;
            }

            const data = await res.json();

            if(data.open === true){
                unlockSystem();
            }else{
                await loadCashModalContent();
                lockSystem();
            }

        }catch(e){
            console.error('Cash session validation error', e);

            // fallback seguro
            window.location.href = '/login';
        }
    }


    let activeModalId = null;

    function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        modal.classList.add('is-open');
        activeModalId = id;

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
    }

    function lockSystem(){
        const modal = document.getElementById('cashModalOverlay');
        openModal('cashModalOverlay');
    }

    function unlockSystem(){
        const modal = document.getElementById('cashModalOverlay');
        closeModal('cashModalOverlay');
    }

    document.addEventListener('DOMContentLoaded', ()=>{
        checkCashSession();
    });

    async function loadCashModalContent(){
        const body = document.querySelector('#cashModalOverlay .modal-body');

        body.innerHTML = `
            <div class="cash-empty-state">
                <div class="cash-alert">
                    <div class="cash-alert-icon">🔒</div>
                    <div>
                        <h4>Sistema bloqueado</h4>
                        <p>Debes abrir una sesión de caja antes de comenzar a operar.</p>
                    </div>
                </div>
                <div class="cash-open-card">
                    <label class="cash-label">Monto inicial</label>
                    <div class="cash-input-wrap">
                        <span class="currency">$</span>
                        <input
                            type="number"
                            id="openingAmount"
                            class="cash-input"
                            placeholder="0.00"
                            min="0"
                            step="0.01">
                    </div>
                    <button
                        class="cash-open-btn"
                        onclick="openCashSession()">
                        <i class="fas fa-unlock me-2"></i>
                        Abrir Caja
                    </button>
                </div>
            </div>
        `;
    }

    async function openCashSession(){
        const amount = document.getElementById('openingAmount').value;

        if(!amount || parseFloat(amount) <= 0){
            alert('Debes ingresar un monto inicial válido');
            return;
        }

        try{
            const res = await fetch('/api/cash-sessions/open', {
                method: 'POST',
                credentials: 'same-origin',
                headers:{
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    opening_amount: amount
                })
            });

            console.log(res);

            if(!res.ok){
                const err = await res.json();
                alert(err.message || 'Error al abrir caja');
                return;
            }

            unlockSystem();
            await checkCashSession();

        }catch(e){
            alert('Error de conexión');
        }
    }

    function showAlert(type, message) {
        const box = document.getElementById('alertBox');
        box.className = `alert ua-alert alert-${type}`;
        box.textContent = message;
        box.classList.remove('d-none');

        setTimeout(() => box.classList.add('d-none'), 4500);
    }
</script>
