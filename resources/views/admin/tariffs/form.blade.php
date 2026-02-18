
<div class="modal-card" role="dialog" aria-modal="true">
    <!-- HEADER -->
    <div class="modal-header">
        <div class="modal-title-wrap">
            <i class="fas fa-tags text-primary me-2"></i>
            <div>
                <h3 class="modal-title">
                    {{ isset($tariff) ? 'Editar Tarifa' : 'Nueva Tarifa' }}
                </h3>
            </div>
        </div>
        <button class="ua-close" onclick="closeEditModal()">✕</button>
    </div>

    <!-- BODY -->
    <div class="modal-body">
        <form
            method="POST"
            id="tariffForm"
            action="{{ isset($tariff)
                ? route('admin.tariffs.update', $tariff->id)
                : route('admin.tariffs.store') }}"
            class="edit-card-body">
            @csrf
            @if(isset($tariff)) @method('PUT') @endif
            <div class="form-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div class="form-column">

                    {{-- Sucursal --}}
                    <div class="form-group">
                        <label class="form-label-custom">Sucursal</label>
                        <div class="input-group-custom">
                            <i class="fas fa-building"></i>
                            <select name="branch_id" class="ui-select" required>
                                <option value="">Seleccione sucursal</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $tariff->branch_id ?? '') == $branch->id ? 'selected' : '' }}
                                        class="bg-dark">
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tipo Vehículo --}}
                    <div class="form-group">
                        <label class="form-label-custom">Tipo de Vehículo</label>
                        <div class="input-group-custom">
                            <i class="fas fa-car"></i>
                            <select name="vehicle_type_id" class="ui-select" required>
                                <option value="">Seleccione tipo</option>
                                @foreach($vehicleTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('vehicle_type_id', $tariff->vehicle_type_id ?? '') == $type->id ? 'selected' : '' }}
                                        class="bg-dark">
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nombre --}}
                    <div class="form-group">
                        <label class="form-label-custom">Nombre</label>
                        <div class="input-group-custom">
                            <i class="fas fa-tag"></i>
                            <input type="text"
                                name="name"
                                class="ua-input"
                                placeholder="Ej: Tarifa Nocturna"
                                value="{{ old('name', $tariff->name ?? '') }}"
                                required>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label class="form-label-custom">Descripción</label>
                        <div class="input-group-custom">
                            <i class="fas fa-align-left"></i>
                            <textarea name="description"
                                    class="ua-input"
                                    rows="3"
                                    placeholder="Descripción opcional">{{ old('description', $tariff->description ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Tipo de cálculo --}}
                    <div class="form-group">
                        <label class="form-label-custom">Tipo de Cálculo</label>
                        <div class="input-group-custom">
                            <i class="fas fa-calculator"></i>
                            <select name="calc_type" class="ui-select" required>
                                <option value="hourly"
                                    {{ old('calc_type', $tariff->calc_type ?? '') === 'hourly' ? 'selected' : '' }}
                                    class="bg-dark">
                                    Por Hora
                                </option>
                                <option value="flat"
                                    {{ old('calc_type', $tariff->calc_type ?? '') === 'flat' ? 'selected' : '' }}
                                    class="bg-dark">
                                    Monto Fijo
                                </option>
                            </select>
                        </div>
                    </div>

                </div>
                <!-- COLUMNA DERECHA -->
                <div class="form-column">

                    {{-- Precio por hora --}}
                    <div class="form-group">
                        <label class="form-label-custom">Precio por Hora</label>
                        <div class="input-group-custom">
                            <i class="fas fa-clock"></i>
                            <div class="ui-number">
                                <button type="button" onclick="decrement(this)">−</button>
                                <input type="number"
                                    step="0.01"
                                    name="price_per_hour"
                                    class="control-field amount-input clean-number"
                                    value="{{ old('price_per_hour', $tariff->price_per_hour ?? '') }}">
                                    <button type="button" onclick="increment(this)">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Monto fijo --}}
                    <div class="form-group">
                        <label class="form-label-custom">Monto Fijo</label>
                        <div class="input-group-custom">
                            <i class="fas fa-dollar-sign px-1"></i>
                            <div class="ui-number">
                                <button type="button" onclick="decrement(this)">−</button>
                                <input type="number"
                                    step="0.01"
                                    name="flat_amount"
                                    class="control-field amount-input clean-number"
                                    value="{{ old('flat_amount', $tariff->flat_amount ?? '') }}">
                                <button type="button" onclick="increment(this)">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Minutos de gracia --}}
                    <div class="form-group">
                        <label class="form-label-custom">Minutos de Gracia</label>
                        <div class="input-group-custom">
                            <i class="fas fa-stopwatch"></i>
                            <div class="ui-number">
                                <button type="button" onclick="decrement(this)">−</button>
                                <input type="number"
                                    name="grace_minutes"
                                    min="0"
                                    step="1"
                                    class="control-field amount-input clean-number"
                                    value="{{ old('grace_minutes', $tariff->grace_minutes ?? '') }}">
                                <button type="button" onclick="increment(this)">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Prioridad --}}
                    <div class="form-group">
                        <label class="form-label-custom">Prioridad</label>
                        <div class="input-group-custom">
                            <i class="fas fa-layer-group"></i>
                            <div class="ui-number">
                                <button type="button" onclick="decrement(this)">−</button>
                                <input type="number"
                                    name="priority"
                                    class="control-field amount-input clean-number"
                                    value="{{ old('priority', $tariff->priority ?? 1) }}"
                                    required>
                                <button type="button" onclick="increment(this)">+</button>
                            </div>
                        </div>
                    </div>

                    {{-- Estado --}}
                    <div class="form-group">
                        <label class="form-label-custom">Estado</label>
                        <div class="input-group-custom">
                            <i class="fas fa-toggle-on"></i>
                            <select name="is_active" class="ui-select">
                                <option value="1"
                                    {{ old('is_active', $tariff->is_active ?? 1) == 1 ? 'selected' : '' }}
                                    class="bg-dark">
                                    Activa
                                </option>
                                <option value="0"
                                    {{ old('is_active', $tariff->is_active ?? 1) == 0 ? 'selected' : '' }}
                                    class="bg-dark">
                                    Inactiva
                                </option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- FOOTER -->
    <div class="modal-footer">
        <div class="form-actions">
            <button type="button"
                    onclick="closeEditModal()"
                    class="btn-cancel">
                Cancelar
            </button>

            <button type="submit"
                    form="tariffForm"
                    class="btn-primary-action">
                {{ isset($tariff) ? 'Actualizar' : 'Crear' }}
            </button>
        </div>
    </div>

</div>
<style>
    .edit-card-container {
        width: 100%;
        max-width: 500px;
        background: var(--card-bg);
        backdrop-filter: blur(15px);
        border: 1px solid var(--border-glass);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .edit-card-header {
        padding: 2rem 2rem 1.5rem;
        border-bottom: 1px solid var(--border-glass);
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

    .form-label-custom {
        display: block;
        color: #94a3b8;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .input-group-custom i {
        color: #94a3b8;
        opacity: .7;
    }

    .select-clean {
        background: transparent !important;
        border: none !important;
        padding: 5px 10px !important;
        color: white;
        width: 100%;
        outline: none;
        font-size: 0.95rem;
    }

    /* Estilo para opciones de select en modo oscuro */
    .bg-dark {
        background-color: #1a202c !important;
        color: white;
    }

</style>
