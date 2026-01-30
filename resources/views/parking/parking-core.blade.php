@extends('layouts.app')

@section('content')

<div class="parking-core">

    <!-- OPERACIÓN (siempre en layout) -->
    <section id="parkingOperationPanel">

        <div class="operation-grid">
            @include('parking.partials.space-select')
            {{-- @include('parking.partials.ticket-checkout') --}}
        </div>

    </section>

</div>

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

    document.addEventListener('keydown', e=>{
        if(e.key === 'Escape') closeCheckout();
    });

    async function initCashPanel(){
        if(typeof loadBranches === 'function'){
            await loadBranches();
        }
    }

    async function checkCashSession(){
        try{
            const res = await fetch('/api/cash-sessions/current', {
                headers:{
                    'Authorization': 'Bearer {{ session("jwt_token") }}',
                    'Accept':'application/json'
                }
            });

            if(res.status === 404){
                lockSystem();
                return;
            }

            const data = await res.json();

            if(data.open === true){
                unlockSystem();
            }else{
                lockSystem();
            }

        }catch(e){
            lockSystem();
        }
    }

    function lockSystem(){
        const modal = document.getElementById('cashModalOverlay');
        if(modal) modal.style.display = 'flex';
    }

    function unlockSystem(){
        const modal = document.getElementById('cashModalOverlay');
        if(modal) modal.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', ()=>{
        checkCashSession();
    });

</script>

