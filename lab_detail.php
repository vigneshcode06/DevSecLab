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
        'title' => 'MySQL WebBench',
        'description' => 'Database management and testing environment with MySQL and web interface.',
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


$current_lab = $labs[$lab_id] ?? $labs[1];

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - <?php echo htmlspecialchars($current_lab['title']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="lab-info-page">
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
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Cybersecurity Student</p>
                </div>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="active">
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
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="breadcrumb">
                    <a href="dashboard.php">Home</a>
                    <span>/</span>
                    <a href="labs.php">Labs</a>
                    <span>/</span>
                    <span><?php echo htmlspecialchars($current_lab['title']); ?></span>
                </div>
                <div class="header-actions">
<a href="deploy_lab.php?id=<?php echo $lab_id; ?>" class="btn btn-primary lab-deploy-btn">
    Deploy
</a>

                </div>
            </header>

            <div class="content-body">
                <div class="lab-info-header">
                    <div class="lab-icon-large">
                        <i class="<?php echo htmlspecialchars($current_lab['icon']); ?>"></i>
                    </div>
                    <div class="lab-info-details">
                        <h1><?php echo htmlspecialchars($current_lab['title']); ?></h1>
                        <p class="lab-description"><?php echo htmlspecialchars($current_lab['description']); ?></p>
                        <div class="lab-tags">
                            <?php foreach ($current_lab['tags'] as $tag): ?>
                                <span class="lab-tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="lab-tabs">
                    <button class="tab-btn active" data-tab="dashboard">Dashboard</button>
                    <button class="tab-btn" data-tab="overview">Overview</button>
                    <button class="tab-btn" data-tab="preferences">Preferences</button>
                </div>

                <div class="tab-content active" id="dashboard-tab">
                    <div class="lab-dashboard-grid">
                        <div class="lab-info-card">
                            <h3>Lab Information</h3>
                            <div class="lab-info-content">
                                <p><strong>Status:</strong> <span class="status-indicator"><?php echo htmlspecialchars($current_lab['status']); ?></span></p>
                                <p>Lab is not active and restarting.</p>
                            </div>
                        </div>

                        <div class="lab-metrics-grid">
                            <div class="metric-card">
                                <h4>Container Load</h4>
                                <div class="metric-value">
                                    <span class="metric-number"><?php echo $current_lab['cpu_load']; ?>%</span>
                                    <div class="metric-bar">
                                        <div class="metric-progress" style="width: <?php echo $current_lab['cpu_load']; ?>%"></div>
                                    </div>
                                </div>
                                <p class="metric-label">CPU Load</p>
                            </div>
                            
                            <div class="metric-card">
                                <h4>Memory Usage</h4>
                                <div class="metric-value">
                                    <span class="metric-number"><?php echo $current_lab['memory_usage']; ?>GB</span>
                                    <div class="metric-bar">
                                        <div class="metric-progress" style="width: <?php echo ($current_lab['memory_usage'] * 20); ?>%"></div>
                                    </div>
                                </div>
                                <p class="metric-label">Memory Usage</p>
                            </div>
                            
                            <div class="metric-card">
                                <h4>Network Usage</h4>
                                <div class="metric-value">
                                    <span class="metric-number"><?php echo $current_lab['network_usage']; ?>MB</span>
                                    <div class="metric-bar">
                                        <div class="metric-progress" style="width: <?php echo ($current_lab['network_usage'] * 10); ?>%"></div>
                                    </div>
                                </div>
                                <p class="metric-label">Network Usage</p>
                            </div>
                        </div>

                        <div class="lab-history-card">
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
                    <div class="overview-content">
                        <h3>Lab Overview</h3>
                        <p>This lab provides a complete development environment with all necessary tools and configurations.</p>
                    </div>
                </div>

                <div class="tab-content" id="preferences-tab">
                    <div class="preferences-content">
                        <h3>Lab Preferences</h3>
                        <p>Configure your lab settings and preferences here.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                this.classList.add('active');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // Sidebar toggle
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