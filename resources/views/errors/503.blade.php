<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TecnoByte | Mantenimiento</title>

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
    }
    *{box-sizing:border-box}
    body{
      margin:0; min-height:100vh; display:grid; place-items:center;
      background: var(--bg); color:white;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      overflow:hidden; padding: 24px;
    }

    /* --- ESTILOS DE LA PANTALLA DE BIENVENIDA (SPLASH) --- */
    #splash-screen {
      position: fixed; inset: 0; z-index: 9999;
      background: var(--bg);
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      transition: opacity 0.8s ease, visibility 0.8s;
    }
    .splash-hidden { opacity: 0; visibility: hidden; pointer-events: none; }
    
    .enter-btn {
      margin-top: 20px;
      padding: 12px 30px;
      background: var(--orange);
      color: white; font-weight: bold; border: none; border-radius: 50px;
      font-size: 16px; cursor: pointer;
      box-shadow: 0 0 20px rgba(255,149,33,0.4);
      animation: pulseBtn 2s infinite;
      transition: transform 0.2s;
    }
    .enter-btn:hover { transform: scale(1.05); }
    .enter-btn:active { transform: scale(0.95); }
    
    @keyframes pulseBtn {
      0% { box-shadow: 0 0 0 0 rgba(255,149,33, 0.7); }
      70% { box-shadow: 0 0 0 15px rgba(255,149,33, 0); }
      100% { box-shadow: 0 0 0 0 rgba(255,149,33, 0); }
    }
    /* ----------------------------------------------------- */

    .glow{position:fixed; inset:0; pointer-events:none;}
    .blob{position:absolute; width:520px; height:520px; border-radius:999px; filter: blur(60px);}
    .b1{top:-140px; left:-160px; background: var(--blue);}
    .b2{top:25%; right:-180px; background: var(--indigo);}
    .b3{bottom:-220px; left:30%; width:720px; height:720px; background: var(--orangeGlow);}
    .shade{position:absolute; inset:0; background: linear-gradient(to bottom, rgba(0,0,0,.25), transparent, rgba(0,0,0,.45));}
    .noise{position:absolute; inset:0; opacity:.06; background-image: radial-gradient(rgba(255,255,255,.9) 1px, transparent 1px); background-size: 18px 18px;}
    .wrap{width:100%; max-width:560px;}
    .brand{text-align:center; margin-bottom:18px;}
    .logo{width:56px; height:56px; margin:0 auto; border-radius:18px; background: rgba(255,255,255,.05); border: 1px solid var(--border); display:grid; place-items:center; box-shadow: 0 18px 50px rgba(0,0,0,.55); backdrop-filter: blur(16px);}
    .logo span{color:var(--orange); font-weight:800; letter-spacing:.5px;}
    .brand h1{margin:14px 0 4px; font-size:22px; font-weight:800; letter-spacing:.4px;}
    .brand p{margin:0; color: var(--muted); font-size:13px;}
    .card{border-radius:26px; background: var(--glass); border:1px solid var(--border); box-shadow: 0 30px 80px rgba(0,0,0,.65); backdrop-filter: blur(18px); overflow:hidden;}
    .topbar{display:flex; align-items:center; justify-content:space-between; padding:16px 18px; border-bottom: 1px solid rgba(255,255,255,.10);}
    .pill{font-size:11px; padding:6px 10px; border-radius:999px; color: var(--orange); background: rgba(255,149,33,.12); border: 1px solid rgba(255,149,33,.30);}
    .status{display:flex; align-items:center; gap:8px; font-size:12px; color: var(--muted);}
    .dot{width:8px; height:8px; border-radius:999px; background: var(--orange); box-shadow: 0 0 18px rgba(255,149,33,.75); animation: pulse 1.2s infinite;}
    @keyframes pulse{0%,100%{transform:scale(1); opacity:1} 50%{transform:scale(.72); opacity:.55}}
    .content{padding:22px 22px 18px; text-align:center;}
    .imgGlow{position:relative; width:120px; height:120px; margin: 8px auto 14px;}
    .imgGlow::before{content:""; position:absolute; inset:-14px; background: rgba(255,149,33,.14); filter: blur(22px); border-radius: 28px;}
    .imgBox{position:relative; width:120px; height:120px; border-radius:22px; background: rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); box-shadow: 0 18px 40px rgba(0,0,0,.55); overflow:hidden; display:grid; place-items:center;}
    .imgBox img{width:100%; height:100%; object-fit:cover;}
    .content h2{margin:10px 0 6px; font-size:18px; font-weight:800;}
    .content .txt{margin:0 auto; max-width:420px; font-size:13px; color: var(--muted); line-height:1.5;}
    .badge2{display:inline-flex; align-items:center; gap:8px; margin-top:14px; font-size:12px; color: var(--muted); padding:10px 12px; border-radius:999px; border:1px solid rgba(255,255,255,.10); background: rgba(255,255,255,.05);}
    .badge2 .dot2{width:8px; height:8px; border-radius:999px; background: rgba(59,130,246,.9); box-shadow: 0 0 16px rgba(59,130,246,.55); animation: pulse 1.4s infinite;}
    .footer{display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-top: 1px solid rgba(255,255,255,.10); font-size:12px; color: var(--muted2);}
    .below{margin-top:14px; text-align:center; font-size:11px; color: var(--muted2);}
  </style>
</head>
<body>

  <div id="splash-screen">
    <div class="logo" style="margin-bottom: 20px; transform: scale(1.5);"><span>TB</span></div>
    <h2 style="margin-bottom: 5px;">TecnoByte</h2>
    <p style="color:var(--muted); font-size: 13px;">Sitio en mantenimiento</p>
    
    <button class="enter-btn" id="enterSite">
      Ingresar
    </button>
  </div>
  <audio id="bgMusic" preload="auto" loop>
    <source src="{{ asset('audio/mantenimiento.mp3') }}" type="audio/mpeg">
  </audio>

  <button id="soundToggle" type="button" style="position:fixed; bottom:18px; right:18px; padding:10px 14px; border-radius:999px; border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.08); color:white; font-size:12px; cursor:pointer; backdrop-filter: blur(10px); z-index:50;">
    🔇 Silenciar
  </button>

  <div class="glow">
    <div class="blob b1"></div> <div class="blob b2"></div> <div class="blob b3"></div>
    <div class="shade"></div> <div class="noise"></div>
  </div>

  <div class="wrap">
    <div class="brand">
      <div class="logo"><span>TB</span></div>
      <h1>TecnoByte</h1>
      <p>Sistema en mantenimiento</p>
    </div>

    <div class="card">
      <div class="topbar">
        <div class="status"><span class="dot"></span> Actualizando…</div>
        <div class="pill">Modo mantenimiento</div>
      </div>

      <div class="content">
        <div class="imgGlow">
          <div class="imgBox">
            <img src="{{ asset('images/mantenimiento.gif') }}" alt="Mantenimiento">
          </div>
        </div>
        <h2>Estamos aplicando mejoras</h2>
        <p class="txt">
          En unos minutos estará disponible nuevamente.<br>
          Si eres técnico, por favor espera y vuelve a intentar.
        </p>
        <div class="badge2">
          <span class="dot2"></span> Gracias por tu paciencia 🙌
        </div>
      </div>

      <div class="footer">
        <span>© {{ date('Y') }} TecnoByte</span>
        <span>Preparación</span>
      </div>
    </div>
    <div class="below">Si necesitas acceso urgente, contacta a un administrador.</div>
  </div>

  <script>
    (function () {
      const splash = document.getElementById('splash-screen');
      const enterBtn = document.getElementById('enterSite');
      const music = document.getElementById('bgMusic');
      const toggleBtn = document.getElementById('soundToggle');

      music.volume = 0.4;

      // FUNCION PARA INICIAR TODO
      async function startExperience() {
        try {
          // 1. Iniciar música
          await music.play();
          toggleBtn.innerText = '🔇 Silenciar';
        } catch (e) {
          console.error("Audio falló", e);
          toggleBtn.innerText = '🔈 Activar sonido';
        }

        // 2. Desvanecer la pantalla de bienvenida
        splash.classList.add('splash-hidden');
      }

      // El usuario da clic en "Ingresar" -> BOOM, tenemos audio garantizado
      enterBtn.addEventListener('click', startExperience);

      // (Opcional) Si da clic en cualquier parte de la pantalla negra, también entra
      splash.addEventListener('click', (e) => {
         if(e.target !== enterBtn) startExperience();
      });

      // Lógica del botón pequeño de mute/unmute
      toggleBtn.addEventListener('click', () => {
        if (music.paused) {
          music.play();
          toggleBtn.innerText = '🔇 Silenciar';
        } else {
          music.pause();
          toggleBtn.innerText = '🔈 Activar sonido';
        }
      });
    })();
  </script>
</body>
</html>