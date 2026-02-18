<div id="modalCheckout" class="modal-overlay" data-modal>
    <div class="modal-card modal-pos">
        <!-- Toast Container -->
        <div id="toastContainer" class="ps-toast-container"></div>
        <!-- HEADER -->
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-icon">🧾</div>
                <div>
                    <h3 class="modal-title">Consulta de ticket</h3>
                    <p class="modal-subtitle">
                        Escanea o ingresa el token para consultar el estado
                    </p>
                </div>
            </div>
            <button class="ua-close" onclick="closeCheckout()">✕</button>
        </div>

        <!-- BODY -->
        <div class="modal-body modal-pos-body">
            @include('parking.partials.ticket-checkout')
        </div>
    </div>
</div>

