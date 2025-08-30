<?php
require_once 'includes/auth.php';
require_once 'includes/labs.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$fullname  = $_SESSION['fullname'];

$progress_summary   = get_user_progress_summary($user_id);
$recent_labs        = array_slice(get_user_labs($user_id), 0, 3);
$achievements       = get_user_achievements($user_id);
$recent_achievements = array_slice(array_values(array_filter($achievements, fn($a)=>$a['unlocked']==1)), 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VLab - Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --bg-0:#0b1220;
      --bg-1:#0f172a;
      --panel:#101827dd;
      --text:#e6f0ff;
      --muted:#a7b6d6;
      --brand:#00b4ff;
      --brand-2:#4f46e5;
      --ring:0 0 0 3px rgba(0,180,255,.25);
      --radius:14px;
      --shadow:0 10px 30px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.03);
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

    .app-container{
      display:grid; grid-template-columns: 250px 1fr; min-height:100vh;
    }
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
    .content-header h2{ margin:0; font-size:1.2rem; font-weight:800; letter-spacing:.3px }
    .search-bar{ display:flex; align-items:center; gap:6px; background:rgba(15,23,42,.6); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:6px }
    .search-bar input{ background:transparent; border:none; outline:none; color:var(--text); padding:6px }
    .search-bar button{ background:linear-gradient(90deg, var(--brand), var(--brand-2)); color:#fff; border:none; border-radius:10px; padding:8px 10px; cursor:pointer }
    .notification-btn{ position:relative; background:transparent; border:1px solid rgba(255,255,255,.12); color:var(--text); border-radius:10px; padding:8px 10px; cursor:pointer }
    .badge{
      position:absolute; top:-6px; right:-6px; background: #0ea5e9; color:#001; font-weight:800; font-size:.65rem;
      border-radius:999px; padding:2px 6px; box-shadow:0 0 0 3px rgba(14,165,233,.25)
    }

    .welcome-banner{
      display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px;
      background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow)
    }
    .welcome-banner h3{ margin:0; font-size:1.1rem }
    .btn-primary{
      display:inline-flex; align-items:center; gap:.5rem; padding:.7rem 1rem; border-radius:12px;
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(0,0,0,.2)), linear-gradient(90deg, var(--brand), var(--brand-2));
      background-size:100% 100%, 200% 100%; color:white; border:1px solid rgba(255,255,255,.08);
      box-shadow: var(--shadow); text-decoration:none; transition: transform .12s ease, box-shadow .2s ease, background-position .6s ease;
    }
    .btn-primary:hover{ transform: translateY(-1px); background-position: 100% 0; }
    .btn-primary:active{ transform: translateY(0); }

    .stats-grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap:14px; margin:16px 0 }
    .stat-card{
      display:grid; grid-template-rows: auto auto 8px; gap:8px; padding:14px; background:var(--panel);
      border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow)
    }
    .stat-icon{ font-size:20px; color:#7dd3fc }
    .stat-info h4{ margin:0 0 4px 0; font-size:.95rem }
    .stat-info p{ margin:0; color:#dbe7ff; font-weight:800 }
    .stat-progress{ height:8px; background:rgba(255,255,255,.06); border-radius:999px; overflow:hidden; }
    .progress-bar{
      height:100%; background: linear-gradient(90deg, var(--brand), var(--brand-2)); width:0; transition: width 1s ease;
    }

    .dashboard-panels{ display:grid; grid-template-columns:1fr 1fr; gap:14px }
    @media (max-width: 1000px){ .app-container{ grid-template-columns: 72px 1fr } .sidebar{ padding:10px } .sidebar .user-info{ display:none } .dashboard-panels{ grid-template-columns:1fr } }
    @media (max-width: 640px){ .content-header{ position:static; margin:0 0 12px 0 } }

    .panel{ background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow) }
    .panel-header{ display:flex; align-items:center; justify-content:space-between; padding:14px; border-bottom:1px solid rgba(255,255,255,.06) }
    .panel-header h3{ margin:0; font-size:1rem }
    .panel-link{ color:#9ddcff; text-decoration:none }
    .panel-body{ padding:12px 14px }

    .lab-list, .achievement-list{ display:grid; gap:10px }
    .lab-item, .achievement-item{
      display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px; border-radius:12px;
      border:1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.03)
    }
    .lab-icon, .achievement-icon{ font-size:18px; color:#7dd3fc; width:32px; height:32px; display:grid; place-items:center; background: rgba(0,180,255,.12); border-radius:10px; border:1px solid rgba(0,180,255,.3) }
    .lab-details h4, .achievement-details h4{ margin:0; font-size:.95rem }
    .lab-details p, .achievement-details p{ margin:2px 0 0 0; color:var(--muted) }

    .btn-resume, .btn-review, .btn-start{
      padding:.55rem .8rem; border-radius:10px; border:1px solid rgba(255,255,255,.12); color:#e6f0ff; background: transparent; cursor:pointer;
      transition: transform .1s ease, background .2s ease, border-color .2s ease;
    }
    .btn-resume:hover, .btn-review:hover, .btn-start:hover{ transform: translateY(-1px); background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.18) }

    /* Animations */
    .fade-in{ animation: fadeIn .35s ease both }
    @keyframes fadeIn{ from{ opacity:0; transform: translateY(6px)} to{ opacity:1; transform:none } }
    .reveal{ opacity:0; transform: translateY(10px) }
    .reveal.visible{ opacity:1; transform:none; transition: opacity .4s ease, transform .4s ease }

    /* Focus states and a11y */
    :focus-visible{ outline: none; box-shadow: var(--ring) }
    @media (prefers-reduced-motion: reduce){
      .progress-bar{ transition:none }
      .fade-in,.reveal{ animation:none; opacity:1; transform:none }
      .header-accent{ animation:none }
    }
  </style>
</head>
<body class="dashboard-page bg-grid">
  <div class="header-accent"></div>

  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h1><span class="logo-v">V</span>LAB</h1>
        <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar">
          <i class="fas fa-bars"></i>
        </button>
      </div>

      <div class="sidebar-user">
        <div class="user-avatar">
          <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-info">
          <h3 id="userDisplayName"><?= htmlspecialchars($username) ?></h3>
          <p>Cybersecurity Student</p>
        </div>
      </div>

      <nav class="sidebar-nav" aria-label="Primary">
        <ul>
          <li class="active">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
          </li>
          <li><a href="labs.php"><i class="fas fa-flask"></i><span>Labs</span></a></li>
          <li><a href="#"><i class="fas fa-book"></i><span>Learning</span></a></li>
          <li><a href="#"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
          <li><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
          <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        </ul>
      </nav>

      <div class="sidebar-footer">
        <a href="logout.php" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content fade-in">
      <header class="content-header">
        <h2>Dashboard</h2>
        <div class="header-actions" style="display:flex;align-items:center;gap:10px">
          <div class="search-bar">
            <input type="text" placeholder="Search..." aria-label="Search">
            <button aria-label="Run search"><i class="fas fa-search"></i></button>
          </div>
          <div class="notification">
            <button class="notification-btn" aria-label="Notifications">
              <i class="fas fa-bell"></i>
              <span class="badge">3</span>
            </button>
          </div>
        </div>
      </header>

      <div class="content-body">
        <div class="welcome-banner reveal">
          <div class="welcome-text">
            <h3>Welcome back, <span id="welcomeUserName"><?= htmlspecialchars($fullname) ?></span>!</h3>
            <p>Continue your cybersecurity training journey</p>
          </div>
          <div class="welcome-actions">
            <a href="labs.php" class="btn-primary">Start Lab <i class="fas fa-play"></i></a>
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card reveal">
            <div class="stat-icon"><i class="fas fa-flask"></i></div>
            <div class="stat-info">
              <h4>Labs Completed</h4>
              <p><?= $progress_summary['completed_labs'] ?> / <?= $progress_summary['total_labs'] ?></p>
            </div>
            <div class="stat-progress">
              <div class="progress-bar" style="width: <?= (int)$progress_summary['completion_percentage'] ?>%"></div>
            </div>
          </div>

          <div class="stat-card reveal">
            <div class="stat-icon"><i class="fas fa-flag"></i></div>
            <div class="stat-info">
              <h4>CTF Points</h4>
              <p>0</p>
            </div>
            <div class="stat-progress">
              <div class="progress-bar" style="width: 45%"></div>
            </div>
          </div>

          <div class="stat-card reveal">
            <div class="stat-icon"><i class="fas fa-award"></i></div>
            <div class="stat-info">
              <h4>Level</h4>
              <p>Intermediate</p>
            </div>
            <div class="stat-progress">
              <div class="progress-bar" style="width: 65%"></div>
            </div>
          </div>
        </div>

        <div class="dashboard-panels">
          <div class="panel panel-labs reveal">
            <div class="panel-header">
              <h3>Recent Labs</h3>
              <a href="labs.php" class="panel-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="panel-body">
              <div class="lab-list">
              <?php if (empty($recent_labs)): ?>
                <div class="empty-state">
                  <p>No labs activity yet. Start your first lab!</p>
                </div>
              <?php else: foreach ($recent_labs as $lab): ?>
                <div class="lab-item">
                  <div class="lab-icon"><i class="<?= htmlspecialchars($lab['icon']) ?>"></i></div>
                  <div class="lab-details">
                    <h4><?= htmlspecialchars($lab['title']) ?></h4>
                    <p><?= htmlspecialchars($lab['status']) ?></p>
                  </div>
                  <div class="lab-actions">
                    <?php if ($lab['status'] == 'In Progress'): ?>
                      <button class="btn-resume">Resume</button>
                    <?php elseif ($lab['status'] == 'Completed'): ?>
                      <button class="btn-review">Review</button>
                    <?php else: ?>
                      <button class="btn-start">Start</button>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; endif; ?>
              </div>
            </div>
          </div>

          <div class="panel panel-achievements reveal">
            <div class="panel-header">
              <h3>Recent Achievements</h3>
              <a href="#" class="panel-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="panel-body">
              <div class="achievement-list">
              <?php if (empty($recent_achievements)): ?>
                <div class="empty-state">
                  <p>No achievements unlocked yet. Complete labs to earn achievements!</p>
                </div>
              <?php else: foreach ($recent_achievements as $achievement): ?>
                <div class="achievement-item">
                  <div class="achievement-icon"><i class="<?= htmlspecialchars($achievement['icon']) ?>"></i></div>
                  <div class="achievement-details">
                    <h4><?= htmlspecialchars($achievement['title']) ?></h4>
                    <p><?= htmlspecialchars($achievement['description']) ?></p>
                  </div>
                </div>
              <?php endforeach; endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toggle sidebar
      const sidebarToggle = document.getElementById('sidebar-toggle');
      if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
          const sidebar = document.querySelector('.sidebar');
          sidebar.classList.toggle('expanded');
        });
      }

      // Reveal on scroll
      const revealEls = document.querySelectorAll('.reveal');
      const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
          if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
        })
      }, {threshold:.12});
      revealEls.forEach(el=>io.observe(el));

      // Animate progress bars on first paint
      // (width inline style already set; this forces transition)
      requestAnimationFrame(()=>{
        document.querySelectorAll('.progress-bar').forEach(bar=>{
          const w = bar.style.width;
          bar.style.width = '0';
          requestAnimationFrame(()=>{ bar.style.width = w; });
        });
      });
    });
  </script>
</body>
</html>
