<x-guest-layout>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<div class="cinematic-bg">

<div class="bg-texture"></div>
<div class="ambient-glow"></div>

<div class="login-glass">

<div class="brand">
    <div class="logo"><i class="fa-solid fa-car-side"></i></div>
    <h1>PARK <span>EASY</span></h1>
    <p>Sistema de Control Operativo</p>
</div>

<form method="POST" action="{{ route('login') }}">
@csrf

<div class="input-wrap glow-field">
    <i class="fa-regular fa-envelope"></i>
    <input type="email" name="email" placeholder="Correo electrónico" required>
</div>

<div class="input-wrap glow-field">
    <i class="fa-solid fa-lock"></i>
    <input type="password" name="password" placeholder="Contraseña" required>
    <span class="eye"><i class="fa-regular fa-eye"></i></span>
</div>

<div class="options">
    <label><input type="checkbox" name="remember"> Recordarme</label>
    <a href="{{ route('password.request') }}">¿Olvidaste contraseña?</a>
</div>

<button class="cinematic-btn">Iniciar Sesión</button>

<div class="social">
    <span>Continuar con</span>
    <div>
        <i class="fa-brands fa-google"></i>
        <i class="fa-brands fa-facebook-f"></i>
    </div>
</div>

</form>
</div>
</div>

<style>

/* BACKGROUND */

.cinematic-bg{
height:100vh;
background:
radial-gradient(circle at 20% 20%,rgba(90,160,255,.22),transparent 42%),
radial-gradient(circle at 80% 75%,rgba(80,220,200,.22),transparent 45%),
linear-gradient(135deg,#020617,#071b34,#020617);
display:flex;
align-items:center;
justify-content:center;
position:relative;
overflow:hidden;
}

.bg-texture{
position:absolute;
inset:0;
background:
repeating-linear-gradient(135deg,rgba(255,255,255,.018) 0 1px,transparent 1px 70px);
}

/* REAL GLOW (not shadow) */

.ambient-glow{
position:absolute;
bottom:-150px;
width:720px;
height:300px;
background:radial-gradient(ellipse,rgba(120,200,255,.65),transparent 72%);
filter:blur(130px);
}

/* GLASS CARD */

.login-glass{
width:420px;
padding:54px;
border-radius:40px;
background:rgba(22,40,75,.55);
backdrop-filter:blur(30px);
border:1px solid rgba(255,255,255,.22);
box-shadow:
0 40px 120px rgba(0,0,0,.8),
inset 0 0 70px rgba(255,255,255,.04),
0 0 40px rgba(120,220,255,.28);
z-index:2;
}

/* BRAND */

.brand{text-align:center;margin-bottom:40px;}

.logo{
font-size:48px;
color:white;
margin-bottom:14px;
text-shadow:0 0 30px rgba(150,230,255,1);
}

.brand h1{color:white;font-size:38px;letter-spacing:1px;}
.brand span{color:#43ffd2;}
.brand p{color:#a9c2e6;}

/* INPUTS */

.input-wrap{
position:relative;
margin-bottom:18px;
}

.input-wrap i{
position:absolute;
left:18px;
top:50%;
transform:translateY(-50%);
color:#9fb7df;
}

.input-wrap input{
width:100%;
padding:16px 58px 16px 54px;
border-radius:18px;
border:none;
outline:none;
font-size:15px;
background:white;
box-shadow:0 0 0 1px rgba(120,220,255,.28);
transition:.25s;
}

.glow-field input:focus{
box-shadow:
0 0 0 2px #5fd6ff,
0 0 40px rgba(120,220,255,.9);
}

/* eye inside input */

.eye{
position:absolute;
right:15%;
top:50%;
transform:translateY(-50%);
color:#9fb7df;
cursor:pointer;
}

/* OPTIONS */

.options{
display:flex;
justify-content:space-between;
font-size:14px;
color:#c6d7f3;
margin:12px 0 30px;
}

.options a{color:#74d4ff;text-decoration:none;}

/* BUTTON */

.cinematic-btn{
width:100%;
padding:18px;
border:none;
border-radius:56px;
font-size:18px;
font-weight:600;
cursor:pointer;
background:linear-gradient(90deg,#4cbcff,#ff9f43);
color:#000;
box-shadow:
0 0 50px rgba(120,220,255,1),
0 0 130px rgba(255,160,80,.65);
transition:.3s;
}

.cinematic-btn:hover{
transform:translateY(-2px) scale(1.04);
}

/* SOCIAL */

.social{
margin-top:28px;
text-align:center;
color:#a9c2e6;
font-size:14px;
}

.social div{
margin-top:12px;
display:flex;
justify-content:center;
gap:18px;
}

.social i{
font-size:22px;
background:rgba(255,255,255,.15);
padding:12px;
border-radius:50%;
cursor:pointer;
transition:.25s;
}

.social i:hover{
background:rgba(255,255,255,.32);
}

</style>
</x-guest-layout>
