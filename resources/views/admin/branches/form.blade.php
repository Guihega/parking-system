<div class="modal-card" role="dialog" aria-modal="true">
    <!-- HEADER -->
    <div class="modal-header">
        <div class="modal-title-wrap">
            <div class="modal-icon-glass">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h3 class="modal-title">
                    {{ $branch->exists ? 'Editar Sucursal' : 'Nueva Sucursal' }}
                </h3>
            </div>
        </div>
        <button class="ua-close" onclick="closeBranchModal()">✕</button>
    </div>
    <!-- BODY -->
    <div class="modal-body">
        <form
            method="POST" id="brancheForm"
            action="{{ $branch->exists
                ? route('admin.branches.update', $branch->id)
                : route('admin.branches.store') }}"
            class="edit-card-body">
            @csrf
            @if($branch->exists)
                @method('PUT')
            @endif
            <div class="form-section">

                {{-- Nombre de la Sucursal --}}
                <div class="form-group">
                    <label class="form-label-custom">Nombre de la Sucursal</label>
                    <div class="input-group-custom">
                        <i class="fas fa-building"></i>
                        <input type="text"
                            name="name"
                            class="ua-input"
                            placeholder="Ej: Sucursal Norte"
                            value="{{ old('name', $branch->name) }}"
                            required>
                    </div>
                </div>

                {{-- Estado (solo edición) --}}
                @if($branch->exists)
                <div class="form-group">
                    <label class="form-label-custom">Estado Operativo</label>
                    <div class="input-group-custom">
                        <i class="fas fa-toggle-on"></i>
                        <select name="is_active" class="ui-select select-clean">
                            <option value="1" {{ old('is_active', $branch->is_active) == 1 ? 'selected' : '' }} class="bg-dark">
                                Activa
                            </option>
                            <option value="0" {{ old('is_active', $branch->is_active) == 0 ? 'selected' : '' }} class="bg-dark">
                                Inactiva
                            </option>
                        </select>
                    </div>
                </div>
                @endif

            </div>
        </form>
    </div>
    <!-- FOOTER -->
    <div class="modal-footer">
        {{-- ACCIONES HOMOLOGADAS --}}
        <div class="form-actions">
            <button type="button"
                    onclick="closeBranchModal()"
                    class="btn-cancel">
                Cancelar
            </button>
            <button type="submit"  form="brancheForm"
                    class="btn-primary-action">
                {{ $branch->exists ? 'Actualizar' : 'Crear' }}
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

    /* Estilo para opciones de select en modo oscuro */
    .bg-dark {
        background-color: #1a202c !important;
        color: white;
    }

</style>
