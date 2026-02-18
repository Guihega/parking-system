<div class="checkout-pos">
    <div class="glass-card token-search">
        <div class="token-field">
            <span class="token-icon">🔍</span>
            <input
                id="tokenInput"
                class="token-input"
                placeholder="Escanear QR o ingresar token"
                autocomplete="off"
            >
            <button class="btn-primary-action" onclick="consultTicket()">
                Consultar
            </button>
        </div>
    </div>
    <div class="checkout-grid">
        <!-- DATOS -->
        <div class="ticket-charge-grid">
            <div id="ticketCard" class="glass-card p-3 ">
                <h5>Ticket</h5>
                <p><strong>Folio:</strong> <span id="folio"></span></p>
                <p><strong>Placa:</strong> <span id="plate"></span></p>
                <p><strong>Cajón:</strong> <span id="space"></span></p>
                <p><strong>Entrada:</strong> <span id="entry"></span></p>
                <p><strong>Estado:</strong> <span id="status"></span></p>
                <p><strong>Minutos:</strong> <span id="minutes"></span></p>
            </div>
            <div id="chargeCard" class="glass-card p-3 ">
                <h5>Cobro</h5>
                <p>Horas cobradas: <span id="hours"></span></p>
                <p>Precio hora: $<span id="price"></span></p>
                <h4>Total: $<span id="total"></span></h4>
                <div class="payment-box mt-3">
                    <label class="payment-label">Método de pago</label>
                    <select id="paymentMethod" class="ui-select payment-select">
                        <option value="cash">Efectivo</option>
                        <option value="card">Tarjeta</option>
                        <option value="transfer">Transferencia</option>
                    </select>
                    <button class="pay-cta" id="payBtn" onclick="confirmPayment()">
                        Confirmar pago
                    </button>
                </div>
            </div>
            <!-- HISTORIAL -->
            <div id="historyCard" class="glass-card p-3  mt-1 history-full">
                <div class="history-header" onclick="toggleHistory()">
                    <h5>Historial del ticket</h5>
                    <span id="historyToggle">▼</span>
                </div>
                <div id="historyBody" class="history-body collapsed">
                    <div id="historyTimeline" class="timeline"></div>
                </div>
            </div>
        </div>
        <!-- PREVIEW TICKET (MISMO DISEÑO) -->
        <div class="receipt-preview">
            <div id="receiptCard" class="receipt ">
                <div class="receipt-logo">PARKING SYSTEM</div>
                <div class="receipt-header">
                    <div class="branch" id="receiptBranch"></div>
                    <div class="datetime" id="receiptDatetime"></div>
                </div>
                <div class="separator"></div>
                <div class="receipt-row"><span>Folio:</span><span id="receiptFolio"></span></div>
                <div class="receipt-row"><span>Placa:</span><span id="receiptPlate"></span></div>
                <div class="receipt-row"><span>Cajón:</span><span id="receiptSpace"></span></div>
                <div class="receipt-row"><span>Entrada:</span><span id="receiptEntry"></span></div>
                <div class="receipt-row"><span>Salida:</span><span id="receiptExit"></span></div>
                <div class="receipt-row"><span>Tiempo:</span><span><span id="receiptMinutes"></span> min</span></div>
                <div class="separator"></div>
                <div class="receipt-total">
                    TOTAL $<span id="receiptTotal"></span>
                </div>
                <div class="receipt-payment">
                    Pago: <span id="receiptPayment"></span>
                </div>
                <div class="separator"></div>
                <div class="receipt-footer">Gracias por su visita</div>
                <div class="separator"></div>
                <div class="receipt-qr">
                    <img id="receiptQR">
                </div>
            </div>
            <button id="reprintBtn"
                class="btn-primary-action"
                disabled
                onclick="reprintFromConsult()">
                Imprimir comprobante
            </button>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="pos-modal hidden">
    <div class="pos-modal-card">
        <h4 id="confirmTitle">Confirmar acción</h4>
        <p id="confirmMessage"></p>
        <div class="pos-modal-actions">
            <button class="btn-cancel" onclick="closeConfirm()">Cancelar</button>
            <button class="btn-confirm" id="confirmOkBtn">Confirmar</button>
        </div>
    </div>
</div>

<script>
    let currentToken = null;
    let lastReceiptHTML = null;
    let lastReceiptData = null;
    let PRINT_MODE = 'auto';

    const tokenInput     = document.getElementById('tokenInput');
    const ticketCard     = document.getElementById('ticketCard');
    const chargeCard     = document.getElementById('chargeCard');
    const receiptCard    = document.getElementById('receiptCard');
    const historyCard    = document.getElementById('historyCard');
    const historyTimeline= document.getElementById('historyTimeline');
    const payBtn         = document.getElementById('payBtn');
    const reprintBtn     = document.getElementById('reprintBtn');
    const folio    = document.getElementById('folio');
    const plate    = document.getElementById('plate');
    const space    = document.getElementById('space');
    const entry    = document.getElementById('entry');
    const status   = document.getElementById('status');
    const minutes  = document.getElementById('minutes');
    const hours    = document.getElementById('hours');
    const price    = document.getElementById('price');
    const total    = document.getElementById('total');
    const receiptBranch   = document.getElementById('receiptBranch');
    const receiptDatetime = document.getElementById('receiptDatetime');
    const receiptFolio    = document.getElementById('receiptFolio');
    const receiptPlate    = document.getElementById('receiptPlate');
    const receiptSpace    = document.getElementById('receiptSpace');
    const receiptEntry    = document.getElementById('receiptEntry');
    const receiptExit     = document.getElementById('receiptExit');
    const receiptMinutes  = document.getElementById('receiptMinutes');
    const receiptTotal    = document.getElementById('receiptTotal');
    const receiptPayment  = document.getElementById('receiptPayment');
    const receiptQR       = document.getElementById('receiptQR');
    const historyBody     = document.getElementById('historyBody');
    const historyToggle   = document.getElementById('historyToggle');

    let confirmCallback = null;

    async function consultTicket(autoPrint = false) {
        const token = tokenInput.value.trim();

        if (!token) {
            showToast('Debes ingresar un token válido', 'error');
            return;
        }

        currentToken = token;
        ticketCard.classList.add('is-loading');
        chargeCard.classList.add('is-loading');
        receiptCard.classList.add('is-loading');
        historyCard.classList.add('is-loading');

        historyTimeline.innerHTML = '';

        payBtn.disabled = true;
        reprintBtn.disabled = true;

        try {
            /* ===== INFO GENERAL ===== */
            const infoRes = await fetch(`/api/tickets/${token}`, {
                headers: { 'Authorization': 'Bearer {{ session("jwt_token") }}' }
            });

            const info = await infoRes.json();
            //if (!infoRes.ok) return alert(info.message);
            if (!infoRes.ok) {
                showToast(info.message || 'Error consultando ticket', 'error');
                return;
            }

            const ticket = info.ticket;

            ticketCard.classList.remove('is-loading');
            folio.textContent   = ticket.folio;
            plate.textContent   = ticket.plate;
            space.textContent   = ticket.parking_space;
            entry.textContent   = ticket.entry_time;
            status.textContent  = ticket.status;
            minutes.textContent= ticket.minutes_elapsed;

            /* =====================================================
            🟢 TICKET ABIERTO → CALCULO EN VIVO
            ===================================================== */
            if (ticket.status === 'open') {

                payBtn.textContent = 'Confirmar pago';
                payBtn.disabled = false;

                const chargeRes = await fetch(`/api/tickets/${token}/charge`, {
                    headers: { 'Authorization': 'Bearer {{ session("jwt_token") }}' }
                });

                const charge = await chargeRes.json();

                //if (!chargeRes.ok) return alert(charge.message);
                if (!chargeRes.ok) {
                    showToast(charge.message || 'Error calculando cobro', 'error');
                    return;
                }


                chargeCard.classList.remove('is-loading');
                hours.textContent = charge.ticket.charged_hours;
                price.textContent = charge.ticket.price_per_hour;
                total.textContent = charge.ticket.amount;
            }

            /* =====================================================
            🔒 TICKET CERRADO → SNAPSHOT HISTÓRICO
            ===================================================== */
            if (info.ticket.status === 'closed') {
                payBtn.textContent = 'Pago realizado';
                payBtn.disabled = true;
                const receiptData = await loadReceipt(token, autoPrint);

                if(receiptData){
                    chargeCard.classList.remove('is-loading');
                    const hoursCharged = Math.ceil(receiptData.minutes / 60);
                    hours.textContent =hoursCharged;
                    price.textContent = (receiptData.total_amount / hoursCharged).toFixed(2);
                    total.textContent = receiptData.total_amount;
                }

                //reprintBtn.classList.remove('');
                reprintBtn.disabled = false;
            }

            /* ===== HISTORIAL ===== */
            loadHistory(token);

        } catch (e) {
            showToast('Error de conexión al consultar ticket ' + e, 'error');
        }
    }

    async function registerExit() {
        if (!currentToken) return;

        showConfirm(
            '¿Registrar pago y cerrar ticket?',
            async () => {
                const res = await fetch('/api/tickets/exit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer {{ session("jwt_token") }}'
                    },
                    body: JSON.stringify({
                        token: currentToken,
                        payment_code: 'cash'
                    })
                });

                const result = await res.json();

                if (!res.ok) {
                    showToast(result.message, 'error');
                    return;
                }

                showToast('Ticket cerrado correctamente', 'success');
                await consultTicket(true);
            },
            'Cerrar ticket'
        );
    }

    async function loadReceipt(token, autoPrint = false) {
        try {
            const res = await fetch(`/api/tickets/token/${token}/receipt`, {
                headers: { 'Authorization': 'Bearer {{ session("jwt_token") }}' }
            });

            const data = await res.json();

            if (!res.ok || !data.receipt) return null;

            const r = data.receipt;
            receiptBranch.textContent  = r.branch || 'Sucursal';
            receiptDatetime.textContent= r.exit_time || '';
            receiptFolio.textContent  = r.folio || '—';
            receiptPlate.textContent  = r.plate || '—';
            receiptSpace.textContent  = r.parking_space || '—';
            receiptEntry.textContent  = r.entry_time || '—';
            receiptExit.textContent   = r.exit_time || '—';
            receiptMinutes.textContent = r.minutes ?? 0;
            receiptTotal.textContent = r.total_amount ?? '0.00';
            receiptPayment.textContent = r.payment_name || r.payment_code || '—';
            document.getElementById('receiptQR').src =
                `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(r.folio)}`;

            receiptCard.classList.remove('is-loading');

            lastReceiptData = r;
            lastReceiptHTML = buildReceiptHTML(r);

            if(autoPrint){
                printReceipt(r);
            }

            return r;   // ✅ ESTA ES LA ÚNICA LÍNEA CLAVE

        } catch (e) {
            showToast('Receipt error ' + e, 'error');
            return null;
        }
    }

    async function loadHistory(token){
        const historyRes = await fetch(`/api/tickets/${token}/history`, {
            headers: { 'Authorization': 'Bearer {{ session("jwt_token") }}' }
        });

        const history = await historyRes.json();
        if (!historyRes.ok) return;

        historyCard.classList.remove('is-loading');
        history.history.forEach(item => {
            let payload = {};
            try { payload = JSON.parse(item.payload || '{}'); } catch {}
            let detail = '';

            if (item.action === 'entry')
                detail = `Placa ${payload.plate} — Cajón ${payload.parking_space_id}`;

            if (item.action === 'charge')
                detail = `${payload.minutes} min — $${payload.amount}`;

            if (item.action === 'payment')
                detail = `$${payload.amount} (${payload.payment_code})`;

            if (item.action === 'cancel')
                detail = `Motivo: ${payload.reason}`;

            const div = document.createElement('div');
            div.className = `timeline-item timeline-${item.action}`;

            div.innerHTML = `
                <strong>${item.description}</strong>
                <div>${detail}</div>
                <div class="timeline-time">${item.created_at}</div>
            `;

            historyTimeline.appendChild(div);
        });
    }

    function printReceiptClean(html){
        const win = window.open('', '_blank','width=300,height=600');

        win.document.write(`
        <html>
        <head>
        <style>
            body{margin:0;font-family:monospace;font-size:12px}
            .receipt{width:76mm;padding:6px}
            .receipt-row{display:flex;justify-content:space-between}
            .separator{border-top:1px dashed #000;margin:6px 0}
            .receipt-logo,.receipt-header,.receipt-total,.receipt-footer{text-align:center}
            img{width:120px;margin:auto;display:block}
        </style>
        </head>
        <body onload="window.print();window.close()">
            ${html}
        </body>
        </html>`);

        win.document.close();
    }

    function buildReceiptHTML(r){
        return `
        <div class="receipt">
            <div class="receipt-logo">PARKING SYSTEM</div>
            <div class="receipt-header">
                ${r.branch}<br>${r.exit_time}
            </div>
            <div class="separator"></div>
            <div class="receipt-row"><span>Folio:</span><span>${r.folio}</span></div>
            <div class="receipt-row"><span>Placa:</span><span>${r.plate}</span></div>
            <div class="receipt-row"><span>Cajón:</span><span>${r.parking_space}</span></div>
            <div class="receipt-row"><span>Entrada:</span><span>${r.entry_time}</span></div>
            <div class="receipt-row"><span>Salida:</span><span>${r.exit_time}</span></div>
            <div class="receipt-row"><span>Tiempo:</span><span>${r.minutes} min</span></div>
            <div class="separator"></div>
            <div class="receipt-total">TOTAL $${r.total_amount}</div>
            <div class="receipt-payment">Pago: ${r.payment_name || r.payment_code}</div>
            <div class="separator"></div>
            <div class="receipt-footer">Gracias por su visita</div>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(r.folio)}">
        </div>`;
    }

    function showReprintButton(){
        document.getElementById('reprintBtn').classList.remove('');
    }

    async function reprintFromConsult(){
        reprintBtn.textContent = 'Imprimiendo...';
        reprintBtn.disabled = true;

        try{
            if(!lastReceiptData) return;
            await printReceipt(lastReceiptData);
        }finally{

            reprintBtn.textContent = '🖨 Reimprimir comprobante';
            reprintBtn.disabled = false;

            // Limpieza tipo caja real
            setTimeout(() => {
                resetCheckoutModal();
            }, 500);
        }
    }

    async function printThermalESC(r){
        try{
            await fetch('http://localhost:3000/print', {
                method:'POST',
                headers:{ 'Content-Type':'application/json' },
                body: JSON.stringify({
                    branch: r.branch,
                    datetime: r.exit_time,
                    folio: r.folio,
                    plate: r.plate,
                    space: r.parking_space,
                    entry: r.entry_time,
                    exit: r.exit_time,
                    minutes: r.minutes,
                    total: r.total_amount,
                    payment: r.payment_name || r.payment_code
                })
            });
        }catch(e){
            showToast('No se pudo imprimir en térmica ' + e, 'error');
        }
    }

    async function printReceipt(r){
        if(PRINT_MODE === 'browser'){
            printReceiptClean(buildReceiptHTML(r));
            return;
        }

        if(PRINT_MODE === 'thermal'){
            await printThermalESC(r);
            return;
        }

        // AUTO MODE (recomendado)
        try{
            await printThermalESC(r);
        }catch(e){
            showToast('Térmica no disponible, usando navegador ' + e, 'error');
            printReceiptClean(buildReceiptHTML(r));
        }
    }

    function toggleHistory(){
        const body = document.getElementById('historyBody');
        const icon = document.getElementById('historyToggle');

        if(body.classList.contains('collapsed')){
            body.classList.remove('collapsed');
            body.classList.add('expanded');
            icon.textContent = '▲';
        }else{
            body.classList.remove('expanded');
            body.classList.add('collapsed');
            icon.textContent = '▼';
        }
    }

    tokenInput.addEventListener('keydown', e => {
        if(e.key === 'Enter'){
            consultTicket(true);
        }
    });

    async function confirmPayment(){
        if(!currentToken) return;
        const method = document.getElementById('paymentMethod').value;
        showConfirm(
            `¿Confirmar pago con método: ${method}?`,
            async () => {
                await processPayment(method);
            },
            "Confirmar pago"
        );
    }

    async function processPayment(paymentMethod){
        const res = await fetch('/api/tickets/exit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer {{ session("jwt_token") }}'
            },
            body: JSON.stringify({
                token: currentToken,
                payment_code: paymentMethod
            })
        });
        //console.log('processPayment '+res);
        const result = await res.json();
        //if (!res.ok) return alert(result.message);
        if (!res.ok || result.status !== 'success') {
            showToast(result.message ?? 'Error procesando pago', 'error');
            return;
        }

        showToast('Pago registrado correctamente', 'success');
        showAlert('Pago registrado correctamente', 'success');
        //console.log('processPayment', JSON.stringify(result, null, 2));
        // Refresca y ahora ya imprime
        await consultTicket(true);
    }


    function resetCheckoutModal(){
        currentToken = null;
        lastReceiptHTML = null;
        lastReceiptData = null;

        tokenInput.value = '';
        historyTimeline.innerHTML = '';

        folio.textContent = '';
        plate.textContent = '';
        space.textContent = '';
        entry.textContent = '';
        status.textContent = '';
        minutes.textContent = '';

        hours.textContent = '';
        price.textContent = '';
        total.textContent = '';

        receiptBranch.textContent = '';
        receiptDatetime.textContent = '';
        receiptFolio.textContent = '';
        receiptPlate.textContent = '';
        receiptSpace.textContent = '';
        receiptEntry.textContent = '';
        receiptExit.textContent = '';
        receiptMinutes.textContent = '';
        receiptTotal.textContent = '';
        receiptPayment.textContent = '';
        receiptQR.src = '';

        payBtn.disabled = true;
        reprintBtn.disabled = true;

        historyBody.classList.remove('expanded');
        historyBody.classList.add('collapsed');
        historyToggle.textContent = '▼';

        tokenInput.focus();
    }


    function closeCheckout(){
        //document.getElementById('modalCheckout').style.display = 'none';
        const modal = document.getElementById('modalCheckout');
        modal.classList.remove('is-open');
        resetCheckoutModal();
    }

    function showToast(message, type = 'info', duration = 3500) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');

        toast.className = `ps-toast ps-${type}`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    function showConfirm(message, callback, title = "Confirmar acción") {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;

        confirmCallback = callback;

        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirm() {
        document.getElementById('confirmModal').classList.add('hidden');
        confirmCallback = null;
    }

    document.getElementById('confirmOkBtn').addEventListener('click', () => {
        if (confirmCallback) confirmCallback();
        closeConfirm();
    });

</script>
