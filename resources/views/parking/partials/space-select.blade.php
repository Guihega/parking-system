<div class="container-fluid ua-page">

    <!-- HEADER -->
    <div class="ua-header">
        <div>
            <h3 class="ua-title">
                Asignar cajón de estacionamiento
            </h3>
            <p class="ua-subtitle">
                Selecciona un espacio disponible en el plano
            </p>
        </div>

        <div class="ua-toolbar">
            <button id="openCheckoutModal"
                class="btn-primary-action"
                onclick="openCheckout()">
                🔍 Buscar ticket / Cobrar
            </button>
        </div>
    </div>

    <!-- SELECT SUCURSAL -->
    <div class="ua-card mb-4">
        <div class="card-body">
            <select id="branchSelectSpace" class="ui-select">
                <option value="">Selecciona sucursal</option>
            </select>
        </div>
    </div>

    <!-- MAPA -->
    <div class="ua-card mb-4">
        <div class="card-body">
            <div id="parkingMap" class="parking-map"></div>
        </div>
    </div>

    <!-- REGISTRO ENTRADA -->
    <div class="ua-card">
        <div class="card-body">
            <div id="selectedLabel" class="ua-pill mb-3">
                Ningún cajón seleccionado
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Placa</label>
                    <input id="plateInput"
                        class="ua-input"
                        placeholder="Ej. ABC-123" maxlength="10">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de vehículo</label>
                    <select id="vehicleTypeSelect"
                        class="ui-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button id="createTicketBtn"
                        class="btn-primary-action"
                        disabled>
                        Registrar entrada
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================
     MODAL TICKET
============================ -->
<div id="ticketModalOverlay" class="ua-modal-overlay" style="display:none;">
    <div id="printTicketTemplate" style="display:none;">
        <div class="print-ticket">
            <h3>ParkEasy</h3>
            <p id="printFolio"></p>
            <p id="printPlate"></p>
            <p id="printEntry"></p>
            <div id="printQR"></div>
        </div>
    </div>
    <div class="ticket-modal-card">
        <!-- HEADER -->
        <div class="ticket-header">
            <div>
                <h3 class="ticket-title">Ticket de ingreso</h3>
                <p class="ticket-subtitle">Conserve este ticket para registrar su salida</p>
            </div>
            <button class="ticket-close" onclick="openModalTicket('ticketModalOverlay')">✕</button>
        </div>
        <!-- BODY -->
        <div class="ticket-body">
            <div class="ticket-folio" id="ticketFolio">
                T-XXXXXXXX
            </div>
            <div class="ticket-info">
                <div>
                    <span class="ticket-label">Placa</span>
                    <span id="ticketPlate" class="ticket-value">ABC-123</span>
                </div>
                <div>
                    <span class="ticket-label">Ingreso</span>
                    <span id="ticketEntryTime" class="ticket-value">--</span>
                </div>
            </div>
            <div class="ticket-qr-wrapper">
                <div id="qrCode"></div>
            </div>
        </div>
        <!-- FOOTER -->
        <div class="ticket-footer">
            <button class="btn-secondary-action" onclick="closeTicketModal()">
                Cerrar
            </button>
            <button class="btn-primary-action" onclick="printTicket()">
                🖨 Imprimir
            </button>
        </div>
    </div>
</div>

<script>

    /* ============================
    ELEMENTOS
    ============================ */
    const plateInput         = document.getElementById('plateInput');
    const vehicleTypeSelect  = document.getElementById('vehicleTypeSelect');
    const createBtn          = document.getElementById('createTicketBtn');
    const branchSelectSpace  = document.getElementById('branchSelectSpace');
    let selectedSpaceId = null;
    const PLATE_REGEX = /^[A-Z0-9-]{4,10}$/;

    function validateEntryForm(){
        const plate = plateInput.value.trim();
        const plateRegex = /^[A-Z0-9-]{4,10}$/;
        const hasSpace   = selectedSpaceId !== null;
        const hasVehicle = vehicleTypeSelect.value !== '';
        const validPlate = plateRegex.test(plate);
        plateInput.classList.remove('invalid','valid');

        if(plate.length > 0){
            plateInput.classList.add(validPlate ? 'valid' : 'invalid');
        }

        createBtn.disabled = !(hasSpace && hasVehicle && validPlate);
    }

    plateInput.addEventListener('input', e => {
        let value = e.target.value
            .toUpperCase()
            .replace(/[^A-Z0-9-]/g, '')
            .substring(0, 10); // 🔐 límite real BD

        plateInput.value = value;

        validateEntryForm();
    });

    vehicleTypeSelect.addEventListener('change', validateEntryForm);
    document.getElementById('openCheckoutModal').onclick = openCheckout;

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
       FETCH ROBUSTO
    ============================ */
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
                showAlert('error', `No fue posible cargar cajones`);
                return;
            }

            renderSpaces(result.data);
        } catch (err) {
            showAlert('error', `No fue posible cargar cajones`);
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
        document.querySelectorAll('.space-slot').forEach(x => x.classList.remove('selected'));
        el.classList.add('selected');
        selectedSpaceId = id;
        document.getElementById('selectedLabel').textContent = `Cajón seleccionado: ${code}`;
        validateEntryForm();
    }

    /* ============================
    Registrar ticket
    ============================= */
    createBtn.onclick = async () => {
        if(createBtn.disabled) return;
        const plate = plateInput.value.trim();
        const type  = vehicleTypeSelect.value;
        createBtn.disabled = true;
        createBtn.innerHTML = 'Registrando...';

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

            if(result.status !== 'success'){
                throw result;
            }

            showTicketModal(result.ticket);
            resetEntryForm();
            loadSpaces(branchSelectSpace.value);
        } catch (err) {
            showAlert('error', err.message || 'Error al registrar entrada');
        } finally {
            createBtn.innerHTML = 'Registrar entrada';
            validateEntryForm();
        }
    };


    function resetEntryForm(){
        selectedSpaceId = null;
        plateInput.value = '';
        vehicleTypeSelect.value = '';
        document.getElementById('selectedLabel').textContent = 'Ningún cajón seleccionado';
        document.querySelectorAll('.space-slot').forEach(x => x.classList.remove('selected'));
    }

    function showTicketModal(ticket) {
        document.getElementById('ticketFolio').innerText = ticket.folio;
        document.getElementById('ticketPlate').innerText = ticket.plate;
        document.getElementById('ticketEntryTime').innerText = ticket.entry_time;
        generateQR(ticket.token);
        document.getElementById('ticketModalOverlay').style.display = 'flex';
        //openModal('ticketModalOverlay');
    }

    function generateQR(token){
        const qrContainer = document.getElementById("qrCode");
        qrContainer.innerHTML = "";
        new QRCode(qrContainer, {
            text: token,
            width: 160,
            height: 160,
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    async function loadBranches()
    {
        try{
            const response = await apiFetch('/api/branches');

            if(
                !response ||
                response.status !== 'success' ||
                !Array.isArray(response.branches) ||
                response.branches.length === 0
            ){
                showAlert('error', 'No se pudieron cargar sucursales');
                return;
            }

            const branches = response.branches;
            branchSelectSpace.innerHTML = '';
            branches.forEach((b, index) => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.location
                    ? `${b.name} (${b.location})`
                    : b.name;
                branchSelectSpace.appendChild(opt);
                if(index === 0){
                    branchSelectSpace.value = b.id;
                }
            });
            loadSpaces(branchSelectSpace.value);
        }catch(err){
            showAlert('error', err.message || 'Error cargando sucursales');
        }
    }

    function openCheckout(){
        openModal('modalCheckout');
        resetCheckoutModal();
    }

    async function loadVehicleTypes(){
        try{
            const result = await apiFetch('/api/vehicle-types');
            if(result.status !== 'success'){
                showAlert('error', 'No se pudieron cargar tipos de vehículo');
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
            showAlert('errorr', 'Error cargando tipos de vehículo');
            //console.error(err);
            //alert('Error cargando tipos de vehículo');
        }
    }

    function openModalTicket(id){
        document.getElementById(id + 'Overlay')
            ? document.getElementById(id + 'Overlay').style.display = 'flex'
            : document.getElementById('ticketModalOverlay').style.display = 'flex';
    }

    function closeTicketModal(){
        document.getElementById('ticketModalOverlay').style.display = 'none';
    }

    function printTicket(){
        document.getElementById('printFolio').innerText =
        document.getElementById('ticketFolio').innerText;
        document.getElementById('printPlate').innerText =
        document.getElementById('ticketPlate').innerText;
        document.getElementById('printEntry').innerText =
        document.getElementById('ticketEntryTime').innerText;
        window.print();
    }


    /* ============================
    Inicial
    ============================= */
    loadVehicleTypes();
    loadBranches();
</script>
