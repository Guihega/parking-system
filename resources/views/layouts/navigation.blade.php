<nav class="app-navbar">
    <div class="nav-inner">

        <!-- Logo + Brand -->
        <div class="nav-left">
            <a href="{{ route('dashboard') }}" class="nav-brand">
                <i class="fa-solid fa-cube"></i>
                <span>Parking System</span>
            </a>
        </div>

        <!-- Center menu -->
        <div class="nav-center">
            <a href="{{ route('dashboard') }}" class="nav-link active">
                Dashboard
            </a>
        </div>

        <!-- User -->
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
