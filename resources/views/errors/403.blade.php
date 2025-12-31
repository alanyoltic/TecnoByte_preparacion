<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TecnoByte | Acceso denegado (403)</title>

  <style>
    :root{
      --bg:#05070F;
      --glass: rgba(255,255,255,.06);
      --border: rgba(255,255,255,.12);
      --muted: rgba(226,232,240,.70);
      --muted2: rgba(226,232,240,.55);
      --orange:#FF9521;
      --blue: rgba(59,130,246,.28);
      --indigo: rgba(99,102,241,.26);
      --orangeGlow: rgba(255,149,33,.18);
      --warn: rgba(245,158,11,.95);
      --warnSoft: rgba(245,158,11,.12);
    }
    *{box-sizing:border-box}
    body{
      margin:0; min-height:100vh; display:grid; place-items:center;
      background: var(--bg); color:white;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      overflow:hidden; padding: 24px;
    }

    .glow{position:fixed; inset:0; pointer-events:none;}
    .blob{position:absolute; width:520px; height:520px; border-radius:999px; filter: blur(60px);}
    .b1{top:-140px; left:-160px; background: var(--blue);}
    .b2{top:25%; right:-180px; background: var(--indigo);}
    .b3{bottom:-220px; left:30%; width:720px; height:720px; background: var(--orangeGlow);}
    .shade{position:absolute; inset:0; background: linear-gradient(to bottom, rgba(0,0,0,.25), transparent, rgba(0,0,0,.45));}
    .noise{position:absolute; inset:0; opacity:.06; background-image: radial-gradient(rgba(255,255,255,.9) 1px, transparent 1px); background-size: 18px 18px;}

    .wrap{width:100%; max-width:560px;}
    .brand{text-align:center; margin-bottom:18px;}
    .logo{
      width:56px; height:56px; margin:0 auto;
      border-radius:18px; background: rgba(255,255,255,.05);
      border: 1px solid var(--border);
      display:grid; place-items:center;
      box-shadow: 0 18px 50px rgba(0,0,0,.55);
      backdrop-filter: blur(16px);
    }
    .logo span{color:var(--orange); font-weight:800; letter-spacing:.5px;}
    .brand h1{margin:14px 0 4px; font-size:22px; font-weight:800; letter-spacing:.4px;}
    .brand p{margin:0; color: var(--muted); font-size:13px;}

    .card{
      border-radius:26px;
      background: var(--glass);
      border:1px solid var(--border);
      box-shadow: 0 30px 80px rgba(0,0,0,.65);
      backdrop-filter: blur(18px);
      overflow:hidden;
    }
    .topbar{
      display:flex; align-items:center; justify-content:space-between;
      padding:16px 18px;
      border-bottom: 1px solid rgba(255,255,255,.10);
    }
    .pill{
      font-size:11px; padding:6px 10px; border-radius:999px;
      color: var(--warn);
      background: var(--warnSoft);
      border: 1px solid rgba(245,158,11,.35);
    }
    .status{display:flex; align-items:center; gap:8px; font-size:12px; color: var(--muted);}
    .dot{
      width:8px; height:8px; border-radius:999px;
      background: var(--warn);
      box-shadow: 0 0 18px rgba(245,158,11,.55);
      animation: pulse 1.2s infinite;
    }
    @keyframes pulse{0%,100%{transform:scale(1); opacity:1} 50%{transform:scale(.72); opacity:.55}}

    .content{padding:22px 22px 18px; text-align:center;}
    .badgeIcon{
      width:58px; height:58px; margin: 8px auto 14px;
      border-radius:18px; background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.12);
      box-shadow: 0 18px 40px rgba(0,0,0,.55);
      display:grid; place-items:center; position:relative;
    }
    .badgeIcon::before{
      content:""; position:absolute; inset:-14px;
      background: rgba(245,158,11,.14);
      filter: blur(22px);
      border-radius: 28px;
    }
    .badgeIcon svg{
      position:relative;
      width:26px; height:26px;
      color: rgba(245,158,11,.95);
    }

    .content h2{margin:10px 0 6px; font-size:18px; font-weight:800;}
    .content .txt{margin:0 auto; max-width:420px; font-size:13px; color: var(--muted); line-height:1.5;}

    .actions{
      display:flex; gap:10px; justify-content:center; flex-wrap:wrap;
      margin-top:16px;
    }
    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      padding:10px 14px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,.14);
      background: rgba(255,255,255,.06);
      color:white; font-size:12px; text-decoration:none;
      backdrop-filter: blur(10px);
      transition: transform .15s ease, background .15s ease, border-color .15s ease;
    }
    .btn:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.10);
      border-color: rgba(255,255,255,.18);
    }
    .btnPrimary{
      border-color: rgba(255,149,33,.30);
      background: rgba(255,149,33,.10);
    }
    .btnPrimary:hover{
      background: rgba(255,149,33,.14);
      border-color: rgba(255,149,33,.38);
    }

    .footer{
      display:flex; justify-content:space-between; align-items:center;
      padding:14px 18px;
      border-top: 1px solid rgba(255,255,255,.10);
      font-size:12px; color: var(--muted2);
    }
    .below{margin-top:14px; text-align:center; font-size:11px; color: var(--muted2);}
  </style>
</head>

<body>

  <div class="glow">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
    <div class="shade"></div>
    <div class="noise"></div>
  </div>

  <div class="wrap">
    <div class="brand">
      <div class="logo"><span>TB</span></div>
      <h1>TecnoByte</h1>
      <p>Acceso restringido</p>
    </div>

    <div class="card">
      <div class="topbar">
        <div class="status"><span class="dot"></span> Permisos insuficientes</div>
        <div class="pill">403</div>
      </div>

      <div class="content">
        <div class="badgeIcon" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 11V7a4 4 0 0 1 8 0v4"/>
            <rect x="4" y="11" width="16" height="9" rx="2"/>
          </svg>
        </div>

        <h2>No tienes permiso para acceder</h2>
        <p class="txt">
          Esta sección está restringida según tu rol actual.<br>
          Si necesitas acceso, solicita autorización a un administrador.
        </p>

        <div class="actions">
          <a class="btn" href="{{ url()->previous() }}">Volver</a>
          <a class="btn btnPrimary" href="{{ route('dashboard') }}">Ir al dashboard</a>
        </div>
      </div>

      <div class="footer">
        <span>© {{ date('Y') }} TecnoByte</span>
        <span>Preparación</span>
      </div>
    </div>

    <div class="below">
      Este intento fue bloqueado por el sistema de seguridad.
    </div>
  </div>

</body>
</html>
