<?php
require_once 'includes/auth.php';
require_once 'includes/labs.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$fullname  = $_SESSION['fullname'];

$category_filter   = $_GET['category']   ?? 'all';
$difficulty_filter = $_GET['difficulty'] ?? 'all';
$status_filter     = $_GET['status']     ?? 'all';

$filters = [
    'category'   => $category_filter,
    'difficulty' => $difficulty_filter,
    'status'     => $status_filter
];

$user_labs = get_user_labs($user_id, $filters);

$labs_by_category = [];
foreach ($user_labs as $lab) {
    $labs_by_category[$lab['category']][] = $lab;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VLab - Labs</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

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
    .content-header h2{ margin:0; font-size:1.2rem; font-weight:800; letter-spacing:.3px }
    .search-bar{ display:flex; align-items:center; gap:6px; background:rgba(15,23,42,.6); border:1px solid rgba(255,255,255,.08); border-radius:12px; padding:6px }
    .search-bar input{ background:transparent; border:none; outline:none; color:var(--text); padding:6px }
    .search-bar button{ background:linear-gradient(90deg, var(--brand), var(--brand-2)); color:#fff; border:none; border-radius:10px; padding:8px 10px; cursor:pointer }
    .notification-btn{ position:relative; background:transparent; border:1px solid rgba(255,255,255,.12); color:var(--text); border-radius:10px; padding:8px 10px; cursor:pointer }
    .badge{
      position:absolute; top:-6px; right:-6px; background: #0ea5e9; color:#001; font-weight:800; font-size:.65rem;
      border-radius:999px; padding:2px 6px; box-shadow:0 0 0 3px rgba(14,165,233,.25)
    }

    .labs-filters{ margin:12px 0 16px 0 }
    .filter-form{ display:flex; flex-wrap:wrap; gap:10px }
    .filter-group{
      display:grid; gap:6px; background:var(--panel); border:1px solid rgba(255,255,255,.06);
      border-radius:12px; padding:10px 12px; min-width:220px; box-shadow:var(--shadow)
    }
    .filter-group label{ font-size:.8rem; color:var(--muted) }
    select{
      width:100%; padding:.6rem .8rem; color:var(--text); background:rgba(15,23,42,.6);
      border:1px solid rgba(255,255,255,.12); border-radius:10px; outline:none;
    }
    select:focus{ border-color:var(--brand); box-shadow: var(--ring) }

    .labs-categories{ display:grid; gap:16px }
    .category-section{ background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow) }
    .category-title{ margin:0; padding:14px; border-bottom:1px solid rgba(255,255,255,.06); font-size:1rem }
    .labs-grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:12px; padding:12px 14px }

    .lab-card{
      display:flex; flex-direction:column; gap:10px; padding:14px; border-radius:14px; background: rgba(255,255,255,.03);
      border:1px solid rgba(255,255,255,.06); transition: transform .12s ease, background .2s ease, border-color .2s ease;
    }
    .lab-card:hover{ transform: translateY(-2px); background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.12) }

    .lab-header{ display:flex; align-items:center; justify-content:space-between }
    .lab-badge{
      display:inline-flex; align-items:center; gap:.4rem; padding:.3rem .6rem; border-radius:999px; font-weight:700; font-size:.75rem;
      border:1px solid rgba(255,255,255,.16);
    }
    .lab-badge.beginner{ background: rgba(34,197,94,.12); color:#a7f3d0; border-color: rgba(34,197,94,.35) }
    .lab-badge.intermediate{ background: rgba(59,130,246,.12); color:#bfdbfe; border-color: rgba(59,130,246,.35) }
    .lab-badge.advanced{ background: rgba(245,158,11,.12); color:#fde68a; border-color: rgba(245,158,11,.35) }
    .lab-badge.expert{ background: rgba(239,68,68,.12); color:#fecaca; border-color: rgba(239,68,68,.35) }

    .lab-duration{ color:var(--muted); font-size:.8rem; display:flex; align-items:center; gap:6px }
    .lab-content h4{ margin:6px 0 4px 0; font-size:.95rem }
    .lab-content p{ margin:0; color:var(--muted) }
    .lab-icon{ font-size:18px; color:#7dd3fc; width:32px; height:32px; display:grid; place-items:center; background: rgba(0,180,255,.12); border-radius:10px; border:1px solid rgba(0,180,255,.3) }

    .lab-progress{ display:grid; gap:6px; margin-top:6px }
    .progress-text{ color:#cfe6ff; font-size:.8rem }
    .progress-bar{
      height:8px; background:rgba(255,255,255,.06); border-radius:999px; overflow:hidden; position:relative;
    }
    .progress-bar.completed::after{
      content:""; position:absolute; inset:0; background:linear-gradient(90deg, rgba(255,255,255,.05), transparent 40%, rgba(255,255,255,.05));
      background-size:200% 100%; animation: shimmer 2.2s linear infinite;
    }
    @keyframes shimmer{ 0%{ background-position:-200% 0 } 100%{ background-position:200% 0 } }
    .progress{
      height:100%; background: linear-gradient(90deg, var(--brand), var(--brand-2)); width:0;
      transition: width 1s ease;
    }

    .lab-footer{ display:flex; align-items:center; justify-content:flex-end; gap:8px }
    .btn-resume, .btn-review, .btn-start, .btn-locked{
      padding:.55rem .8rem; border-radius:10px; border:1px solid rgba(255,255,255,.12); color:#e6f0ff; background: transparent; cursor:pointer;
      transition: transform .1s ease, background .2s ease, border-color .2s ease; text-decoration:none; display:inline-flex; align-items:center; gap:.45rem
    }
    .btn-resume:hover, .btn-review:hover, .btn-start:hover{ transform: translateY(-1px); background: rgba(255,255,255,.06); border-color: rgba(255,255,255,.18) }
    .btn-locked{ opacity:.5; cursor:not-allowed }

    .fade-in{ animation: fadeIn .35s ease both }
    @keyframes fadeIn{ from{ opacity:0; transform: translateY(6px)} to{ opacity:1; transform:none } }
    .reveal{ opacity:0; transform: translateY(10px) }
    .reveal.visible{ opacity:1; transform:none; transition: opacity .4s ease, transform .4s ease }

    .content-header, .category-section, .filter-group, .lab-card { will-change: transform, opacity }

    :focus-visible{ outline: none; box-shadow: var(--ring) }
    @media (max-width: 1000px){ .app-container{ grid-template-columns: 72px 1fr } .sidebar{ padding:10px } .sidebar .user-info{ display:none } }
    @media (prefers-reduced-motion: reduce){
      .progress, .reveal, .fade-in, .header-accent{ transition:none; animation:none }
      .reveal{ opacity:1; transform:none }
    }
  </style>
</head>
<body class="labs-page bg-grid">
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
          <h3 id="userDisplayName"><?= htmlspecialchars($username) ?></h3>
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
        <a href="logout.php" id="logoutBtn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content fade-in">
      <header class="content-header">
        <h2>Labs</h2>
        <div class="header-actions" style="display:flex;align-items:center;gap:10px">
          <div class="search-bar">
            <input type="text" placeholder="Search labs..." id="labSearch" aria-label="Search labs">
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
        <div class="labs-filters reveal">
          <form id="filterForm" action="labs.php" method="GET" class="filter-form">
            <div class="filter-group">
              <label for="categoryFilter">Category:</label>
              <select id="categoryFilter" name="category" onchange="this.form.submit()">
                <option value="all" <?= $category_filter==='all'?'selected':'' ?>>All Categories</option>
                <option value="Development" <?= $category_filter==='Development'?'selected':'' ?>>Development</option>
                <option value="CTF" <?= $category_filter==='CTF'?'selected':'' ?>>CTF</option>
                <option value="Windows" <?= $category_filter==='Windows'?'selected':'' ?>>Windows</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="difficultyFilter">Difficulty:</label>
              <select id="difficultyFilter" name="difficulty" onchange="this.form.submit()">
                <option value="all" <?= $difficulty_filter==='all'?'selected':'' ?>>All Levels</option>
                <option value="Beginner" <?= $difficulty_filter==='Beginner'?'selected':'' ?>>Beginner</option>
                <option value="Intermediate" <?= $difficulty_filter==='Intermediate'?'selected':'' ?>>Intermediate</option>
                <option value="Advanced" <?= $difficulty_filter==='Advanced'?'selected':'' ?>>Advanced</option>
                <option value="Expert" <?= $difficulty_filter==='Expert'?'selected':'' ?>>Expert</option>
              </select>
            </div>
            <div class="filter-group">
              <label for="statusFilter">Status:</label>
              <select id="statusFilter" name="status" onchange="this.form.submit()">
                <option value="all" <?= $status_filter==='all'?'selected':'' ?>>All Status</option>
                <option value="notStarted" <?= $status_filter==='notStarted'?'selected':'' ?>>Not Started</option>
                <option value="inProgress" <?= $status_filter==='inProgress'?'selected':'' ?>>In Progress</option>
                <option value="completed" <?= $status_filter==='completed'?'selected':'' ?>>Completed</option>
              </select>
            </div>
          </form>
        </div>

        <div class="labs-categories">
          <?php if (empty($labs_by_category)): ?>
            <div class="empty-state reveal" style="background:var(--panel);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow)">
              <p>No labs found matching your filters. Try changing your filter options.</p>
            </div>
          <?php else: ?>
            <?php foreach ($labs_by_category as $category => $labs): ?>
              <div class="category-section reveal">
                <h3 class="category-title"><?= htmlspecialchars($category) ?> Labs</h3>
                <div class="labs-grid">
                  <?php foreach ($labs as $lab): ?>
                    <div class="lab-card">
                      <div class="lab-header">
                        <div class="lab-badge <?= strtolower($lab['difficulty']) ?>"><?= htmlspecialchars($lab['difficulty']) ?></div>
                        <div class="lab-duration"><i class="fas fa-clock"></i> <?= ($lab['duration']/60) ?> hours</div>
                      </div>
                      <div class="lab-content">
                        <div class="lab-icon"><i class="<?= htmlspecialchars($lab['icon']) ?>"></i></div>
                        <h4><?= htmlspecialchars($lab['title']) ?></h4>
                        <p><?= htmlspecialchars($lab['description']) ?></p>
                        <div class="lab-progress">
                          <div class="progress-text">
                            <?php if ($lab['status'] == 'In Progress'): ?>
                              In progress (<?= (int)$lab['progress_percentage'] ?>%)
                            <?php else: ?>
                              <?= htmlspecialchars($lab['status']) ?>
                            <?php endif; ?>
                          </div>
                          <div class="progress-bar <?= ($lab['status']=='Completed')?'completed':'' ?>">
                            <div class="progress" style="width: <?= (int)$lab['progress_percentage'] ?>%"></div>
                          </div>
                        </div>
                      </div>
                      <div class="lab-footer">
                        <?php if ($lab['status'] == 'In Progress'): ?>
                          <a href="lab_detail.php?id=<?= $lab['lab_id'] ?>" class="btn-resume">Resume Lab</a>
                        <?php elseif ($lab['status'] == 'Completed'): ?>
                          <a href="lab_detail.php?id=<?= $lab['lab_id'] ?>" class="btn-review">Review Lab</a>
                        <?php elseif ($lab['difficulty'] == 'Expert' && strpos($lab['title'], 'Advanced') !== false): ?>
                          <button class="btn-locked" disabled>Locked</button>
                        <?php else: ?>
                          <a href="lab_detail.php?id=<?= $lab['lab_id'] ?>" class="btn-start">Start Lab</a>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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

      // Search filter
      const searchInput = document.getElementById('labSearch');
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const q = this.value.toLowerCase();
          const cards = document.querySelectorAll('.lab-card');
          cards.forEach(card=>{
            const t = card.querySelector('h4')?.textContent.toLowerCase() || '';
            const d = card.querySelector('p')?.textContent.toLowerCase() || '';
            card.style.display = (t.includes(q) || d.includes(q)) ? '' : 'none';
          });
          // Toggle category visibility
          document.querySelectorAll('.category-section').forEach(section=>{
            const visible = Array.from(section.querySelectorAll('.lab-card')).some(c=>c.style.display!=='none');
            section.style.display = visible ? '' : 'none';
          });
        });
      }

      // Reveal on scroll
      const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{
          if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); }
        })
      }, {threshold:.12});
      document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

      // Animate progress bars
      requestAnimationFrame(()=>{
        document.querySelectorAll('.progress').forEach(bar=>{
          const w = bar.style.width;
          bar.style.width = '0';
          requestAnimationFrame(()=>{ bar.style.width = w; });
        });
      });
    });
  </script>
</body>
</html>
