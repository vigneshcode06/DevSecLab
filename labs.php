<?php

require_once 'includes/auth.php';
require_once 'includes/labs.php';


if (!is_logged_in()) {
    redirect('index.php');
}


$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];


$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$difficulty_filter = isset($_GET['difficulty']) ? $_GET['difficulty'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';


$filters = [
    'category' => $category_filter,
    'difficulty' => $difficulty_filter,
    'status' => $status_filter
];


$user_labs = get_user_labs($user_id, $filters);


$labs_by_category = [];
foreach ($user_labs as $lab) {
    if (!isset($labs_by_category[$lab['category']])) {
        $labs_by_category[$lab['category']] = [];
    }
    $labs_by_category[$lab['category']][] = $lab;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Labs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="labs-page">
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
                <a href="logout.php" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h2>Labs</h2>
                <div class="header-actions">
                    <div class="search-bar">
                        <input type="text" placeholder="Search labs..." id="labSearch">
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
                <div class="labs-filters">
                    <form id="filterForm" action="labs.php" method="GET" class="filter-form">
                        <div class="filter-group">
                            <label for="categoryFilter">Category:</label>
                            <select id="categoryFilter" name="category" onchange="this.form.submit()">
                                <option value="all" <?php echo $category_filter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                                <option value="Development" <?php echo $category_filter === 'Development' ? 'selected' : ''; ?>>Development</option>
                                <option value="CTF" <?php echo $category_filter === 'CTF' ? 'selected' : ''; ?>>CTF</option>
                                <option value="Windows" <?php echo $category_filter === 'Windows' ? 'selected' : ''; ?>>Windows</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="difficultyFilter">Difficulty:</label>
                            <select id="difficultyFilter" name="difficulty" onchange="this.form.submit()">
                                <option value="all" <?php echo $difficulty_filter === 'all' ? 'selected' : ''; ?>>All Levels</option>
                                <option value="Beginner" <?php echo $difficulty_filter === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="Intermediate" <?php echo $difficulty_filter === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo $difficulty_filter === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                                <option value="Expert" <?php echo $difficulty_filter === 'Expert' ? 'selected' : ''; ?>>Expert</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="statusFilter">Status:</label>
                            <select id="statusFilter" name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="notStarted" <?php echo $status_filter === 'notStarted' ? 'selected' : ''; ?>>Not Started</option>
                                <option value="inProgress" <?php echo $status_filter === 'inProgress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="labs-categories">
                    <?php if (empty($labs_by_category)): ?>
                        <div class="empty-state">
                            <p>No labs found matching your filters. Try changing your filter options.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($labs_by_category as $category => $labs): ?>
                            <div class="category-section">
                                <h3 class="category-title"><?php echo htmlspecialchars($category); ?> Labs</h3>
                                <div class="labs-grid">
                                    <?php foreach ($labs as $lab): ?>
                                        <div class="lab-card">
                                            <div class="lab-header">
                                                <div class="lab-badge <?php echo strtolower($lab['difficulty']); ?>"><?php echo htmlspecialchars($lab['difficulty']); ?></div>
                                                <div class="lab-duration"><i class="fas fa-clock"></i> <?php echo ($lab['duration'] / 60); ?> hours</div>
                                            </div>
                                            <div class="lab-content">
                                                <div class="lab-icon"><i class="<?php echo htmlspecialchars($lab['icon']); ?>"></i></div>
                                                <h4><?php echo htmlspecialchars($lab['title']); ?></h4>
                                                <p><?php echo htmlspecialchars($lab['description']); ?></p>
                                                <div class="lab-progress">
                                                    <div class="progress-text">
                                                        <?php if ($lab['status'] == 'In Progress'): ?>
                                                            In progress (<?php echo $lab['progress_percentage']; ?>%)
                                                        <?php else: ?>
                                                            <?php echo $lab['status']; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="progress-bar <?php echo ($lab['status'] == 'Completed') ? 'completed' : ''; ?>">
                                                        <div class="progress" style="width: <?php echo $lab['progress_percentage']; ?>%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="lab-footer">
                                                <?php if ($lab['status'] == 'In Progress'): ?>
                                                    <a href="lab_detail.php?id=<?php echo $lab['lab_id']; ?>" class="btn-resume">Resume Lab</a>
                                                <?php elseif ($lab['status'] == 'Completed'): ?>
                                                    <a href="lab_detail.php?id=<?php echo $lab['lab_id']; ?>" class="btn-review">Review Lab</a>
                                                <?php elseif ($lab['difficulty'] == 'Expert' && strpos($lab['title'], 'Advanced') !== false): ?>
                                                    <button class="btn-locked">Locked</button>
                                                <?php else: ?>
                                                    <a href="lab_detail.php?id=<?php echo $lab['lab_id']; ?>" class="btn-start">Start Lab</a>
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
        // Toggle sidebar functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('expanded');
            });
        }
        
        // Search functionality
        const searchInput = document.getElementById('labSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const labCards = document.querySelectorAll('.lab-card');
                
                labCards.forEach(function(card) {
                    const title = card.querySelector('h4').textContent.toLowerCase();
                    const description = card.querySelector('p').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show/hide category sections based on whether they have visible cards
                const categorySections = document.querySelectorAll('.category-section');
                categorySections.forEach(function(section) {
                    const visibleCards = section.querySelectorAll('.lab-card[style=""]').length;
                    if (visibleCards === 0) {
                        section.style.display = 'none';
                    } else {
                        section.style.display = '';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>