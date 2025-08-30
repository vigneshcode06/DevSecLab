<?php
require_once 'includes/auth.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$lab_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$username = $_SESSION['username'];

$lab_folders = [
    1 => "ubuntu",
    2 => "java_dev",
    3 => "kali_linux",
    10 => "mysql_test"
];

$lab_folder = $lab_folders[$lab_id] ?? "ubuntu";
$user_lab_path = "C:/xampp/htdocs/DevSecLab/user_labs/$username/$lab_folder";

// Decide action: start or stop
$action = $_POST['action'] ?? 'start';

if ($action === 'stop') {
    $command = "cd /d $user_lab_path && docker compose down 2>&1";
    $title = "🛑 Stopping Lab Container";
} else {
    $command = "cd /d $user_lab_path && docker compose up -d 2>&1";
    $title = "🚀 Deploying Lab Container";
}

$output = shell_exec($command);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lab Deployment - <?= htmlspecialchars($username) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0b1220; --panel:#101827; --text:#e6f0ff; --muted:#9fb3d3;
      --brand:#00b4ff; --brand2:#4f46e5; --ring:0 0 0 3px rgba(0,180,255,.25);
      --ok:#22c55e; --warn:#f59e0b; --err:#ef4444; --radius:14px;
    }
    *{box-sizing:border-box}
    body{
      margin:0; padding:20px; background:
        radial-gradient(1000px 700px at 80% -10%, rgba(0,180,255,.08), transparent 60%),
        radial-gradient(900px 600px at 10% 110%, rgba(79,70,229,.10), transparent 60%),
        var(--bg);
      color:var(--text); font:14px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
    }
    h1{ margin:0 0 10px 0; font-size:1.2rem }
    p{ margin:4px 0; color:#cfe2ff }
    a{ color:#9ddcff; text-decoration:none }
    a:hover{text-decoration:underline}

    .header-accent{
      height:4px; margin:-20px -20px 16px -20px;
      background:linear-gradient(90deg,var(--brand),var(--brand2),var(--brand));
      background-size:200% 100%; animation: sheen 8s linear infinite;
    }
    @keyframes sheen{0%{background-position:0% 50%}100%{background-position:200% 50%}}

    .wrap{ max-width:1000px; margin:0 auto }
    .meta{
      display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; color:var(--muted)
    }
    .pill{
      display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .6rem; border-radius:999px; font-weight:700; font-size:.75rem;
      border:1px solid rgba(255,255,255,.16); background:rgba(255,255,255,.05); color:#cfe6ff
    }

    .actions{ margin:12px 0 18px 0 }
    .btn{
      display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:10px;
      background:transparent; color:#e6f0ff; border:1px solid rgba(255,255,255,.16); cursor:pointer;
      transition:transform .1s ease, background .2s ease, border-color .2s ease; text-decoration:none; margin-right:8px
    }
    .btn:hover{ transform:translateY(-1px); background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.22) }

    /* Console */
    .console{
      border:1px solid rgba(255,255,255,.12); border-radius:14px; overflow:hidden;
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(0,0,0,.2)), #0b1324;
      box-shadow: 0 10px 30px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.03);
    }
    .console__top{
      display:flex; align-items:center; justify-content:space-between; gap:8px;
      padding:10px 12px; border-bottom:1px solid rgba(255,255,255,.08); background:rgba(12,18,34,.6); backdrop-filter: blur(8px);
    }
    .dots{ display:flex; gap:8px }
    .dot{ width:10px; height:10px; border-radius:999px; }
    .dot.r{ background:#ef4444 } .dot.y{ background:#f59e0b } .dot.g{ background:#22c55e }
    .title{ font-weight:800; font-size:.9rem; color:#cfe6ff; letter-spacing:.3px }
    .console__tools{ display:flex; align-items:center; gap:8px }
    .tool{
      display:inline-flex; align-items:center; gap:.35rem; padding:.4rem .6rem; border-radius:8px; font-size:.8rem;
      background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); color:#cfe6ff; cursor:pointer
    }
    .tool:hover{ background:rgba(255,255,255,.08) }

    .console__body{
      position:relative; padding:12px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      color:#c9d1d9; background: #0c1322; max-height: 480px; overflow:auto;
    }
    /* scanlines */
    .console__body::before{
      content:""; position:absolute; inset:0; pointer-events:none;
      background: repeating-linear-gradient(0deg, rgba(255,255,255,.02), rgba(255,255,255,.02) 1px, transparent 1px, transparent 3px);
      opacity:.35;
    }
    /* subtle glow edge */
    .console__body::after{
      content:""; position:absolute; inset:0; pointer-events:none; box-shadow: inset 0 0 80px rgba(0,180,255,.07);
    }
    pre{
      margin:0; background:transparent; padding:0; white-space:pre-wrap; word-wrap:break-word;
    }
    /* ANSI-like coloring (very light heuristic) */
    .ansi-green{ color:#86efac }
    .ansi-yellow{ color:#fde68a }
    .ansi-red{ color:#fca5a5 }
    .ansi-blue{ color:#93c5fd }

    .footer-links{ margin-top:14px }

    /* Focus + reduced motion */
    :focus-visible{ outline:none; box-shadow: var(--ring) }
    @media (prefers-reduced-motion: reduce){
      .header-accent{ animation:none }
    }
  </style>
</head>
<body>
  <div class="header-accent"></div>
  <div class="wrap">
    <h1><?= $title ?> - <?= htmlspecialchars($username) ?></h1>
    <div class="meta">
      <span class="pill">Lab ID: <?= (int)$lab_id ?></span>
      <span class="pill">Path: <?= htmlspecialchars($user_lab_path) ?></span>
      <span class="pill">Action: <?= htmlspecialchars($action) ?></span>
    </div>

    <div class="console" id="console">
      <div class="console__top">
        <div style="display:flex; align-items:center; gap:10px">
          <div class="dots"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span></div>
          <div class="title">Server Log</div>
        </div>
        <div class="console__tools">
          <button class="tool" id="copyBtn" type="button">Copy</button>
          <button class="tool" id="autoscrollBtn" type="button" aria-pressed="true">Auto-scroll: On</button>
        </div>
      </div>
      <div class="console__body" id="consoleBody">
        <pre id="logText"><?=
          // Safe-escape text but keep some basic markers we tint with CSS
          htmlspecialchars($output ?? 'No output received.')
        ?></pre>
      </div>
    </div>

    <div class="actions">
      <form method="post" style="display:inline">
        <button class="btn" type="submit" name="action" value="start">🚀 Start Again</button>
      </form>
      <form method="post" style="display:inline">
        <button class="btn" type="submit" name="action" value="stop">🛑 Stop Container</button>
      </form>
      <a class="btn" href="lab_detail.php?id=<?= (int)$lab_id ?>">⬅️ Back to Lab</a>
    </div>

    <div class="footer-links">
      <small style="color:var(--muted)">Tip: Use the Copy button to share logs when reporting issues.</small>
    </div>
  </div>

  <script>
    // Copy to clipboard
    const copyBtn = document.getElementById('copyBtn');
    const logText = document.getElementById('logText');
    copyBtn?.addEventListener('click', async () => {
      try{
        await navigator.clipboard.writeText(logText.textContent || '');
        copyBtn.textContent = 'Copied!';
        setTimeout(()=> copyBtn.textContent = 'Copy', 1200);
      }catch(e){
        copyBtn.textContent = 'Copy failed';
        setTimeout(()=> copyBtn.textContent = 'Copy', 1200);
      }
    });

    // Auto-scroll toggle
    const consoleBody = document.getElementById('consoleBody');
    const autoscrollBtn = document.getElementById('autoscrollBtn');
    let autoScroll = true;
    autoscrollBtn?.addEventListener('click', ()=>{
      autoScroll = !autoScroll;
      autoscrollBtn.setAttribute('aria-pressed', autoScroll ? 'true':'false');
      autoscrollBtn.textContent = 'Auto-scroll: ' + (autoScroll ? 'On' : 'Off');
      if(autoScroll){ consoleBody.scrollTop = consoleBody.scrollHeight; }
    });

    // Initial scroll to bottom
    window.requestAnimationFrame(()=>{ consoleBody.scrollTop = consoleBody.scrollHeight; });

    // Simple ANSI-like tint (optional, non-destructive)
    // Color common keywords inside the pre without altering backend text.
    (function colorize(){
      const raw = logText.textContent;
      const colored = raw
        .replace(/(error|failed|fatal)/gi,  m => `<span class="ansi-red">${m}</span>`)
        .replace(/(warn|warning)/gi,       m => `<span class="ansi-yellow">${m}</span>`)
        .replace(/(success|started|running|done)/gi, m => `<span class="ansi-green">${m}</span>`)
        .replace(/(docker|compose|container)/gi, m => `<span class="ansi-blue">${m}</span>`);
      logText.innerHTML = colored; // safe because source was escaped; we only add known spans
    })();
  </script>
</body>
</html>
