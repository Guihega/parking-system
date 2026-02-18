@php
    $uiPerms = session('permissions', []);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ParkEasy') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <script>
            window.__UI_PERMS__ = @json($uiPerms);
        </script>
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body >
        <div class="app-shell">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                         @yield('header')
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="page-content">
                {{-- GLOBAL ALERT --}}
                <div id="alertBox" class="ua-alert d-none"></div>
                @yield('content')
            </main>
        </div>
        @stack('scripts')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    </body>
</html>

<script>
function toggleUserMenu(){
    document.getElementById('userMenu').classList.toggle('hidden');
}

document.addEventListener('click', e=>{
    const menu = document.getElementById('userMenu');
    if(!e.target.closest('.nav-right')){
        menu?.classList.add('hidden');
    }
});

document.addEventListener('alpine:init', () => {
    console.log('Alpine iniciado correctamente');
});
</script>
