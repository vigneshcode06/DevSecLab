<?php

require_once 'includes/auth.php';
require_once 'includes/labs.php';


if (!is_logged_in()) {
    redirect('index.php');
}


$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

$progress_summary = get_user_progress_summary($user_id);

$recent_labs = get_user_labs($user_id);
$recent_labs = array_slice($recent_labs, 0, 3);


$achievements = get_user_achievements($user_id);
$recent_achievements = array_filter($achievements, function($achievement) {
    return $achievement['unlocked'] == 1;
});
$recent_achievements = array_slice($recent_achievements, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-page">
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1><span class="logo-v">V</span>LAB</h1>
                <button id="sidebar-toggle" class="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="sidebar-user">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <h3 id="userDisplayName"><?php echo htmlspecialchars($username); ?></h3>
                    <p>Cybersecurity Student</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="labs.php">
                            <i class="fas fa-flask"></i>
                            <span>Labs</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-book"></i>
                            <span>Learning</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-trophy"></i>
                            <span>Achievements</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h2>Dashboard</h2>
                <div class="header-actions">
                    <div class="search-bar">
                        <input type="text" placeholder="Search...">
                        <button><i class="fas fa-search"></i></button>
                    </div>
                    <div class="notification">
                        <button class="notification-btn">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <div class="welcome-banner">
                    <div class="welcome-text">
                        <h3>Welcome back, <span id="welcomeUserName"><?php echo htmlspecialchars($fullname); ?></span>!</h3>
                        <p>Continue your cybersecurity training journey</p>
                    </div>
                    <div class="welcome-actions">
                        <a href="labs.php" class="btn-primary">Start Lab <i class="fas fa-play"></i></a>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div class="stat-info">
                            <h4>Labs Completed</h4>
                            <p><?php echo $progress_summary['completed_labs']; ?> / <?php echo $progress_summary['total_labs']; ?></p>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: <?php echo $progress_summary['completion_percentage']; ?>%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="stat-info">
                            <h4>CTF Points</h4>
                            <p>0</p>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-award"></i>
                        </div>
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
                    <div class="panel panel-labs">
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
                                <?php else: ?>
                                    <?php foreach ($recent_labs as $lab): ?>
                                        <div class="lab-item">
                                            <div class="lab-icon"><i class="<?php echo htmlspecialchars($lab['icon']); ?>"></i></div>
                                            <div class="lab-details">
                                                <h4><?php echo htmlspecialchars($lab['title']); ?></h4>
                                                <p><?php echo htmlspecialchars($lab['status']); ?></p>
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
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-achievements">
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
                                <?php else: ?>
                                    <?php foreach ($recent_achievements as $achievement): ?>
                                        <div class="achievement-item">
                                            <div class="achievement-icon"><i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i></div>
                                            <div class="achievement-details">
                                                <h4><?php echo htmlspecialchars($achievement['title']); ?></h4>
                                                <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('expanded');
            });
        }
    });
    </script>
</body>
</html>