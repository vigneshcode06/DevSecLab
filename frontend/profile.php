<?php
// Include authentication and labs functions
require_once 'includes/auth.php';
require_once 'includes/labs.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('index.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

// Get complete user profile information
$user_profile = get_user_profile($user_id);

// Get user progress summary
$progress_summary = get_user_progress_summary($user_id);

// Get user achievements
$achievements = get_user_achievements($user_id);

// Initialize variables for form processing
$profile_updated = false;
$error_message = '';
$success_message = '';

// Process profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['update_profile'])) {
        // Profile information update
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
            
            // Update displayed name
            $fullname = $profile_data['fullname'];
            $_SESSION['fullname'] = $fullname;
            
            // Refresh user profile data
            $user_profile = get_user_profile($user_id);
        } else {
            $error_message = $result['message'];
        }
    } else if (isset($_POST['update_password'])) {
        // Password update
        $current_password = $_POST['currentPassword'];
        $new_password = $_POST['newPassword'];
        $confirm_password = $_POST['confirmNewPassword'];
        
        if ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match';
        } else {
            $result = update_user_password($user_id, $current_password, $new_password);
            
            if ($result['success']) {
                $success_message = $result['message'];
            } else {
                $error_message = $result['message'];
            }
        }
    } else if (isset($_POST['update_settings'])) {
        // Settings update
        $settings_data = [
            'email_notifications' => isset($_POST['emailNotifications']) ? true : false,
            'lab_reminders' => isset($_POST['labReminders']) ? true : false,
            'new_lab_alerts' => isset($_POST['newLabAlerts']) ? true : false,
            'security_alerts' => isset($_POST['securityAlerts']) ? true : false
        ];
        
        $result = update_user_settings($user_id, $settings_data);
        
        if ($result['success']) {
            $success_message = $result['message'];
            
            // Refresh user profile data
            $user_profile = get_user_profile($user_id);
        } else {
            $error_message = $result['message'];
        }
    }
}

// Set active tab from URL parameter, default to "personal"
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="profile-page">
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
                    <li class="active">
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
                <h2>Profile</h2>
                <div class="header-actions">
                    <div class="notification">
                        <button class="notification-btn">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                            <button class="edit-avatar-btn"><i class="fas fa-camera"></i></button>
                        </div>
                        <div class="profile-info">
                            <h3 id="profileUserName"><?php echo htmlspecialchars($fullname); ?></h3>
                            <p>Cybersecurity Student</p>
                            <div class="profile-stats">
                                <div class="stat">
                                    <span class="stat-value"><?php echo $progress_summary['completed_labs']; ?></span>
                                    <span class="stat-label">Labs Completed</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">1250</span>
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
                        <div class="alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert-error">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-tabs">
                        <a href="?tab=personal" class="tab-btn <?php echo $active_tab === 'personal' ? 'active' : ''; ?>" data-tab="personal">Personal Information</a>
                        <a href="?tab=progress" class="tab-btn <?php echo $active_tab === 'progress' ? 'active' : ''; ?>" data-tab="progress">Progress Tracking</a>
                        <a href="?tab=achievements" class="tab-btn <?php echo $active_tab === 'achievements' ? 'active' : ''; ?>" data-tab="achievements">Achievements</a>
                        <a href="?tab=settings" class="tab-btn <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" data-tab="settings">Account Settings</a>
                    </div>

                    <div class="profile-content">
                        <div class="tab-content <?php echo $active_tab === 'personal' ? 'active' : ''; ?>" id="personal">
                            <div class="profile-section">
                                <h3 class="section-title">Personal Information</h3>
                                <form id="personalInfoForm" class="profile-form" method="POST" action="profile.php?tab=personal">
                                    <div class="form-group">
                                        <label for="profileFullName">Full Name</label>
                                        <input type="text" id="profileFullName" name="fullname" value="<?php echo htmlspecialchars($user_profile['fullname']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="profileEmail">Email</label>
                                        <input type="email" id="profileEmail" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="profileUsername">Username</label>
                                        <input type="text" id="profileUsername" name="username" value="<?php echo htmlspecialchars($user_profile['username']); ?>" readonly>
                                        <small class="form-text">Username cannot be changed</small>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="profileCountry">Country</label>
                                            <select id="profileCountry" name="country">
                                                <option value="us" <?php echo $user_profile['country'] === 'us' ? 'selected' : ''; ?>>United States</option>
                                                <option value="uk" <?php echo $user_profile['country'] === 'uk' ? 'selected' : ''; ?>>United Kingdom</option>
                                                <option value="ca" <?php echo $user_profile['country'] === 'ca' ? 'selected' : ''; ?>>Canada</option>
                                                <option value="au" <?php echo $user_profile['country'] === 'au' ? 'selected' : ''; ?>>Australia</option>
                                            </select>
                                        </div>
                                        <div class="form-group half">
                                            <label for="profileTimezone">Timezone</label>
                                            <select id="profileTimezone" name="timezone">
                                                <option value="est" <?php echo $user_profile['timezone'] === 'est' ? 'selected' : ''; ?>>Eastern Time (EST)</option>
                                                <option value="cst" <?php echo $user_profile['timezone'] === 'cst' ? 'selected' : ''; ?>>Central Time (CST)</option>
                                                <option value="mst" <?php echo $user_profile['timezone'] === 'mst' ? 'selected' : ''; ?>>Mountain Time (MST)</option>
                                                <option value="pst" <?php echo $user_profile['timezone'] === 'pst' ? 'selected' : ''; ?>>Pacific Time (PST)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="profileBio">Bio</label>
                                        <textarea id="profileBio" name="bio" rows="4"><?php echo htmlspecialchars($user_profile['bio'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                                        <button type="reset" class="btn-secondary">Reset</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="tab-content <?php echo $active_tab === 'progress' ? 'active' : ''; ?>" id="progress">
                            <div class="profile-section">
                                <h3 class="section-title">Progress Tracking</h3>
                                <div class="progress-overview">
                                    <div class="progress-card">
                                        <h4>Overall Completion</h4>
                                        <div class="circular-progress">
                                            <div class="circular-progress-inner">
                                                <span><?php echo $progress_summary['completion_percentage']; ?>%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress-details">
                                        <?php foreach ($progress_summary['category_progress'] as $category => $data): ?>
                                            <div class="progress-item">
                                                <div class="progress-header">
                                                    <h4><?php echo htmlspecialchars($category); ?></h4>
                                                    <span><?php echo $data['completed']; ?>/<?php echo $data['total']; ?> Completed</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress" style="width: <?php echo $data['percentage']; ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($progress_summary['category_progress'])): ?>
                                            <div class="empty-state">
                                                <p>No progress data available yet. Start completing labs to see your progress.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content <?php echo $active_tab === 'achievements' ? 'active' : ''; ?>" id="achievements">
                            <div class="profile-section">
                                <h3 class="section-title">Achievements</h3>
                                <div class="achievements-grid">
                                    <?php if (empty($achievements)): ?>
                                        <div class="empty-state">
                                            <p>No achievements available yet. Complete labs to earn achievements!</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($achievements as $achievement): ?>
                                            <div class="achievement-card <?php echo $achievement['unlocked'] ? 'unlocked' : ''; ?>">
                                                <div class="achievement-icon">
                                                    <i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i>
                                                </div>
                                                <div class="achievement-info">
                                                    <h4><?php echo htmlspecialchars($achievement['title']); ?></h4>
                                                    <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>" id="settings">
                            <div class="profile-section">
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
                                        <input type="checkbox" id="emailNotifications" name="emailNotifications" <?php echo $user_profile['email_notifications'] ? 'checked' : ''; ?>>
                                        <label for="emailNotifications">Email notifications</label>
                                    </div>
                                    <div class="form-checkbox">
                                        <input type="checkbox" id="labReminders" name="labReminders" <?php echo $user_profile['lab_reminders'] ? 'checked' : ''; ?>>
                                        <label for="labReminders">Lab completion reminders</label>
                                    </div>
                                    <div class="form-checkbox">
                                        <input type="checkbox" id="newLabAlerts" name="newLabAlerts" <?php echo $user_profile['new_lab_alerts'] ? 'checked' : ''; ?>>
                                        <label for="newLabAlerts">New lab alerts</label>
                                    </div>
                                    <div class="form-checkbox">
                                        <input type="checkbox" id="securityAlerts" name="securityAlerts" <?php echo $user_profile['security_alerts'] ? 'checked' : ''; ?>>
                                        <label for="securityAlerts">Security alerts</label>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" name="update_settings" class="btn-primary">Save Preferences</button>
                                    </div>
                                </form>
                                
                                <h4 class="settings-title">Account Actions</h4>
                                <div class="account-actions">
                                    <button class="btn-danger">Delete Account</button>
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
        // Toggle sidebar functionality
        const sidebarToggle = document.getElementById('sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('expanded');
            });
        }
        
        // Password validation
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmNewPassword').value;
                
                // Check password format
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                if (!passwordRegex.test(newPassword)) {
                    alert('Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number');
                    event.preventDefault();
                    return;
                }
                
                // Check passwords match
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match');
                    event.preventDefault();
                }
            });
        }
        
        // Account deletion confirmation
        const deleteAccountBtn = document.querySelector('.btn-danger');
        if (deleteAccountBtn) {
            deleteAccountBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                    // This would submit a form or make an AJAX request to delete the account
                    alert('Account deletion would be processed here.');
                }
            });
        }
    });
    </script>
</body>
</html>