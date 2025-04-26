<?php
// Include authentication functions
require_once 'includes/auth.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Initialize variables
$error_message = '';
$username = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate inputs
    if (empty($username)) {
        $error_message = 'Username is required';
    } else if (empty($password)) {
        $error_message = 'Password is required';
    } else {
        // Attempt login
        $result = user_login($username, $password);
        
        if ($result['success']) {
            // Set remember-me cookie if requested
            if ($remember) {
                setcookie('vlab_username', $username, time() + (86400 * 30), "/"); // 30 days
            }
            
            // Redirect to dashboard
            redirect('dashboard.php');
        } else {
            $error_message = $result['message'];
            
            // Create a demo user if none exists (for testing purposes)
            if ($username === 'demo' && $password === 'Demo1234') {
                $demoUser = [
                    'username' => 'demo',
                    'password' => 'Demo1234',
                    'fullname' => 'Demo User',
                    'email' => 'demo@example.com'
                ];
                $reg_result = user_register($demoUser);
                
                if ($reg_result['success']) {
                    redirect('dashboard.php');
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><span class="logo-v">V</span>LAB</h1>
                <p class="subtitle">Virtual Laboratory Environment</p>
            </div>
            <div class="auth-form">
                <h2>Login</h2>
                <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <?php if (!empty($error_message)): ?>
                        <div class="form-error" style="margin-bottom: 15px; text-align: center;"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        <span class="form-error" id="usernameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="form-error" id="passwordError"></span>
                    </div>
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn-primary">LOGIN <i class="fas fa-arrow-right"></i></button>
                    <div class="auth-redirect">
                        Don't have an account? <a href="signup.php">Sign up</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> VLab - Cybersecurity Training Platform</p>
        </div>
    </div>

    <div class="cyber-grid">
        <div class="grid-line horizontal"></div>
        <div class="grid-line horizontal"></div>
        <div class="grid-line horizontal"></div>
        <div class="grid-line vertical"></div>
        <div class="grid-line vertical"></div>
        <div class="grid-line vertical"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Client-side validation
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            // Clear previous errors
            document.getElementById('usernameError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            
            // Validate username
            if (!username) {
                document.getElementById('usernameError').textContent = 'Username is required';
                isValid = false;
            }
            
            // Validate password
            if (!password) {
                document.getElementById('passwordError').textContent = 'Password is required';
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>