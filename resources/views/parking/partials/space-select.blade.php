<div class="parking-wrapper">
    <div class="parking-header">
        <h2>Asignar cajón de estacionamiento</h2>
        <span>Selecciona un espacio disponible en el plano</span>
    </div>
    <select id="branchSelectSpace" class="form-control mb-3">
        <option value="">Selecciona sucursal</option>
    </select>
    <button id="openCheckoutModal" class="search-ticket-btn" onclick="openCheckout()">
        🔍 Buscar ticket / Cobrar
    </button>
    {{-- <div id="spacesGrid" class="grid"></div> --}}
    <div id="parkingMap" class="parking-map"></div>
    <div class="glass-card vehicle-form mt-3">
        <div class="vehicle-grid">
            <div class="vehicle-field">
                <label>Placa</label>
                <input id="plateInput" placeholder="Ej. ABC-123">
            </div>
            <div class="vehicle-field">
                <label>Tipo de vehículo</label>
                <select id="vehicleTypeSelect" class="vehicle-select">
                    <option value="">Tipo de vehículo</option>
                </select>
            </div>
        </div>
    </div>
    <button id="createTicketBtn" class="btn btn-primary mt-3" disabled>
        Registrar entrada
    </button>
    <div id="selectedLabel">Ningún cajón seleccionado</div>
</div>
<script>
    function validateEntryForm(){
        const hasSpace   = selectedSpaceId !== null;
        const hasPlate   = plateInput.value.trim().length >= 3;
        const hasVehicle = vehicleTypeSelect.value !== '';

        createBtn.disabled = !(hasSpace && hasPlate && hasVehicle);
    }

    plateInput.addEventListener('input', validateEntryForm);
    vehicleTypeSelect.addEventListener('change', validateEntryForm);


    let selectedSpaceId = null;
    const createBtn = document.getElementById('createTicketBtn');
    const branchSelectSpace = document.getElementById('branchSelectSpace');

    document.getElementById('openCheckoutModal').onclick = openCheckout;


    function openCheckout(){
        document.getElementById('checkoutModalOverlay').style.display = 'flex';
    }

    function closeCheckout(){
        document.getElementById('checkoutModalOverlay').style.display = 'none';
    }


    branchSelectSpace.addEventListener('change', e => {
        selectedSpaceId = null;
        plateInput.value = '';
        vehicleTypeSelect.value = '';
        validateEntryForm();

        const branchId = e.target.value;

        if(!branchId){
            document.getElementById('parkingMap').innerHTML = '';
            createBtn.disabled = true;
            selectedSpaceId = null;
            document.getElementById('selectedLabel').textContent = 'Ningún cajón seleccionado';
            return;
        }

        loadSpaces(branchId);
    });


    /* ============================
    Fetch robusto (JSON + errores)
    ============================= */
    async function apiFetch(url, options = {}) {

        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...(options.headers || {})
            },
            ...options
        });

        const contentType = response.headers.get('content-type') || '';
        let data;

        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            data = { status: 'error', message: await response.text() };
        }

        if (!response.ok) {
            throw data;
        }

        return data;
    }

    /* ============================
    Cargar cajones disponibles
    ============================= */
    async function loadSpaces(branchId) {
        try {
            const result = await apiFetch(
                `/api/parking-spaces?branch_id=${branchId}&status=available`
            );

            if (result.status !== 'success') {
                alert(result.message || 'No fue posible cargar cajones');
                return;
            }

            renderSpaces(result.data);
        } catch (err) {
            alert(err.message || 'Error al consultar cajones');
            console.error(err);
        }
    }

    /* ============================
    Render cajones
    ============================= */
    function renderSpaces(spaces){
        const map = document.getElementById('parkingMap');
        map.innerHTML = '';
        const zones = {};

        spaces.forEach(s=>{
            const zone = s.code.split('-')[0];
            if(!zones[zone]) zones[zone]=[];
            zones[zone].push(s);
        });

        Object.entries(zones).forEach(([zone,items])=>{
            const zoneDiv = document.createElement('div');
            zoneDiv.className = 'zone';
            zoneDiv.innerHTML = `
                <div class="zone-title">Zona ${zone}</div>
            `;

            // dividir en filas realistas (3 por lado)
            for(let i=0;i<items.length;i+=6){
                const left = items.slice(i,i+3);
                const right = items.slice(i+3,i+6);
                const row = document.createElement('div');
                row.className = 'parking-row';
                const leftGroup = document.createElement('div');
                leftGroup.className = 'slot-group spaces-grid';
                const rightGroup = document.createElement('div');
                rightGroup.className = 'slot-group spaces-grid';

                left.forEach(space=>{
                    leftGroup.appendChild(createSlot(space));
                });

                right.forEach(space=>{
                    rightGroup.appendChild(createSlot(space));
                });

                row.innerHTML = `<div class="parking-lane"></div>`;
                row.prepend(leftGroup);
                row.append(rightGroup);
                zoneDiv.appendChild(row);
            }

            map.appendChild(zoneDiv);
        });
    }

    function createSlot(space){
        const slot = document.createElement('div');
        slot.className = 'space-slot';
        slot.innerHTML = `
            <i class="fa-solid fa-car"></i>
            <span>${space.code}</span>
        `;
        slot.onclick = ()=>selectSpace(slot,space.id,space.code);
        return slot;
    }


    /* ============================
    Selección visual
    ============================= */
    function selectSpace(el,id,code){
        document.querySelectorAll('.space-slot')
            .forEach(x => x.classList.remove('selected'));

        el.classList.add('selected');
        selectedSpaceId = id;

        document.getElementById('selectedLabel')
            .textContent = `Cajón seleccionado: ${code}`;

        validateEntryForm();   // 👈 activa o no el botón según todo
    }



    /* ============================
    Registrar ticket
    ============================= */
    createBtn.onclick = async () => {
        if(createBtn.disabled) return;

        const plate = plateInput.value.trim();
        const type  = vehicleTypeSelect.value;
        createBtn.disabled = true;

        try {
            const result = await apiFetch('/api/tickets/entry', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    plate: plate,
                    vehicle_type_id: type,
                    parking_space_id: selectedSpaceId
                })
            });

            alert(`Ticket creado: ${result.ticket.folio}`);

            // 🔄 reset real
            selectedSpaceId = null;
            plateInput.value = '';
            vehicleTypeSelect.value = '';
            createBtn.disabled = true;
            document.getElementById('selectedLabel')
                .textContent = 'Ningún cajón seleccionado';
            loadSpaces(branchSelectSpace.value);
        } catch (err) {
            alert(err.message || 'Error al registrar entrada');
            console.error(err);
        }
    };

    async function loadBranches(){
        try{
            const result = await apiFetch('/api/branches');

            if(result.status !== 'success' || !result.branches.length){
                alert('No se pudieron cargar sucursales');
                return;
            }

            branchSelectSpace.innerHTML = '';

            result.branches.forEach((b,index)=>{
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = `${b.name} (${b.location})`;
                branchSelectSpace.appendChild(opt);

                // 👉 seleccionar la primera automáticamente
                if(index === 0){
                    branchSelectSpace.value = b.id;
                }
            });

            // 👉 cargar cajones de la sucursal por defecto
            loadSpaces(branchSelectSpace.value);

        }catch(err){
            alert(err.message || 'Error cargando sucursales');
            console.error(err);
        }
    }

    function openCheckout(){
        const modal = document.getElementById('checkoutModalOverlay');
        modal.style.display = 'flex';   // o block según tu CSS
        resetCheckoutModal();
        // pequeño delay para asegurar render
        setTimeout(() => {
            tokenInput.focus();
        }, 50);
    }

    async function loadVehicleTypes(){
        try{
            const result = await apiFetch('/api/vehicle-types');

            if(result.status !== 'success'){
                alert('No se pudieron cargar tipos de vehículo');
                return;
            }

            const select = document.getElementById('vehicleTypeSelect');
            select.innerHTML = '<option value="">Tipo de vehículo</option>';

            result.data.forEach(v=>{
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.name;
                select.appendChild(opt);
            });

        }catch(err){
            console.error(err);
            alert('Error cargando tipos de vehículo');
        }
    }

    /* ============================
    Inicial
    ============================= */
    loadVehicleTypes();
    loadBranches();
</script>
