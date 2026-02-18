@php
    $perms = session('permissions', []);
@endphp
<nav class="app-navbar">
    <div class="nav-inner">

        {{-- Logo / Home --}}
        <div class="nav-left">
            <a href="{{ route('dashboard') }}" class="nav-brand">
                <i class="fa-solid fa-cube"></i>
                <span>Parking System</span>
            </a>
        </div>

        {{-- Menú central --}}
        <div class="nav-center">

            {{-- 🅿️ Operación Parking (POS) --}}
            @can('parking.entry')
                <a href="{{ route('parking.core') }}"
                   class="nav-link {{ request()->routeIs('parking.*') ? 'active' : '' }}">
                    Operación
                </a>
            @endif

            {{-- 📊 Dashboard administrativo --}}
            @if(in_array('reports.view', $perms))
                <a href="{{ route('admin.dashboard.index') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
                    Dashboard
                </a>
            @endif

            @if(in_array('tickets.view', $perms))
                <a href="{{ route('admin.tickets.index') }}"
                class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    Tickets
                </a>
            @endif

            {{-- 💰 Caja / Auditoría --}}
            @if(in_array('cash.audit', $perms))
                <a href="{{ route('admin.cash-sessions.index') }}"
                   class="nav-link {{ request()->routeIs('admin.cash-sessions.*') ? 'active' : '' }}">
                    Caja
                </a>
            @endif

            {{-- 💵 Tarifas --}}
            @if(in_array('tariffs.view', $perms))
                <a href="{{ route('admin.tariffs.index') }}"
                   class="nav-link {{ request()->routeIs('admin.tariffs.*') ? 'active' : '' }}">
                    Tarifas
                </a>
            @endif

            {{-- 🚗 Cajones / Espacios --}}
            @if(in_array('parking.view', $perms))
                <a href="{{ route('admin.parking-spaces.index') }}"
                class="nav-link {{ request()->routeIs('admin.parking-spaces.*') ? 'active' : '' }}">
                    Cajones
                </a>
            @endif


            {{-- 👥 Usuarios --}}
            @if(in_array('users.view', $perms))
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    Usuarios
                </a>
            @endif

            {{-- 🛡️ Roles / Permisos --}}
            @if(in_array('roles.view', $perms))
                <a href="{{ route('admin.roles.index') }}"
                   class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    Roles
                </a>
            @endif

            {{-- 🏢 Sucursales --}}
            @if(in_array('roles.assign', $perms))
                <a href="{{ route('admin.branches.index') }}"
                   class="nav-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                    Sucursales
                </a>
            @endif

        </div>

        {{-- Usuario --}}
        <div class="nav-right">
            <div class="user-chip" onclick="toggleUserMenu()">
                <i class="fa-solid fa-user"></i>
                <span>{{ Auth::user()->name }}</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>

            <div id="userMenu" class="user-menu hidden">
                <a href="{{ route('profile.edit') }}">Perfil</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>

    </div>
</nav>
