<?php
// Include authentication functions
require_once 'includes/auth.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Initialize variables
$error_message = '';
$success_message = '';
$fullname = '';
$email = '';
$username = '';

// Process signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fullname = isset($_POST['fullname']) ? sanitize_input($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validate inputs
    if (empty($fullname)) {
        $error_message = 'Full name is required';
    } else if (empty($email)) {
        $error_message = 'Email is required';
    } else if (!is_valid_email($email)) {
        $error_message = 'Please enter a valid email address';
    } else if (empty($username)) {
        $error_message = 'Username is required';
    } else if (strlen($username) < 3) {
        $error_message = 'Username must be at least 3 characters';
    } else if (empty($password)) {
        $error_message = 'Password is required';
    } else if (!is_valid_password($password)) {
        $error_message = 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number';
    } else if ($password !== $confirm_password) {
        $error_message = 'Passwords do not match';
    } else if (!$terms) {
        $error_message = 'You must accept the Terms and Conditions';
    } else {
        // All inputs are valid, attempt registration
        $userData = [
            'fullname' => $fullname,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];
        
        $result = user_register($userData);
        
        if ($result['success']) {
            // Redirect to dashboard
            redirect('dashboard.php');
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Sign Up</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="signup-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><span class="logo-v">V</span>LAB</h1>
                <p class="subtitle">Virtual Laboratory Environment</p>
            </div>
            <div class="auth-form">
                <h2>Create Account</h2>
                <form id="signupForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <?php if (!empty($error_message)): ?>
                        <div class="form-error" style="margin-bottom: 15px; text-align: center;"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        <span class="form-error" id="fullnameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <span class="form-error" id="emailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user-shield"></i> Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        <span class="form-error" id="usernameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="form-error" id="passwordError"></span>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword"><i class="fas fa-lock"></i> Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="form-error" id="confirmPasswordError"></span>
                    </div>
                    <div class="form-options">
                        <div class="terms-agreement">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">SIGN UP <i class="fas fa-user-plus"></i></button>
                    <div class="auth-redirect">
                        Already have an account? <a href="index.php">Login</a>
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
        const signupForm = document.getElementById('signupForm');
        
        // Function to validate email format
        function isValidEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
        
        // Function to validate password requirements
        function isValidPassword(password) {
            // At least 8 characters, one uppercase, one lowercase, one number
            const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            return re.test(password);
        }
        
        signupForm.addEventListener('submit', function(event) {
            let isValid = true;
            const fullname = document.getElementById('fullname').value.trim();
            const email = document.getElementById('email').value.trim();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirmPassword').value.trim();
            const terms = document.getElementById('terms').checked;
            
            // Clear previous errors
            document.getElementById('fullnameError').textContent = '';
            document.getElementById('emailError').textContent = '';
            document.getElementById('usernameError').textContent = '';
            document.getElementById('passwordError').textContent = '';
            document.getElementById('confirmPasswordError').textContent = '';
            
            // Validate fullname
            if (!fullname) {
                document.getElementById('fullnameError').textContent = 'Full name is required';
                isValid = false;
            }
            
            // Validate email
            if (!email) {
                document.getElementById('emailError').textContent = 'Email is required';
                isValid = false;
            } else if (!isValidEmail(email)) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address';
                isValid = false;
            }
            
            // Validate username
            if (!username) {
                document.getElementById('usernameError').textContent = 'Username is required';
                isValid = false;
            } else if (username.length < 3) {
                document.getElementById('usernameError').textContent = 'Username must be at least 3 characters';
                isValid = false;
            }
            
            // Validate password
            if (!password) {
                document.getElementById('passwordError').textContent = 'Password is required';
                isValid = false;
            } else if (!isValidPassword(password)) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters with at least one uppercase letter, one lowercase letter, and one number';
                isValid = false;
            }
            
            // Validate password confirmation
            if (password !== confirmPassword) {
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                isValid = false;
            }
            
            // Validate terms acceptance
            if (!terms) {
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