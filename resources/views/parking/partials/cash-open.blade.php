<div class="parking-wrapper">
    <div class="parking-header">
        <h2>Apertura de caja</h2>
        <span>Selecciona sucursal y registra el fondo inicial</span>
    </div>
    <div class="form-block">
        <div class="form-group">
            <label>Sucursal</label>
            <select id="branchSelectCash" class="ui-select"></select>
        </div>
        <div class="form-group">
            <label>Monto inicial <span class="currency">$</span></label>
            <div class="number-wrapper">
                <button type="button" class="num-btn"
                    @click="
                        let v = parseFloat(opening_amount || 0);
                        v = v - 1;
                        opening_amount = Math.max(0, v).toFixed(2);
                    ">
                    −
                </button>
                <input
                    id="openingAmount"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    class="control-field amount-input clean-number"
                >
                <button type="button" class="num-btn"
                    @click="
                        let v = parseFloat(opening_amount || 0);
                        v = v + 1;
                        opening_amount = v.toFixed(2);
                    ">
                    +
                </button>
            </div>
        </div>
        <button id="openCashBtn">
            Abrir caja
        </button>
        <div id="errorBox" class="error-box" style="display:none"></div>
    </div>
</div>
<script>
    const amountInput = document.getElementById('openingAmount');
    const openBtn = document.getElementById('openCashBtn');
    const errorBox = document.getElementById('errorBox');

    let loading = false;
    async function checkCashOpen(){
        try{
            const res = await fetch('/api/cash-sessions/current', {
                headers:{
                    'Authorization': 'Bearer {{ session("jwt_token") }}',
                    'Accept':'application/json'
                }
            });

            const data = await res.json();

            if(data.status !== 'success') return;

            if(data.open === true){
                //window.location.href = "{{ route('parking.select.space') }}";
                activateOperation();
            }

        }catch(e){
            console.error('Error validando caja', e);
        }
    }

    /* ============================
    Cargar sucursales
    ============================= */
    async function loadBranches() {
        const branchSelectCash = document.getElementById('branchSelectCash');
        if (!branchSelectCash) return;

        try {
            const res = await fetch('/api/branches', {
                headers: {
                    'Authorization': 'Bearer {{ session("jwt_token") }}',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            const { status, branches } = await res.json();

            if (status !== 'success' || !Array.isArray(branches) || branches.length === 0) {
                throw new Error('Sin sucursales');
            }

            branchSelectCash.innerHTML =
                '<option value="">Selecciona sucursal</option>';

            branches.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.location
                    ? `${b.name} (${b.location})`
                    : b.name;

                branchSelectCash.appendChild(opt);
            });

        } catch (e) {
            console.error('loadBranches:', e);
            showError('No se pudieron cargar sucursales');
        }
    }





    /* ============================
    Abrir caja
    ============================= */
    async function openCash(){

        if(loading) return;

        hideError();
        loading = true;
        openBtn.disabled = true;

        try{
            const res = await fetch('{{ route('cash.open') }}', {
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body:JSON.stringify({
                    //branch_id: branchSelectCash.value,
                    branch_id: document.getElementById('branchSelectCash').value,
                    opening_amount: amountInput.value
                })
            });

            const data = await res.json();

            if(!res.ok) throw new Error(data.message || 'Error al abrir caja');

            window.location.href = "{{ route('parking.select.space') }}";

        }catch(e){
            showError(e.message);
        }finally{
            loading = false;
            openBtn.disabled = false;
        }
    }

    /* ============================
    Helpers UI
    ============================= */
    function showError(msg){
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }

    function hideError(){
        errorBox.style.display = 'none';
    }

    /* ============================
    Eventos
    ============================= */
    openBtn.addEventListener('click', openCash);

    /* ============================
    Init
    ============================= */
    openBtn.addEventListener('click', openCash);
    document.addEventListener('DOMContentLoaded', loadBranches);
</script>

