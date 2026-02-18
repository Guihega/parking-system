@php
    $uiPerms = session('permissions', []);
@endphp

@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between mb-6">
    <div class="sticky top-0 z-10 bg-slate-900/95 backdrop-blur">
        <h3 class="font-semibold">Permisos por módulo</h3>
    </div>
    <span class="text-xs px-3 py-1 rounded-full bg-slate-800 text-indigo-400">
        RBAC
    </span>
</div>
@endsection
@section('content')
    <div class="grid grid-cols-12 gap-6">
        {{-- LISTA DE ROLES --}}
        <div class="col-span-3 bg-slate-900 rounded-xl p-4">
            <h3 class="font-semibold mb-3">Roles</h3>
            <ul id="rolesList" class="space-y-2"></ul>
        </div>

        {{-- PERMISOS --}}
        <div class="col-span-9 bg-slate-900 rounded-xl p-6">
            <p id="roleLabel" class="text-sm text-gray-400 mb-4">
                Selecciona un rol para editar sus permisos
            </p>

            <form id="permissionsForm">
                <div id="permissionsContainer" class="space-y-6"></div>

                @if(in_array('roles.assign', $uiPerms))
                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                            class="
                                px-6 py-2 rounded-lg font-semibold
                                bg-indigo-600 hover:bg-indigo-700
                                transition-all
                                disabled:opacity-40
                                disabled:cursor-not-allowed
                                shadow-md">
                            Guardar cambios
                        </button>
                    </div>
                @endif
            </form>

            <div id="changesSummary"
                class="mt-4 text-sm text-slate-300 hidden">
            </div>

            @if(in_array('roles.audit', $uiPerms))
                <div class="mt-10 border-t border-slate-700 pt-6">
                    <h3 class="text-lg font-semibold mb-4 text-white">
                        Historial de cambios de permisos
                    </h3>

                    <details class="mt-10">
                        <summary class="cursor-pointer text-indigo-400 font-semibold">
                            Ver historial de cambios
                        </summary>

                        <div class="mt-4" id="auditContainer">
                            ...
                        </div>
                    </details>
                </div>
            @endif

        </div>
    </div>
    <style>
        .permission-body {
            animation: fadeSlide 0.25s ease-out;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>

    <div class="hidden
        rotate-180
        transform
        ring-1 ring-indigo-500/30
        from-slate-800 via-slate-800 to-slate-700
        from-slate-900 via-slate-900 to-slate-800
    "></div>
@endsection

{{-- Exponer permisos a JS --}}
{{-- @section('scripts')
<script>
    window.__UI_PERMS__ = @json($uiPerms);
</script>
@endsection --}}
