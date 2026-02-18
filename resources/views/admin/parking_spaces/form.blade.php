<div class="modal-card" role="dialog" aria-modal="true">
    <!-- HEADER -->
    <div class="modal-header">
        <div class="modal-title-wrap">
            <i class="fas fa-parking text-primary me-2"></i>
            <div>
                <h3 class="modal-title">
                    {{ $space->exists ? 'Editar Cajón' : 'Nuevo Cajón' }}
                </h3>
            </div>
        </div>
        <button class="ua-close" onclick="closeEditModal()">✕</button>
    </div>
    <!-- BODY -->
    <div class="modal-body">
            <form action="{{ $space->exists ? route('admin.parking-spaces.update', $space->id) : route('admin.parking-spaces.store') }}" method="POST" class="edit-card-body" id="spaceForm">
            @csrf
            @if($space->exists)
                @method('PUT')
            @endif
            <div class="form-section">
                {{-- Sucursal --}}
                <div class="form-group">
                    <label class="form-label-custom">Sucursal</label>
                    <div class="input-group-custom">
                        <i class="fas fa-building"></i>
                        <select name="branch_id" class="ui-select">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}"
                                    {{ old('branch_id', $space->branch_id) == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Código --}}
                <div class="form-group">
                    <label class="form-label-custom">Código de Espacio</label>
                    <div class="input-group-custom">
                        <i class="fas fa-hashtag"></i>
                        <input type="text"
                            name="code"
                            value="{{ old('code', $space->code) }}"
                            class="ua-input"
                            placeholder="Ej: A-01">
                    </div>
                </div>

                {{-- Tipo --}}
                <div class="form-group">
                    <label class="form-label-custom">Tipo de Vehículo</label>
                    <div class="input-group-custom">
                        <i class="fas fa-car"></i>
                        <select name="vehicle_type_id" class="ui-select" required>
                            <option value="">Seleccione un tipo</option>
                            @foreach($vehicleTypes as $vt)
                                <option value="{{ $vt->id }}"
                                    {{ old('vehicle_type_id', $space->vehicle_type_id) == $vt->id ? 'selected' : '' }}>
                                    {{ $vt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Estado (solo edición) --}}
                @if($space->exists)
                <div class="form-group">
                    <label class="form-label-custom">Estado</label>
                    <div class="input-group-custom">
                        <i class="fas fa-toggle-on"></i>
                        <select name="status_id" class="ui-select">
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}"
                                    {{ old('status_id', $space->status_id) == $st->id ? 'selected' : '' }}>
                                    {{ $st->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
    <!-- FOOTER -->
    <div class="modal-footer">
        {{-- ACCIONES --}}
        <div class="form-actions">
            <button type="button"
                onclick="closeEditModal()"
                class="btn-cancel">
                Cancelar
            </button>
            <button type="submit" form="spaceForm"
                class="btn-primary-action">
                {{ $space->exists ? 'Guardar' : 'Crear' }}
            </button>
        </div>
    </div>
</div>

<style>
    .edit-card-container {
        width: 100%;
        background: rgba(22,27,44,.9);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0,0,0,.55);
        animation: fadeScale .2s ease;
    }

    @keyframes fadeScale {
        from { opacity: 0; transform: scale(.96); }
        to   { opacity: 1; transform: scale(1); }
    }

    .edit-card-header {
        padding: 2rem 2rem 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,.06);
        background: linear-gradient(
            to right,
            rgba(59,130,246,.08),
            rgba(59,130,246,.02)
        );
    }

    .modal-title {
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: .5px;
    }

    .form-label-custom {
        display: block;
        color: #94a3b8;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }

    .form-section {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .input-group-custom i {
        padding-left: 10px;
        color: #64748b;
        font-size: 0.9rem;
    }

    .select-clean {
        background: transparent;
        border: none;
        color: white;
        width: 100%;
        outline: none;
        font-size: 0.9rem;
    }

</style>
