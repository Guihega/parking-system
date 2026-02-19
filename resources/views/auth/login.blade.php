<x-guest-layout>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<div class="cinematic-bg">
    <div class="bg-texture"></div>
    <div class="ambient-glow"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="login-glass">
        <div class="brand">
            <div class="logo-container">
                <i class="fa-solid fa-car-side"></i>
            </div>
            <h1>PARK<span>EASY</span></h1>
            <p>Sistema de Gestión Inteligente</p>
        </div>
        {{-- ALERTAS / ERRORES --}}
        @if ($errors->any())
            <div class="auth-alert auth-alert-error" role="alert">
                <div class="auth-alert-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="auth-alert-body">
                    <div class="auth-alert-title">No se pudo iniciar sesión</div>
                    <div class="auth-alert-msg">{{ $errors->first() }}</div>
                </div>
            </div>
        @endif

        @if (session('status'))
            <div class="auth-alert auth-alert-info" role="alert">
                <div class="auth-alert-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="auth-alert-body">
                    <div class="auth-alert-title">Información</div>
                    <div class="auth-alert-msg">{{ session('status') }}</div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="input-group">
                <label>Email Corporativo</label>
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" placeholder="ejemplo@empresa.com" required>
                </div>
            </div>
            <div class="input-group">
                <label>Contraseña</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="passInput" placeholder="••••••••" required>
                    <span class="eye" onclick="togglePass()"><i class="fa-regular fa-eye"></i></span>
                </div>
            </div>
            <div class="options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span class="checkmark"></span>
                    Recordarme
                </label>
                <a href="{{ route('password.request') }}">¿Problemas de acceso?</a>
            </div>
            <button type="submit" class="cinematic-btn">
                <span>Acceder al Panel</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');

:root {
    --primary: #47b5ff;
    --accent: #06dbac;
    --bg-dark: #020617;
    --glass: rgba(255, 255, 255, 0.03);
    --border: rgba(255, 255, 255, 0.12);
}

* {
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.cinematic-bg {
    min-height: 100vh;
    background: #020617;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Orbes de luz animados */
.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    z-index: 1;
}
.orb-1 { width: 400px; height: 400px; background: rgba(71, 181, 255, 0.15); top: -100px; left: -100px; }
.orb-2 { width: 350px; height: 350px; background: rgba(6, 219, 172, 0.1); bottom: -50px; right: -50px; }

/* Tarjeta Principal */
.login-glass {
    width: 100%;
    max-width: 440px;
    padding: 48px;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid var(--border);
    border-radius: 32px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    z-index: 10;
    margin: 20px;
}

/* Branding */
.brand { text-align: center; margin-bottom: 32px; }
.logo-container {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    margin: 0 auto 16px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #000;
    box-shadow: 0 0 30px rgba(71, 181, 255, 0.4);
}

.brand h1 { color: #fff; font-size: 28px; font-weight: 800; letter-spacing: -1px; margin: 0; }
.brand h1 span { color: var(--primary); }
.brand p { color: #94a3b8; font-size: 14px; margin-top: 4px; }

/* Formulario */
.input-group {
    margin-bottom: 20px;
    display: block ;
}
.input-group label {
    display: block;
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
    margin-left: 4px;
}

.input-wrap { position: relative; }
.input-wrap i:not(.eye i) {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    transition: 0.3s;
}

.input-wrap input {
    width: 100%;
    padding: 14px 16px 14px 48px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border);
    border-radius: 14px;
    color: #fff;
    font-size: 15px;
    transition: all 0.3s ease;
}

.input-wrap input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(71, 181, 255, 0.1);
}

.input-wrap input:focus + i { color: var(--primary); }

.eye {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    cursor: pointer;
}

/* Opciones Extra */
.options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    font-size: 13px;
}

.remember-me { color: #94a3b8; cursor: pointer; display: flex; align-items: center; gap: 8px; }
.options a { color: var(--primary); text-decoration: none; font-weight: 600; }
.options a:hover { text-decoration: underline; }

/* Botón Principal */
.cinematic-btn {
    width: 100%;
    padding: 16px;
    background: var(--primary);
    border: none;
    border-radius: 14px;
    color: #020617;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.cinematic-btn:hover {
    background: #70c5ff;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(71, 181, 255, 0.3);
}

/* Social Logins */
.divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
}
.divider::before {
    content: "";
    position: absolute;
    top: 50%; left: 0; right: 0;
    height: 1px; background: var(--border);
}
.divider span {
    position: relative;
    background: #0f172a;
    padding: 0 12px;
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.social-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.social-btn {
    padding: 12px;
    background: var(--glass);
    border: 1px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    justify-content: center;
    align-items: center;
}

.social-btn img { width: 20px; }
.social-btn i { color: #fff; font-size: 20px; }
.social-btn:hover { background: rgba(255,255,255,0.1); }

</style>

<script>
function togglePass() {
    const input = document.getElementById('passInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</x-guest-layout>
