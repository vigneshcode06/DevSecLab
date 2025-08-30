<?php
require_once 'includes/auth.php';
require_once 'includes/labs.php';

if (!is_logged_in()) { redirect('index.php'); }

$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];
$fullname  = $_SESSION['fullname'];

$user_profile      = get_user_profile($user_id);
$progress_summary  = get_user_progress_summary($user_id);
$achievements      = get_user_achievements($user_id);

$profile_updated = false;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $profile_data = [
            'fullname' => $_POST['fullname'],
            'email' => $_POST['email'],
            'bio' => $_POST['bio'],
            'country' => $_POST['country'],
            'timezone' => $_POST['timezone']
        ];
        $result = update_user_profile($user_id, $profile_data);
        if ($result['success']) {
            $success_message = $result['message'];
            $profile_updated = true;
            $fullname = $profile_data['fullname'];
            $_SESSION['fullname'] = $fullname;
            $user_profile = get_user_profile($user_id);
        } else { $error_message = $result['message']; }
    } else if (isset($_POST['update_password'])) {
        $current_password = $_POST['currentPassword'];
        $new_password = $_POST['newPassword'];
        $confirm_password = $_POST['confirmNewPassword'];
        if ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match';
        } else {
            $result = update_user_password($user_id, $current_password, $new_password);
            if ($result['success']) { $success_message = $result['message']; }
            else { $error_message = $result['message']; }
        }
    } else if (isset($_POST['update_settings'])) {
        $settings_data = [
            'email_notifications' => isset($_POST['emailNotifications']),
            'lab_reminders' => isset($_POST['labReminders']),
            'new_lab_alerts' => isset($_POST['newLabAlerts']),
            'security_alerts' => isset($_POST['securityAlerts'])
        ];
        $result = update_user_settings($user_id, $settings_data);
        if ($result['success']) {
            $success_message = $result['message'];
            $user_profile = get_user_profile($user_id);
        } else { $error_message = $result['message']; }
    }
}

$active_tab = $_GET['tab'] ?? 'personal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VLab - Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    :root{
      --bg-0:#0b1220; --panel:#101827dd; --text:#e6f0ff; --muted:#a7b6d6;
      --brand:#00b4ff; --brand-2:#4f46e5; --ring:0 0 0 3px rgba(0,180,255,.25);
      --radius:14px; --shadow:0 10px 30px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.03);
      --ok:#22c55e; --warn:#f59e0b; --err:#ef4444;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; color:var(--text);
      font:14px/1.5 Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
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
    .header-accent{ height:4px;background: linear-gradient(90deg, var(--brand), var(--brand-2), var(--brand)); background-size:200% 100%; animation: sheen 8s linear infinite; }
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
    .notification-btn{ position:relative; background:transparent; border:1px solid rgba(255,255,255,.12); color:var(--text); border-radius:10px; padding:8px 10px; cursor:pointer }
    .badge{ position:absolute; top:-6px; right:-6px; background: #0ea5e9; color:#001; font-weight:800; font-size:.65rem; border-radius:999px; padding:2px 6px; box-shadow:0 0 0 3px rgba(14,165,233,.25) }

    .profile-container{ display:grid; gap:14px }
    .profile-header{
      display:flex; gap:12px; padding:16px; background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow)
    }
    .profile-avatar{ position:relative; width:72px; height:72px; display:grid; place-items:center; font-size:42px; color:#7dd3fc; background: rgba(0,180,255,.12); border:1px solid rgba(0,180,255,.3); border-radius:18px }
    .edit-avatar-btn{ position:absolute; right:-6px; bottom:-6px; width:28px; height:28px; border-radius:999px; border:1px solid rgba(255,255,255,.18); background:linear-gradient(90deg, var(--brand), var(--brand-2)); color:#fff; display:grid; place-items:center; cursor:pointer }
    .profile-info h3{ margin:0 0 4px 0 }
    .profile-stats{ display:flex; gap:14px; flex-wrap:wrap; margin-top:8px }
    .stat{ background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); border-radius:12px; padding:8px 12px }
    .stat-value{ font-weight:800 }
    .stat-label{ color:var(--muted); font-size:.8rem }

    .alert-success, .alert-error{
      padding:10px 12px; border-radius:12px; border:1px solid; box-shadow:var(--shadow)
    }
    .alert-success{ background: rgba(34,197,94,.12); color:#a7f3d0; border-color: rgba(34,197,94,.35) }
    .alert-error{ background: rgba(239,68,68,.12); color:#fecaca; border-color: rgba(239,68,68,.35) }

    .profile-tabs{
      position:relative; display:flex; gap:8px; padding:6px; background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:12px; box-shadow:var(--shadow)
    }
    .tab-btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.45rem; padding:.6rem .9rem; border-radius:10px; text-decoration:none;
      color:#cfe6ff; border:1px solid transparent; font-weight:700
    }
    .tab-btn.active{ color:#001; background:linear-gradient(90deg, var(--brand), var(--brand-2)) }
    .profile-content{ display:grid; gap:14px }
    .profile-section{ background:var(--panel); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); box-shadow:var(--shadow); padding:14px }
    .section-title{ margin:0 0 8px 0; font-size:1rem }

    .profile-form{ display:grid; gap:10px }
    .form-row{ display:grid; grid-template-columns:1fr 1fr; gap:10px }
    .form-group{ display:grid; gap:6px }
    .form-group.half{ width:100% }
    label{ font-size:.85rem; color:#cfe6ff }
    input[type="text"], input[type="email"], input[type="password"], textarea, select{
      width:100%; padding:.7rem .9rem; color:var(--text); background:rgba(15,23,42,.6); border:1px solid rgba(255,255,255,.12); border-radius:12px; outline:none
    }
    input:focus, textarea:focus, select:focus{ border-color: var(--brand); box-shadow: var(--ring) }
    .form-text{ color:var(--muted); font-size:.75rem }

    .form-actions{ display:flex; gap:8px }
    .btn-primary{
      display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1rem; border-radius:12px; color:#fff; border:1px solid rgba(255,255,255,.12);
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(0,0,0,.2)), linear-gradient(90deg, var(--brand), var(--brand-2));
      background-size:100% 100%, 200% 100%; box-shadow:var(--shadow); cursor:pointer; text-decoration:none;
      transition: transform .12s ease, background-position .6s ease
    }
    .btn-primary:hover{ transform: translateY(-1px); background-position: 100% 0 }
    .btn-secondary{
      display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1rem; border-radius:12px; color:#e6f0ff; border:1px solid rgba(255,255,255,.16); background:transparent; cursor:pointer
    }
    .btn-danger{ display:inline-flex; align-items:center; gap:.5rem; padding:.6rem .9rem; border-radius:12px; color:#fecaca; border:1px solid rgba(239,68,68,.35); background: rgba(239,68,68,.12); cursor:pointer }

    .progress-overview{ display:grid; grid-template-columns: 240px 1fr; gap:12px }
    @media (max-width: 900px){ .app-container{ grid-template-columns: 72px 1fr } .sidebar .user-info{ display:none } .progress-overview{ grid-template-columns:1fr } .form-row{ grid-template-columns:1fr } }

    .circular-progress{
      width:240px; height:240px; border-radius:50%; display:grid; place-items:center; position:relative;
      background: conic-gradient(var(--brand) calc(var(--pct)*1%), rgba(255,255,255,.08) 0);
      --pct: <?= (int)$progress_summary['completion_percentage'] ?>;
      box-shadow: 0 0 0 8px rgba(0,180,255,.08), inset 0 0 40px rgba(0,180,255,.08);
    }
    .circular-progress-inner{
      width: 190px; height:190px; border-radius:50%; display:grid; place-items:center; background:rgba(10,14,25,.8);
      border:1px solid rgba(255,255,255,.1); box-shadow: inset 0 0 30px rgba(0,0,0,.3);
    }
    .circular-progress-inner span{ font-weight:900; font-size:1.6rem }

    .progress-details{ display:grid; gap:10px }
    .progress-item{ background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:10px }
    .progress-header{ display:flex; align-items:center; justify-content:space-between; gap:8px }
    .progress-bar{ height:8px; background:rgba(255,255,255,.06); border-radius:999px; overflow:hidden; }
    .progress{ height:100%; background: linear-gradient(90deg, var(--brand), var(--brand-2)); width:0; transition: width 1s ease }

    .achievements-grid{ display:grid; grid-template-columns: repeat(auto-fit, minmax(260px,1fr)); gap:12px }
    .achievement-card{ display:flex; gap:10px; padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,.1); background: rgba(255,255,255,.04) }
    .achievement-card.unlocked{ background: rgba(34,197,94,.12); border-color: rgba(34,197,94,.35) }
    .achievement-icon{ font-size:18px; color:#7dd3fc; width:32px; height:32px; display:grid; place-items:center; background: rgba(0,180,255,.12); border-radius:10px; border:1px solid rgba(0,180,255,.3) }
    .achievement-info h4{ margin:0; font-size:.95rem }
    .achievement-info p{ margin:2px 0 0 0; color:var(--muted) }

    .settings-title{ margin:14px 0 8px 0; font-size:.95rem; color:#dbe7ff }
    .form-checkbox{ display:flex; align-items:center; gap:8px }
    .warning-text{ color:#fde68a; margin-top:6px }

    .fade-in{ animation: fadeIn .35s ease both }
    @keyframes fadeIn{ from{ opacity:0; transform: translateY(6px)} to{ opacity:1; transform:none } }
    .reveal{ opacity:0; transform: translateY(10px) }
    .reveal.visible{ opacity:1; transform:none; transition: opacity .4s ease, transform .4s ease }
    :focus-visible{ outline:none; box-shadow: var(--ring) }
    @media (prefers-reduced-motion: reduce){
      .progress, .circular-progress, .reveal, .fade-in, .header-accent{ transition:none; animation:none }
      .reveal{ opacity:1; transform:none }
    }
  </style>
</head>
<body class="profile-page bg-grid">
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
          <li><a href="labs.php"><i class="fas fa-flask"></i><span>Labs</span></a></li>
          <li><a href="#"><i class="fas fa-book"></i><span>Learning</span></a></li>
          <li><a href="#"><i class="fas fa-trophy"></i><span>Achievements</span></a></li>
          <li class="active"><a href="profile.php"><i class="fas fa-user"></i><span>Profile</span></a></li>
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
        <h2>Profile</h2>
        <div class="header-actions">
          <div class="notification">
            <button class="notification-btn" aria-label="Notifications">
              <i class="fas fa-bell"></i>
              <span class="badge">3</span>
            </button>
          </div>
        </div>
      </header>

      <div class="content-body">
        <div class="profile-container">
          <div class="profile-header reveal">
            <div class="profile-avatar">
              <i class="fas fa-user-circle"></i>
              <button class="edit-avatar-btn" title="Change avatar"><i class="fas fa-camera"></i></button>
            </div>
            <div class="profile-info">
              <h3 id="profileUserName"><?= htmlspecialchars($fullname) ?></h3>
              <p>Cybersecurity Student</p>
              <div class="profile-stats">
                <div class="stat">
                  <span class="stat-value"><?= (int)$progress_summary['completed_labs'] ?></span>
                  <span class="stat-label">Labs Completed</span>
                </div>
                <div class="stat">
                  <span class="stat-value">0</span>
                  <span class="stat-label">CTF Points</span>
                </div>
                <div class="stat">
                  <span class="stat-value">Intermediate</span>
                  <span class="stat-label">Level</span>
                </div>
              </div>
            </div>
          </div>

          <?php if (!empty($success_message)): ?>
            <div class="alert-success reveal"><?= htmlspecialchars($success_message) ?></div>
          <?php endif; ?>

          <?php if (!empty($error_message)): ?>
            <div class="alert-error reveal"><?= htmlspecialchars($error_message) ?></div>
          <?php endif; ?>

          <div class="profile-tabs reveal">
            <a href="?tab=personal" class="tab-btn <?= $active_tab==='personal'?'active':'' ?>" data-tab="personal">Personal Information</a>
            <a href="?tab=progress" class="tab-btn <?= $active_tab==='progress'?'active':'' ?>" data-tab="progress">Progress Tracking</a>
            <a href="?tab=achievements" class="tab-btn <?= $active_tab==='achievements'?'active':'' ?>" data-tab="achievements">Achievements</a>
            <a href="?tab=settings" class="tab-btn <?= $active_tab==='settings'?'active':'' ?>" data-tab="settings">Account Settings</a>
          </div>

          <div class="profile-content">
            <div class="tab-content <?= $active_tab==='personal'?'active':'' ?>" id="personal">
              <div class="profile-section reveal">
                <h3 class="section-title">Personal Information</h3>
                <form id="personalInfoForm" class="profile-form" method="POST" action="profile.php?tab=personal">
                  <div class="form-group">
                    <label for="profileFullName">Full Name</label>
                    <input type="text" id="profileFullName" name="fullname" value="<?= htmlspecialchars($user_profile['fullname']) ?>">
                  </div>
                  <div class="form-group">
                    <label for="profileEmail">Email</label>
                    <input type="email" id="profileEmail" name="email" value="<?= htmlspecialchars($user_profile['email']) ?>">
                  </div>
                  <div class="form-group">
                    <label for="profileUsername">Username</label>
                    <input type="text" id="profileUsername" name="username" value="<?= htmlspecialchars($user_profile['username']) ?>" readonly>
                    <small class="form-text">Username cannot be changed</small>
                  </div>
                  <div class="form-row">
                    <div class="form-group half">
                      <label for="profileCountry">Country</label>
                      <select id="profileCountry" name="country">
                        <option value="us" <?= $user_profile['country']==='us'?'selected':'' ?>>United States</option>
                        <option value="uk" <?= $user_profile['country']==='uk'?'selected':'' ?>>United Kingdom</option>
                        <option value="ca" <?= $user_profile['country']==='ca'?'selected':'' ?>>Canada</option>
                        <option value="au" <?= $user_profile['country']==='au'?'selected':'' ?>>Australia</option>
                      </select>
                    </div>
                    <div class="form-group half">
                      <label for="profileTimezone">Timezone</label>
                      <select id="profileTimezone" name="timezone">
                        <option value="est" <?= $user_profile['timezone']==='est'?'selected':'' ?>>Eastern Time (EST)</option>
                        <option value="cst" <?= $user_profile['timezone']==='cst'?'selected':'' ?>>Central Time (CST)</option>
                        <option value="mst" <?= $user_profile['timezone']==='mst'?'selected':'' ?>>Mountain Time (MST)</option>
                        <option value="pst" <?= $user_profile['timezone']==='pst'?'selected':'' ?>>Pacific Time (PST)</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="profileBio">Bio</label>
                    <textarea id="profileBio" name="bio" rows="4"><?= htmlspecialchars($user_profile['bio'] ?? '') ?></textarea>
                  </div>
                  <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                    <button type="reset" class="btn-secondary">Reset</button>
                  </div>
                </form>
              </div>
            </div>

            <div class="tab-content <?= $active_tab==='progress'?'active':'' ?>" id="progress">
              <div class="profile-section reveal">
                <h3 class="section-title">Progress Tracking</h3>
                <div class="progress-overview">
                  <div class="progress-card">
                    <div class="circular-progress">
                      <div class="circular-progress-inner"><span><?= (int)$progress_summary['completion_percentage'] ?>%</span></div>
                    </div>
                  </div>
                  <div class="progress-details">
                    <?php foreach ($progress_summary['category_progress'] as $category => $data): ?>
                      <div class="progress-item">
                        <div class="progress-header">
                          <h4><?= htmlspecialchars($category) ?></h4>
                          <span><?= (int)$data['completed'] ?>/<?= (int)$data['total'] ?> Completed</span>
                        </div>
                        <div class="progress-bar">
                          <div class="progress" style="width: <?= (int)$data['percentage'] ?>%"></div>
                        </div>
                      </div>
                    <?php endforeach; ?>

                    <?php if (empty($progress_summary['category_progress'])): ?>
                      <div class="empty-state" style="color:#cfe6ff">No progress data available yet. Start completing labs to see your progress.</div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="tab-content <?= $active_tab==='achievements'?'active':'' ?>" id="achievements">
              <div class="profile-section reveal">
                <h3 class="section-title">Achievements</h3>
                <div class="achievements-grid">
                  <?php if (empty($achievements)): ?>
                    <div class="empty-state" style="color:#cfe6ff">No achievements available yet. Complete labs to earn achievements!</div>
                  <?php else: foreach ($achievements as $achievement): ?>
                    <div class="achievement-card <?= $achievement['unlocked'] ? 'unlocked' : '' ?>">
                      <div class="achievement-icon"><i class="<?= htmlspecialchars($achievement['icon']) ?>"></i></div>
                      <div class="achievement-info">
                        <h4><?= htmlspecialchars($achievement['title']) ?></h4>
                        <p><?= htmlspecialchars($achievement['description']) ?></p>
                      </div>
                    </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            </div>

            <div class="tab-content <?= $active_tab==='settings'?'active':'' ?>" id="settings">
              <div class="profile-section reveal">
                <h3 class="section-title">Account Settings</h3>

                <h4 class="settings-title">Change Password</h4>
                <form id="passwordForm" class="profile-form" method="POST" action="profile.php?tab=settings">
                  <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                  </div>
                  <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                    <small class="form-text">Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number</small>
                  </div>
                  <div class="form-group">
                    <label for="confirmNewPassword">Confirm New Password</label>
                    <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                  </div>
                  <div class="form-actions">
                    <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                  </div>
                </form>

                <h4 class="settings-title">Notification Preferences</h4>
                <form id="notificationForm" class="profile-form" method="POST" action="profile.php?tab=settings">
                  <div class="form-checkbox">
                    <input type="checkbox" id="emailNotifications" name="emailNotifications" <?= !empty($user_profile['email_notifications'])?'checked':'' ?>>
                    <label for="emailNotifications">Email notifications</label>
                  </div>
                  <div class="form-checkbox">
                    <input type="checkbox" id="labReminders" name="labReminders" <?= !empty($user_profile['lab_reminders'])?'checked':'' ?>>
                    <label for="labReminders">Lab completion reminders</label>
                  </div>
                  <div class="form-checkbox">
                    <input type="checkbox" id="newLabAlerts" name="newLabAlerts" <?= !empty($user_profile['new_lab_alerts'])?'checked':'' ?>>
                    <label for="newLabAlerts">New lab alerts</label>
                  </div>
                  <div class="form-checkbox">
                    <input type="checkbox" id="securityAlerts" name="securityAlerts" <?= !empty($user_profile['security_alerts'])?'checked':'' ?>>
                    <label for="securityAlerts">Security alerts</label>
                  </div>
                  <div class="form-actions">
                    <button type="submit" name="update_settings" class="btn-primary">Save Preferences</button>
                  </div>
                </form>

                <h4 class="settings-title">Account Actions</h4>
                <div class="account-actions">
                  <button class="btn-danger" type="button">Delete Account</button>
                  <p class="warning-text">This action cannot be undone. All your data will be permanently deleted.</p>
                </div>
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
      sidebarToggle?.addEventListener('click', function() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('expanded');
      });

      // Reveal on scroll
      const io = new IntersectionObserver((entries)=>{
        entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add('visible'); io.unobserve(e.target); } })
      }, {threshold:.12});
      document.querySelectorAll('.reveal').forEach(el=>io.observe(el));

      // Progress bars animate
      requestAnimationFrame(()=>{
        document.querySelectorAll('.progress').forEach(bar=>{
          const w = bar.style.width; bar.style.width = '0'; requestAnimationFrame(()=>{ bar.style.width = w; });
        });
      });

      // Password validation
      const passwordForm = document.getElementById('passwordForm');
      passwordForm?.addEventListener('submit', function(event) {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmNewPassword').value;
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!passwordRegex.test(newPassword)) {
          alert('Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number');
          event.preventDefault(); return;
        }
        if (newPassword !== confirmPassword) {
          alert('New passwords do not match'); event.preventDefault();
        }
      });

      // Account deletion confirmation
      const deleteAccountBtn = document.querySelector('.btn-danger');
      deleteAccountBtn?.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
          alert('Account deletion would be processed here.');
        }
      });
    });
  </script>
</body>
</html>
