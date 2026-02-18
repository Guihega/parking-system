<div id="closeContainer"
     data-expected="{{ $expectedAmount }}"
     class="p-4">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="text-white fw-900 mb-1">Cerrar Caja</h4>
            <div class="text-white small">
                Sesión #{{ str_pad($cashSessionId, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <span class="badge-status status-open px-3 py-2">
            <i class="fas fa-unlock me-1"></i> Abierta
        </span>
    </div>

    {{-- RESUMEN FINANCIERO (CARDS EN VEZ DE TABLA) --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="close-kpi-card">
                <span class="close-kpi-label">Monto Inicial</span>
                <h5 class="close-kpi-value">
                    ${{ number_format($openingAmount, 2) }}
                </h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="close-kpi-card">
                <span class="close-kpi-label">Total Cobrado</span>
                <h5 class="close-kpi-value">
                    ${{ number_format($totalCollected, 2) }}
                </h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="close-kpi-card highlight">
                <span class="close-kpi-label">Esperado en Caja</span>
                <h4 class="close-kpi-value text-accent">
                    ${{ number_format($expectedAmount, 2) }}
                </h4>
            </div>
        </div>

    </div>

    {{-- INPUT MONTO REAL --}}
    <div class="mb-4">
        <label class="text-white small fw-bold text-uppercase mb-2 d-block">
            Monto Contado (Real)
        </label>

        <div class="close-input-wrapper">
            <span class="currency-symbol">$</span>
            <input id="real_amount"
                   type="number"
                   step="0.01"
                   min="0"
                   class="close-input"
                   placeholder="0.00">
        </div>

        <div class="mt-3 text-white small">
            Diferencia:
            <span id="diff_label" class="fw-bold diff-neutral">$0.00</span>
        </div>
    </div>

    {{-- OBSERVACIONES --}}
    <div class="mb-4">
        <label class="text-white small fw-bold text-uppercase mb-2 d-block">
            Observaciones
        </label>
        <textarea id="observations"
                  class="close-textarea"
                  rows="2"
                  placeholder="Opcional..."></textarea>
    </div>

    {{-- ACCIONES --}}
    <div class="d-flex gap-3 mt-4">

        <button type="button"
                class="btn-secondary-action flex-fill"
                onclick="loadSessionDetail({{ (int)$cashSessionId }})">
            Volver
        </button>

        <button type="button"
                class="btn-primary-danger flex-fill"
                onclick="confirmCloseCashSession({{ (int)$cashSessionId }}, {{ json_encode($expectedAmount) }})">
            <i class="fas fa-lock me-1"></i> Confirmar Cierre
        </button>

    </div>

</div>
