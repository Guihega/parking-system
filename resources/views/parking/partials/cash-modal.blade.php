<div id="modalCheckout" class="modal-overlay" data-modal>
    <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-header">
            <div class="modal-title-wrap">
                <div class="modal-icon">💰</div>
                <div>
                    <h3 class="modal-title">Sesión de Caja</h3>
                    <p class="modal-subtitle">
                        Control de apertura y cierre
                    </p>
                </div>
            </div>
            <button class="modal-close" onclick="closeCheckout()">✕</button>
        </div>
        <div class="modal-body">
            {{-- contenido actual --}}
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary " onclick="closeCheckout()">
                Cerrar
            </button>
        </div>
    </div>
</div>
