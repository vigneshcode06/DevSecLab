<?php
// Include authentication and labs functions
require_once 'includes/auth.php';
require_once 'includes/labs.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('index.php');
}

// Get lab ID from URL
$lab_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$labs = [
    1 => [
        'title' => 'Essentials Lab',
        'description' => 'A Ubuntu 24.04 lab equipped with all the essentials to code, hack, build, and develop, with a Superman front access.',
        'icon' => 'fab fa-ubuntu',
        'tags' => ['Beta', 'Get Learning', 'Premium'],
        'status' => 'Instance Down',
        'cpu_load' => '5',
        'memory_usage' => '2',
        'network_usage' => '0'
    ],
    2 => [
        'title' => 'Java Dev Server',
        'description' => 'Complete Java development environment with Maven, Gradle, and popular IDEs.',
        'icon' => 'fab fa-java',
        'tags' => ['Beta', 'Get Learning'],
        'status' => 'Instance Down',
        'cpu_load' => '12',
        'memory_usage' => '4',
        'network_usage' => '1'
    ],
    3 => [
        'title' => 'kali linux ',
        'description' => 'Kali Linux Desktop that can run on your browser accelerated by Vlabs Labs with all default tools.',
        'icon' => 'fas fa-database',
        'tags' => ['Beta', 'Get Learning'],
        'status' => 'Instance Down',
        'cpu_load' => '8',
        'memory_usage' => '3',
        'network_usage' => '2'
    ],
    10 => [
        'title' => 'MySQL WebBench test',
        'description' => 'Database management and testing environment with MySQL and web interface.',
        'icon' => 'fas fa-database',
        'tags' => ['Beta', 'Get Learning'],
        'status' => 'Instance Down',
        'cpu_load' => '8',
        'memory_usage' => '3',
        'network_usage' => '2'
    ]
];

$current_lab = $labs[$lab_id] ?? $labs[2];

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VLab - <?= htmlspecialchars($current_lab['title']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --bg-0:#0b1220; --bg-1:#0f172a; --panel:#101827dd; --text:#e6f0ff; --muted:#a7b6d6;
      --brand:#00b4ff; --brand-2:#4f46e5; --ring:0 0 0 3px rgba(0,180,255,.25);
      --radius:14px; --shadow:0 10px 30px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.03);
      --ok:#22c55e; --warn:#f59e0b; --err:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font:14px/1.5 Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color:var(--text);
      background: radial-gradient(1200px 800px at 80% -10%, rgba(0,180,255,.08), transparent 60%),
                  radial-gradient(900px 600px at 10% 110%, rgba(79,70,229,.10), transparent 60%),
                  var(--bg-0);
      overflow-x:hidden;
    }
    .bg-grid::before{
      content:""; position:fixed; inset:0; pointer-events:none; opacity:.25;
      background-image: linear-gradient(transparent 23px, rgba(255,255,255,.03) 24px),
                        linear-gradient(90deg, transparent 23px, rgba(255,255,255,.03) 24px);
      background-size:24px 24px;
    }
    .header-accent{
      height:4px;background: linear-gradient(90deg, var(--brand), var(--brand-2), var(--brand));
      background-size:200% 100%; animation: sheen 8s linear infinite;
    }
    @keyframes sheen{0%{background-position:0% 50%}100%{background-position:200% 50%}}

    .app-container{ display:grid; grid-template-columns: 250px 1fr; min-height:100vh }
    .sidebar{
      position:sticky; top:0; height:100vh; background: rgba(10,14,25,.6);
      backdrop-filter: blur(12px); border-right:1px solid rgba(255,255,255,.06);
      padding:14px; transition: width .2s ease;
    }
    .sidebar.expanded{ width:280px }
    .sidebar-header{ display:flex; align-items:center; justify-content:space-between; padding:8px 6px 14px 6px }
    .sidebar-header h1{ margin:0; font-weight:800; letter-spacing:.5px }
    .logo-v{
      display:inline-grid; place-items:center; width:28px; height:28px; border-radius:8px; margin-right:6px;
      background: radial-gradient(circle at 30% 30%, #fff, #7dd3fc 20%, #0891b2 55%, #0ea5e9 70%, #1e40af 100%);
      color:#0b1220; font-weight:900; box-shadow:0 4px 18px rgba(0,180,255,.35)
    }
    .sidebar-toggle{ background:transparent; border:1px solid rgba(255,255,255,.12); color:var(--text); border-radius:10px; padding:8px; cursor:pointer }
    .sidebar-user{ display:flex; gap:10px; align-items:center; padding:10px; background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow) }
    .user-avatar{ font-size:30px; color:#7dd3fc }
    .user-info h3{ margin:0; font-size:1rem }
    .user-info p{ margin:2px 0 0 0; color:var(--muted); font-size:.8rem }
    .sidebar-nav ul{ list-style:none; padding:10px 0 0 0; margin:0; display:grid; gap:6px }
    .sidebar-nav a{
      display:flex; align-items:center; gap:10px; padding:10px 12px; text-decoration:none; color:var(--text);
      border:1px solid transparent; border-radius:12px; transition: background .15s, transform .12s, border-color .2s
    }
    .sidebar-nav li.active a, .sidebar-nav a:hover{
      background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.10)
    }
    .sidebar-footer{ margin-top:auto; padding:14px 6px }
    .sidebar-footer a{ display:flex; align-items:center; gap:10px; color:var(--text); text-decoration:none; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,.10) }
    .sidebar-footer a:hover{ background: rgba(255,255,255,.06) }

    .main-content{ padding:20px 22px }
    .content-header{
      position:sticky; top:0; z-index:5; margin: -20px -22px 16px -22px; padding:14px 22px; background: rgba(10,14,25,.5);
      backdrop-filter: blur(10px); border-bottom:1px solid rgba(255,255,255,.06); display:flex; align-items:center; justify-content:space-between
    }
    .breadcrumb{ display:flex; align-items:center; gap:8px; color:var(--muted) }
    .breadcrumb a{ color:#9ddcff; text-decoration:none }
    .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.6rem 1rem; border-radius:12px; text-decoration:none; cursor:pointer }
    .btn-primary{
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(0,0,0,.2)), linear-gradient(90deg, var(--brand), var(--brand-2));
      background-size:100% 100%, 200% 100%; color:#fff; border:1px solid rgba(255,255,255,.12); box-shadow:var(--shadow);
      transition: transform .12s ease, background-position .6s ease;
    }
    .btn-primary:hover{ transform: translateY(-1px); background-position: 100% 0 }

    .content-body{ display:grid; gap:14px }
    .lab-info-header{
      display:flex; gap:12px; padding:16px; background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow)
    }
    .lab-icon-large{
      width:56px; height:56px; display:grid; place-items:center; border-radius:16px; font-size:26px; color:#7dd3fc;
      background: rgba(0,180,255,.12); border:1px solid rgba(0,180,255,.3)
    }
    .lab-info-details h1{ margin:0 0 6px 0; font-size:1.2rem }
    .lab-description{ margin:0 0 8px 0; color:var(--muted) }
    .lab-tags{ display:flex; gap:8px; flex-wrap:wrap }
    .lab-tag{
      padding:.3rem .6rem; border-radius:999px; border:1px solid rgba(255,255,255,.16);
      background: rgba(255,255,255,.05); font-weight:700; font-size:.75rem; color:#cfe6ff
    }

    .lab-tabs{
      display:flex; align-items:center; gap:8px; position:relative; padding:6px; background:var(--panel);
      border:1px solid rgba(255,255,255,.06); border-radius:12px; box-shadow:var(--shadow)
    }
    .tab-btn{
      appearance:none; border:none; background:transparent; color:#cfe6ff; padding:.6rem .9rem; border-radius:10px; font-weight:700; cursor:pointer;
      position:relative; z-index:1
    }
    .tab-btn.active{ color:#001; }
    .tabs-indicator{
      position:absolute; top:6px; bottom:6px; width:120px; border-radius:10px; background:linear-gradient(90deg, var(--brand), var(--brand-2));
      transition: transform .25s ease; z-index:0; pointer-events:none;
    }

    .tab-content{ display:none }
    .tab-content.active{ display:block; }

    .lab-dashboard-grid{ display:grid; grid-template-columns: 1.2fr 1fr; gap:14px; margin-top:8px }
    @media (max-width: 980px){ .lab-dashboard-grid{ grid-template-columns: 1fr } }

    .lab-info-card, .lab-history-card{
      background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow); padding:14px
    }
    .lab-info-card h3, .lab-history-card h3{ margin:0 0 8px 0; font-size:1rem }
    .status-indicator{ color:#fecaca }

    .lab-metrics-grid{
      display:grid; grid-template-columns: repeat(3, 1fr); gap:12px
    }
    @media (max-width: 640px){ .lab-metrics-grid{ grid-template-columns:1fr } }

    .metric-card{
      background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:12px; padding:12px; box-shadow:var(--shadow)
    }
    .metric-card h4{ margin:0 0 8px 0; font-size:.95rem }
    .metric-number{ font-weight:800; color:#dbe7ff }
    .metric-bar{ height:8px; background:rgba(255,255,255,.06); border-radius:999px; overflow:hidden; margin-top:6px; position:relative }
    .metric-progress{ height:100%; background: linear-gradient(90deg, var(--brand), var(--brand-2)); width:0; transition: width 1s ease }

    .history-metrics{ display:flex; gap:14px; flex-wrap:wrap }
    .history-item{ background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); border-radius:10px; padding:8px 10px }
    .history-label{ font-size:.7rem; color:#c0d9ff }
    .history-value{ font-weight:800 }

    .fade-in{ animation: fadeIn .35s ease both }
    @keyframes fadeIn{ from{ opacity:0; transform: translateY(6px)} to{ opacity:1; transform:none } }
    .reveal{ opacity:0; transform: translateY(10px) }
    .reveal.visible{ opacity:1; transform:none; transition: opacity .4s ease, transform .4s ease }

    :focus-visible{ outline: none; box-shadow: var(--ring) }
    .content-header, .lab-info-header, .metric-card, .lab-info-card, .lab-history-card, .lab-tabs { will-change: transform, opacity }
    @media (prefers-reduced-motion: reduce){
      .metric-progress, .reveal, .fade-in, .header-accent{ transition:none; animation:none }
      .reveal{ opacity:1; transform:none }
    }
  </style>
</head>
<body class="lab-info-page bg-grid">
  <div class="header-accent"></div>

  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h1><span class="logo-v">V</span>LAB</h1>
        <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></button>
      </div>
      <div class="sidebar-user">
        <div class="user-avatar"><i class="fas fa-user-circle"></i></div>
        <div class="user-info">
          <h3><?= htmlspecialchars($username) ?></h3>
          <p>Cybersecurity Student</p>
        </div>
      </div>
      <nav class="sidebar-nav" aria-label="Primary">
        <ul>
          <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></li>
          <li class="active"><a href="labs.php"><i class="fas fa-flask"></i><span>Labs</span></a></li>
          <li><a href="#"><i class="fas fa-book"></i><span>Learning</span></a></li>
          <li><a href="#"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
          <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
          <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content fade-in">
      <header class="content-header">
        <div class="breadcrumb">
          <a href="dashboard.php">Home</a><span>/</span><a href="labs.php">Labs</a><span>/</span><span><?= htmlspecialchars($current_lab['title']) ?></span>
        </div>
        <div class="header-actions">
          <a href="deploy_lab.php?id=<?= $lab_id; ?>" class="btn btn-primary lab-deploy-btn">Deploy</a>
        </div>
      </header>

      <div class="content-body">
        <div class="lab-info-header reveal">
          <div class="lab-icon-large"><i class="<?= htmlspecialchars($current_lab['icon']) ?>"></i></div>
          <div class="lab-info-details">
            <h1><?= htmlspecialchars($current_lab['title']) ?></h1>
            <p class="lab-description"><?= htmlspecialchars($current_lab['description']) ?></p>
            <div class="lab-tags">
              <?php foreach ($current_lab['tags'] as $tag): ?>
                <span class="lab-tag"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="lab-tabs reveal" id="tabs">
          <div class="tabs-indicator" id="tabsIndicator" style="transform: translateX(0)"></div>
          <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
          <button class="tab-btn" data-tab="overview">Overview</button>
          <button class="tab-btn" data-tab="preferences">Preferences</button>
        </div>

        <div class="tab-content active" id="dashboard-tab">
          <div class="lab-dashboard-grid">
            <div class="lab-info-card reveal">
              <h3>Lab Information</h3>
              <div class="lab-info-content">
                <p><strong>Status:</strong> <span class="status-indicator"><?= htmlspecialchars($current_lab['status']) ?></span></p>
                <p>Lab is not active and restarting.</p>
              </div>
            </div>

            <div class="lab-metrics-grid">
              <div class="metric-card reveal">
                <h4>Container Load</h4>
                <div class="metric-value">
                  <span class="metric-number"><?= (int)$current_lab['cpu_load'] ?>%</span>
                  <div class="metric-bar">
                    <div class="metric-progress" style="width: <?= (int)$current_lab['cpu_load'] ?>%"></div>
                  </div>
                </div>
                <p class="metric-label">CPU Load</p>
              </div>

              <div class="metric-card reveal">
                <h4>Memory Usage</h4>
                <div class="metric-value">
                  <span class="metric-number"><?= (int)$current_lab['memory_usage'] ?>GB</span>
                  <div class="metric-bar">
                    <div class="metric-progress" style="width: <?= (int)$current_lab['memory_usage'] * 20 ?>%"></div>
                  </div>
                </div>
                <p class="metric-label">Memory Usage</p>
              </div>

              <div class="metric-card reveal">
                <h4>Network Usage</h4>
                <div class="metric-value">
                  <span class="metric-number"><?= (int)$current_lab['network_usage'] ?>MB</span>
                  <div class="metric-bar">
                    <div class="metric-progress" style="width: <?= (int)$current_lab['network_usage'] * 10 ?>%"></div>
                  </div>
                </div>
                <p class="metric-label">Network Usage</p>
              </div>
            </div>

            <div class="lab-history-card reveal">
              <h3>Load History <span class="history-duration">24h Hour</span></h3>
              <div class="history-metrics">
                <div class="history-item">
                  <span class="history-label">CPU PEAK</span>
                  <span class="history-value">15%</span>
                </div>
                <div class="history-item">
                  <span class="history-label">PID MAX</span>
                  <span class="history-value">512</span>
                </div>
                <div class="history-item">
                  <span class="history-label">MEMORY HIGH</span>
                  <span class="history-value">4.2GB</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="tab-content" id="overview-tab">
          <div class="overview-content reveal">
            <h3>Lab Overview</h3>
            <p>This lab provides a complete development environment with all necessary tools and configurations.</p>
          </div>
        </div>

        <div class="tab-content" id="preferences-tab">
          <div class="preferences-content reveal">
            <h3>Lab Preferences</h3>
            <p>Configure your lab settings and preferences here.</p>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Sidebar toggle
      const sidebarToggle = document.getElementById('sidebar-toggle');
      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
          const sidebar = document.querySelector('.sidebar');
          sidebar.classList.toggle('expanded');
        });
      }

      // Tabs with animated indicator
      const tabs = document.querySelectorAll('.tab-btn');
      const indicator = document.getElementById('tabsIndicator');
      function positionIndicator(active){
        const tabsWrap = document.getElementById('tabs');
        const btns = Array.from(tabsWrap.querySelectorAll('.tab-btn'));
        const idx = btns.indexOf(active);
        const btnWidth = active.offsetWidth;
        let x = 6; // left padding
        for(let i=0;i<idx;i++){ x += btns[i].offsetWidth + 8; } // 8px gap
        indicator.style.width = btnWidth + 'px';
        indicator.style.transform = `translateX(${x}px)`;
      }
      tabs.forEach(btn=>{
        btn.addEventListener('click', ()=>{
          tabs.forEach(b=>b.classList.remove('active'));
          btn.classList.add('active');
          document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
          document.getElementById(btn.dataset.tab + '-tab').classList.add('active');
          positionIndicator(btn);
        });
      });
      // Initial indicator
      const initial = document.querySelector('.tab-btn.active'); if(initial) positionIndicator(initial);

      // Reveal on scroll
      const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
          if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
        })
      }, {threshold:.12});
      document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

      // Animate metric bars
      requestAnimationFrame(()=>{
        document.querySelectorAll('.metric-progress').forEach(bar=>{
          const w = bar.style.width;
          bar.style.width = '0';
          requestAnimationFrame(()=>{ bar.style.width = w; });
        });
      });
    });
  </script>
</body>
</html>
