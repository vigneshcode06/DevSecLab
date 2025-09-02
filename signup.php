<?php
require_once 'includes/auth.php';

if (is_logged_in()) { redirect('dashboard.php'); }

$error_message = '';
$success_message = '';
$fullname = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
  $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
  $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirmPassword'] ?? '';
  $terms = isset($_POST['terms']);

  if (empty($fullname)) { $error_message = 'Full name is required';
  } else if (empty($email)) { $error_message = 'Email is required';
  } else if (!is_valid_email($email)) { $error_message = 'Please enter a valid email address';
  } else if (empty($username)) { $error_message = 'Username is required';
  } else if (strlen($username) < 3) { $error_message = 'Username must be at least 3 characters';
  } else if (empty($password)) { $error_message = 'Password is required';
  } else if (!is_valid_password($password)) { $error_message = 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number';
  } else if ($password !== $confirm_password) { $error_message = 'Passwords do not match';
  } else if (!$terms) { $error_message = 'You must accept the Terms and Conditions';
  } else {
$result = user_register($userData);

if ($result && isset($result['success']) && $result['success']) {
    $folderPath = "C:/xampp/htdocs/DevSecLab/user_labs/" . $username;

    if (!file_exists($folderPath)) { mkdir($folderPath, 0777, true); }

    // Call Python script to create lab folders + Dockerfiles
    $pythonScript = "C:/xampp/htdocs/DevSecLab/scripts/create_user_lab.py";
    $cmd = escapeshellcmd("python3 {$pythonScript} {$username}");
    shell_exec($cmd);

    redirect('dashboard.php');
} else {
    $error_message = $result['message'] ?? 'Unknown registration error';
}
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VLab - Sign Up</title>
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

    .auth-container{ width:min(1060px, 96vw); display:grid; gap:14px; padding:24px }
    .auth-card{
      display:grid; grid-template-columns: 1.1fr .9fr; min-height: 600px; border-radius: var(--radius);
      background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(0,0,0,.25)), var(--panel);
      border:1px solid var(--panel-border); box-shadow: var(--shadow); overflow:hidden; position:relative;
    }
    @media (max-width: 980px){ .auth-card{ grid-template-columns:1fr; min-height:auto } }

    /* Left panel with illustration */
    .auth-header{
      padding:24px; border-right:1px solid var(--panel-border);
      display:flex; flex-direction:column; gap:16px; background: rgba(10,16,30,.45); backdrop-filter: blur(12px);
    }
    .brand{ display:flex; align-items:center; gap:.7rem; }
    .logo-v{
      width:34px; height:34px; border-radius:10px; display:grid; place-items:center;
      background: radial-gradient(circle at 30% 30%, #fff, #7dd3fc 20%, #0891b2 55%, #0ea5e9 70%, #1e40af 100%);
      color:#0b1220; box-shadow:0 6px 22px rgba(0,180,255,.35); font-weight:900;
    }
    .brand .title{ font-weight:900; letter-spacing:.4px; font-size:1.2rem }
    .subtitle{ color:#cfe6ff; opacity:.85 }

    .ill-wrap{ position:relative; flex:1; display:grid; place-items:center; min-height:360px; }
    .ill{ width:100%; max-width:520px; height:auto; filter: drop-shadow(0 30px 60px rgba(0,0,0,.45)); }
    .ill-float { animation: illFloat 10s ease-in-out infinite; }
    @keyframes illFloat { 0%,100%{ transform: translateY(0) } 50%{ transform: translateY(-10px) } }

    /* Right panel form */
    .auth-form{ padding:24px; display:grid; align-content:center; gap:14px }
    .auth-form h2{ margin:0 0 6px 0; font-size:1.2rem; letter-spacing:.3px }
    form{ display:grid; gap:10px }

    .form-group{ display:grid; gap:6px }
    label{ font-size:.85rem; color:#cfe6ff; display:flex; align-items:center; gap:.5rem }

    /* Neutral defaults: no red underline by default */
    input[type="text"], input[type="email"], input[type="password"]{
      width:100%; padding:.8rem 1rem; color:var(--text);
      background: rgba(10,16,32,.7);
      border:1px solid rgba(255,255,255,.12);
      border-radius:12px; outline:none;
      transition: border .15s ease, box-shadow .15s ease;
    }
    input, textarea{ border-bottom-color: transparent; }
    input:focus{ border-color: var(--brand); box-shadow: var(--ring); outline:none; }

    .form-error{ color:#fecaca; background: rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35); padding:.6rem .8rem; border-radius:10px; text-align:center }
    .field-error{ color:#fecaca; font-size:.8rem; margin-top:2px }
    input[aria-invalid="true"], input.is-invalid{ border-color:#ef4444 !important; box-shadow:0 0 0 3px rgba(239,68,68,.25) !important; }

    .form-options{ display:flex; align-items:center; justify-content:space-between; gap:12px }
    .terms-agreement{ display:flex; align-items:center; gap:.5rem; color:#cfe6ff }

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
      .header-accent{ animation:none }
      .ill-float{ animation:none }
    }
  </style>
</head>
<body>
  <div class="header-accent"></div>

  <div class="auth-container">
    <div class="auth-card">
      <!-- LEFT PANEL WITH ILLUSTRATION -->
      <div class="auth-header">
        <div class="brand">
          <div class="logo-v">V</div>
          <div>
            <div class="title">LAB</div>
            <div class="subtitle">Virtual Laboratory Environment</div>
          </div>
        </div>

        <div class="ill-wrap">
          <!-- Animated Cyber Illustration (SVG only) -->
          <svg class="ill ill-float" viewBox="0 0 520 400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Cyber docker/linux network">
            <defs>
              <pattern id="grid" width="24" height="24" patternUnits="userSpaceOnUse">
                <path d="M24 0H0V24" fill="none" stroke="rgba(255,255,255,0.06)"/>
              </pattern>
              <linearGradient id="grad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#00b4ff"/>
                <stop offset="100%" stop-color="#4f46e5"/>
              </linearGradient>
              <filter id="glow"><feGaussianBlur stdDeviation="3.5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
            </defs>

            <!-- subtle grid -->
            <rect x="0" y="0" width="520" height="400" fill="url(#grid)"/>

            <!-- network path -->
            <path id="net" d="M20 360 C120 330 110 260 220 260 S360 300 480 220
                               C505 200 430 150 370 150 S260 175 230 120
                               C200 70 120 60 60 100" fill="none" stroke="url(#grad)" stroke-width="2.6" opacity=".95" filter="url(#glow)"/>

            <!-- moving pulse -->
            <circle r="6" fill="#7dd3fc">
              <animateMotion dur="9s" repeatCount="indefinite" rotate="auto">
                <mpath xlink:href="#net"/>
              </animateMotion>
            </circle>

            <!-- nodes -->
            <g fill="#7dd3fc" opacity=".9">
              <circle cx="60" cy="100" r="4"/><circle cx="230" cy="120" r="4"/><circle cx="370" cy="150" r="4"/><circle cx="480" cy="220" r="4"/><circle cx="220" cy="260" r="4"/>
            </g>

            <!-- Docker whale (blocks) -->
            <g transform="translate(150,230)">
              <rect x="-18" y="-12" width="12" height="10" rx="1.5" fill="#93c5fd"/>
              <rect x="-4"  y="-12" width="12" height="10" rx="1.5" fill="#93c5fd"/>
              <rect x="10"  y="-12" width="12" height="10" rx="1.5" fill="#93c5fd"/>
              <rect x="-4"  y="-24" width="12" height="10" rx="1.5" fill="#93c5fd"/>
              <path d="M-30 -2 h76 c0 18 -16 32 -38 32 -20 0 -30 -9 -38 -17" fill="#38bdf8" opacity=".9"/>
            </g>

            <!-- Linux penguin (simplified) -->
            <g transform="translate(380,110)">
              <ellipse cx="0" cy="14" rx="20" ry="24" fill="#0ea5e9" opacity=".85"/>
              <circle cx="0" cy="0" r="11" fill="#1e3a8a"/>
              <circle cx="-3" cy="-2" r="2" fill="#fff"/><circle cx="3" cy="-2" r="2" fill="#fff"/>
              <path d="M-12 24 q12 8 24 0" stroke="#93c5fd" stroke-width="2" fill="none"/>
            </g>

            <!-- Terminal window -->
            <g transform="translate(90,120)">
              <rect x="-36" y="-26" width="120" height="70" rx="10" fill="#0b1324" stroke="rgba(255,255,255,.12)"/>
              <circle cx="-26" cy="-18" r="3" fill="#ef4444"/><circle cx="-18" cy="-18" r="3" fill="#f59e0b"/><circle cx="-10" cy="-18" r="3" fill="#22c55e"/>
              <text x="-26" y="0" font-size="12" fill="#9ddcff" font-family="monospace">&gt; docker compose up</text>
              <text x="-26" y="18" font-size="12" fill="#86efac" font-family="monospace">running...</text>
            </g>

            <!-- particles -->
            <g>
              <circle cx="60" cy="300" r="3" fill="#38bdf8"><animate attributeName="opacity" values="0;1;0" dur="6s" repeatCount="indefinite"/></circle>
              <circle cx="300" cy="60" r="4" fill="#60a5fa"><animate attributeName="opacity" values="0;1;0" dur="8s" repeatCount="indefinite"/></circle>
              <circle cx="480" cy="300" r="3" fill="#38bdf8"><animate attributeName="opacity" values="0;1;0" dur="7s" repeatCount="indefinite"/></circle>
            </g>
          </svg>
        </div>
      </div>

      <!-- RIGHT PANEL FORM -->
      <div class="auth-form">
        <h2>Create Account</h2>
        <form id="signupForm" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
          <?php if (!empty($error_message)): ?>
            <div class="form-error" role="alert"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>

          <div class="form-group">
            <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($fullname) ?>" required>
            <span class="field-error" id="fullnameError" aria-live="polite"></span>
          </div>
          <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <span class="field-error" id="emailError" aria-live="polite"></span>
          </div>
          <div class="form-group">
            <label for="username"><i class="fas fa-user-shield"></i> Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
            <span class="field-error" id="usernameError" aria-live="polite"></span>
          </div>
          <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" required>
            <span class="field-error" id="passwordError" aria-live="polite"></span>
          </div>
          <div class="form-group">
            <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" required>
            <span class="field-error" id="confirmPasswordError" aria-live="polite"></span>
          </div>

          <div class="form-options">
            <div class="terms-agreement">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
            </div>
          </div>

          <button type="submit" class="btn-primary">SIGN UP <i class="fas fa-user-plus"></i></button>

          <div class="auth-redirect">
            Already have an account? <a href="index.php">Login</a>
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
      const form = document.getElementById('signupForm');
      const fields = {
        fullname: document.getElementById('fullname'),
        email: document.getElementById('email'),
        username: document.getElementById('username'),
        password: document.getElementById('password'),
        confirmPassword: document.getElementById('confirmPassword'),
        terms: document.getElementById('terms')
      };
      const errs = {
        fullname: document.getElementById('fullnameError'),
        email: document.getElementById('emailError'),
        username: document.getElementById('usernameError'),
        password: document.getElementById('passwordError'),
        confirmPassword: document.getElementById('confirmPasswordError')
      };
      function isValidEmail(e){ return /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(e).toLowerCase()); }
      function isValidPassword(p){ return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(p); }

      Object.values(fields).forEach(el=>{
        if(el instanceof HTMLInputElement){
          el.addEventListener('input', ()=>{ el.removeAttribute('aria-invalid'); const id=el.id; if(errs[id]) errs[id].textContent=''; });
        }
      });

      form.addEventListener('submit', function(e){
        let ok = true;
        Object.values(errs).forEach(s=> s.textContent='');
        ['fullname','email','username','password','confirmPassword'].forEach(id=>fields[id].removeAttribute('aria-invalid'));

        if(!fields.fullname.value.trim()){ errs.fullname.textContent='Full name is required'; fields.fullname.setAttribute('aria-invalid','true'); ok=false; }
        if(!fields.email.value.trim()){ errs.email.textContent='Email is required'; fields.email.setAttribute('aria-invalid','true'); ok=false; }
        else if(!isValidEmail(fields.email.value)){ errs.email.textContent='Please enter a valid email address'; fields.email.setAttribute('aria-invalid','true'); ok=false; }

        const uname = fields.username.value.trim();
        if(!uname){ errs.username.textContent='Username is required'; fields.username.setAttribute('aria-invalid','true'); ok=false; }
        else if(uname.length < 3){ errs.username.textContent='Username must be at least 3 characters'; fields.username.setAttribute('aria-invalid','true'); ok=false; }

        const pw = fields.password.value;
        if(!pw){ errs.password.textContent='Password is required'; fields.password.setAttribute('aria-invalid','true'); ok=false; }
        else if(!isValidPassword(pw)){ errs.password.textContent='Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number'; fields.password.setAttribute('aria-invalid','true'); ok=false; }

        if(pw !== fields.confirmPassword.value){ errs.confirmPassword.textContent='Passwords do not match'; fields.confirmPassword.setAttribute('aria-invalid','true'); ok=false; }

        if(!fields.terms.checked){ ok=false; }
        if(!ok) e.preventDefault();
      });
    });
  </script>
</body>
</html>
