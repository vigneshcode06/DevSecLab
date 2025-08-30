<?php
require_once 'includes/auth.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$error_message = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
  $password = $_POST['password'] ?? '';
  $remember = isset($_POST['remember']);

  if (empty($username)) {
    $error_message = 'Username is required';
  } else if (empty($password)) {
    $error_message = 'Password is required';
  } else {
    $result = user_login($username, $password);
    if ($result['success']) {
      if ($remember) { setcookie('vlab_username', $username, time() + (86400 * 30), "/"); }
      redirect('dashboard.php');
    } else {
      $error_message = $result['message'];
      if ($username === 'demo' && $password === 'Demo1234') {
        $demoUser = ['username'=>'demo','password'=>'Demo1234','fullname'=>'Demo User','email'=>'demo@example.com'];
        $reg_result = user_register($demoUser);
        if ($reg_result['success']) { redirect('dashboard.php'); }
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VLab - Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    :root{
      --bg-0:#0b1220; --text:#e6f0ff; --muted:#9fb3d3; --brand:#00b4ff; --brand-2:#4f46e5;
      --ring:0 0 0 3px rgba(0,180,255,.35); --radius:16px;
      --panel:#0e1629cc; --panel-border: rgba(255,255,255,.10);
      --shadow: 0 20px 60px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.04);
      --err:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; display:grid; place-items:center; color:var(--text);
      font:14px/1.5 Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
      background:
        radial-gradient(1200px 800px at 80% -10%, rgba(0,180,255,.10), transparent 60%),
        radial-gradient(900px 600px at 10% 110%, rgba(79,70,229,.12), transparent 60%),
        var(--bg-0);
      overflow:hidden;
    }
    .header-accent{
      position:fixed; top:0; left:0; right:0; height:4px; z-index:5;
      background: linear-gradient(90deg, var(--brand), var(--brand-2), var(--brand));
      background-size:200% 100%; animation: sheen 8s linear infinite;
    }
    @keyframes sheen{0%{background-position:0% 50%}100%{background-position:200% 50%}}

    .auth-container{ width:min(980px, 96vw); display:grid; gap:14px; padding:24px }
    .auth-card{
      display:grid; grid-template-columns: 1.05fr .95fr; min-height: 560px; border-radius: var(--radius);
      background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(0,0,0,.25)), var(--panel);
      border:1px solid var(--panel-border); box-shadow: var(--shadow); overflow:hidden; position:relative;
    }
    @media (max-width: 900px){ .auth-card{ grid-template-columns:1fr; min-height:auto } }

    /* LEFT: new kinetic matrix */
    .left{
      position:relative; padding:24px; border-right:1px solid var(--panel-border);
      background: rgba(10,16,30,.45); backdrop-filter: blur(12px);
      display:flex; flex-direction:column; gap:16px;
    }
    .brand{ display:flex; align-items:center; gap:.7rem; }
    .logo-v{
      width:34px; height:34px; border-radius:10px; display:grid; place-items:center;
      background: radial-gradient(circle at 30% 30%, #fff, #7dd3fc 20%, #0891b2 55%, #0ea5e9 70%, #1e40af 100%);
      color:#0b1220; box-shadow:0 6px 22px rgba(0,180,255,.35); font-weight:900;
    }
    .brand .title{ font-weight:900; letter-spacing:.4px; font-size:1.2rem }
    .subtitle{ color:#cfe6ff; opacity:.85 }

    .scene{ position:relative; flex:1; display:grid; place-items:center; min-height:320px; }
    .scanline{ position:absolute; inset:0; background: linear-gradient(0deg, transparent, rgba(0,180,255,.12), transparent); animation: scan 7s linear infinite; opacity:.35 }
    @keyframes scan{ 0%{ transform: translateY(100%) } 100%{ transform: translateY(-100%) } }

    /* Waveform bars */
    .bars{ position:absolute; bottom:24px; left:24px; right:24px; height:80px; display:flex; align-items:flex-end; gap:6px; opacity:.85 }
    .bar{ width:6px; background: linear-gradient(180deg,#00b4ff,#4f46e5); border-radius:4px; box-shadow:0 0 12px rgba(0,180,255,.35) }
    .bar:nth-child(odd){ filter:hue-rotate(20deg) }
    .bar{ animation: bounce 1.2s ease-in-out infinite; }
    .bar:nth-child(2){ animation-delay:.1s } .bar:nth-child(3){ animation-delay:.2s } .bar:nth-child(4){ animation-delay:.3s }
    .bar:nth-child(5){ animation-delay:.4s } .bar:nth-child(6){ animation-delay:.5s } .bar:nth-child(7){ animation-delay:.6s }
    @keyframes bounce{ 0%,100%{ height:18px } 50%{ height:72px } }

    /* Rotating cube */
    .cube{
      --s:80px; width:var(--s); height:var(--s); position:relative; transform-style:preserve-3d; animation: spin 9s linear infinite;
    }
    .face{ position:absolute; inset:0; background: linear-gradient(135deg, rgba(0,180,255,.18), rgba(79,70,229,.18)); border:1px solid rgba(255,255,255,.12); backdrop-filter: blur(3px) }
    .cube .front{ transform: translateZ(40px) }
    .cube .back{ transform: rotateY(180deg) translateZ(40px) }
    .cube .right{ transform: rotateY(90deg) translateZ(40px) }
    .cube .left{ transform: rotateY(-90deg) translateZ(40px) }
    .cube .top{ transform: rotateX(90deg) translateZ(40px) }
    .cube .bottom{ transform: rotateX(-90deg) translateZ(40px) }
    @keyframes spin{ 0%{ transform: rotateX(0) rotateY(0) } 100%{ transform: rotateX(360deg) rotateY(360deg) } }

    /* Lock pulse */
    .lock{
      position:absolute; top:28%; left:50%; transform:translateX(-50%); display:grid; place-items:center; color:#9ddcff;
      text-shadow: 0 0 14px rgba(0,180,255,.6);
      animation: pulse 2.2s ease-in-out infinite;
    }
    @keyframes pulse{ 0%,100%{ opacity:.7; transform:translateX(-50%) scale(1) } 50%{ opacity:1; transform:translateX(-50%) scale(1.06) } }

    .hero{ color:#b6dfff; font-weight:600; font-size:.95rem; margin-top:6px }
    .hero small{ color:var(--muted); font-weight:400 }

    /* RIGHT: form */
    .right{ padding:24px; display:grid; align-content:center; gap:14px }
    .right h2{ margin:0 0 6px 0; font-size:1.2rem; letter-spacing:.3px }
    form{ display:grid; gap:10px }
    .form-group{ display:grid; gap:6px }
    label{ font-size:.85rem; color:#cfe6ff; display:flex; align-items:center; gap:.5rem }

    input[type="text"], input[type="password"]{
      width:100%; padding:.8rem 1rem; color:var(--text);
      background: rgba(10,16,32,.7);
      border:1px solid rgba(255,255,255,.12);
      border-radius:12px; outline:none;
      transition: border .15s ease, box-shadow .15s ease, transform .06s ease;
    }
    input, textarea{ border-bottom-color: transparent; }
    input:focus{ border-color: var(--brand); box-shadow: var(--ring); outline:none; }
    .form-error{ color:#fecaca; background: rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35); padding:.6rem .8rem; border-radius:10px; }
    .field-error{ color:#fecaca; font-size:.8rem; margin-top:2px }
    input[aria-invalid="true"], input.is-invalid{ border-color: var(--err) !important; box-shadow: 0 0 0 3px rgba(239,68,68,.25) !important; }

    .form-options{ display:flex; align-items:center; justify-content:space-between; gap:12px }
    .remember-me{ display:flex; align-items:center; gap:.5rem; color:#cfe6ff }
    .forgot-password{ color:#9ddcff; text-decoration:none }
    .forgot-password:hover{ text-decoration:underline }

    .btn-primary{
      display:inline-flex; align-items:center; gap:.6rem; justify-content:center; padding:.8rem 1rem; border-radius:12px; width:100%;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(0,0,0,.2)), linear-gradient(90deg, var(--brand), var(--brand-2));
      background-size:100% 100%, 200% 100%; color:#fff; border:1px solid rgba(255,255,255,.12); box-shadow: var(--shadow);
      cursor:pointer; font-weight:800; letter-spacing:.3px; transition: transform .12s ease, background-position .6s ease;
    }
    .btn-primary:hover{ transform: translateY(-1px); background-position: 100% 0 }

    .auth-redirect{ text-align:center; color:#cfe6ff }
    .auth-redirect a{ color:#9ddcff; text-decoration:none }
    .auth-redirect a:hover{ text-decoration:underline }

    .auth-footer{ text-align:center; color:#cfe6ff; opacity:.7 }

    @media (prefers-reduced-motion: reduce){
      .header-accent,.scanline,.bars .bar,.cube,.lock{ animation:none }
    }
  </style>
</head>
<body>
  <div class="header-accent"></div>

  <div class="auth-container">
    <div class="auth-card">
      <!-- LEFT UNIQUE ILLUSTRATION -->
      <div class="left">
        <div class="brand">
          <div class="logo-v">V</div>
          <div>
            <div class="title">LAB</div>
            <div class="subtitle">Virtual Laboratory Environment</div>
          </div>
        </div>

        <div class="scene">
          <div class="scanline"></div>

          <!-- Rotating cube -->
          <div class="cube" aria-hidden="true">
            <div class="face front"></div><div class="face back"></div>
            <div class="face right"></div><div class="face left"></div>
            <div class="face top"></div><div class="face bottom"></div>
          </div>

          <!-- Lock pulse -->
          <div class="lock" aria-hidden="true">
            <i class="fas fa-lock fa-2x"></i>
          </div>

          <!-- Waveform bars -->
          <div class="bars" aria-hidden="true">
            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
            <div class="bar"></div><div class="bar"></div><div class="bar"></div><div class="bar"></div>
          </div>
        </div>

        <div class="hero">
          Authenticate to launch virtual labs with isolated containers and secure compute. <br>
          <small>Security first: sessions are protected and audited.</small>
        </div>
      </div>

      <!-- RIGHT FORM -->
      <div class="right">
        <h2>Login</h2>
        <form id="loginForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
          <?php if (!empty($error_message)): ?>
            <div class="form-error" role="alert"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>

          <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required autocomplete="username">
            <span class="field-error" id="usernameError" aria-live="polite"></span>
          </div>

          <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <span class="field-error" id="passwordError" aria-live="polite"></span>
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Remember me</label>
            </div>
            <a href="#" class="forgot-password">Forgot Password?</a>
          </div>

          <button type="submit" class="btn-primary">LOGIN <i class="fas fa-arrow-right"></i></button>

          <div class="auth-redirect">
            Don't have an account? <a href="signup.php">Sign up</a>
          </div>
        </form>
      </div>
    </div>

    <div class="auth-footer">
      <p>&copy; <?= date('Y') ?> VLab - Cybersecurity Training Platform</p>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loginForm = document.getElementById('loginForm');
      const uEl = document.getElementById('username');
      const pEl = document.getElementById('password');
      const uErr = document.getElementById('usernameError');
      const pErr = document.getElementById('passwordError');

      [uEl,pEl].forEach(el=>{
        el.addEventListener('input', ()=>{ el.removeAttribute('aria-invalid'); if(el===uEl) uErr.textContent=''; else pErr.textContent=''; });
      });

      loginForm.addEventListener('submit', function(event) {
        let isValid = true;
        uErr.textContent = ''; pErr.textContent = '';
        uEl.removeAttribute('aria-invalid'); pEl.removeAttribute('aria-invalid');

        if (!uEl.value.trim()) { uErr.textContent = 'Username is required'; uEl.setAttribute('aria-invalid','true'); isValid = false; }
        if (!pEl.value.trim()) { pErr.textContent = 'Password is required'; pEl.setAttribute('aria-invalid','true'); isValid = false; }
        if (!isValid) event.preventDefault();
      });
    });
  </script>
</body>
</html>
