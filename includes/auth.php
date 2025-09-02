<?php
// Include database configuration
require_once 'config.php';

/**
 * User login function
 * @param string $username Username
 * @param string $password Password
 * @return array Result with success status and message
 */
function user_login($username, $password) {
    global $conn;
    
    $username = sanitize_input($username);
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, username, password, fullname, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a new session
            session_regenerate_id();
            
            // Store user data in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            
            // Update last login time (optional)
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Incorrect password'];
        }
    } else {
        return ['success' => false, 'message' => 'Username not found'];
    }
}

/**
 * User registration function
 * @param array $user_data User data
 * @return array Result with success status and message
 */

function user_register($user_data) {
    global $conn;

    try {
        // Sanitize input
        $username = sanitize_input($user_data['username']);
        $email = sanitize_input($user_data['email']);
        $fullname = sanitize_input($user_data['fullname']);
        $password = $user_data['password'];

        // Username check
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Email check
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $fullname, $email);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Default user settings
            $settings_stmt = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
            $settings_stmt->bind_param("i", $user_id);
            $settings_stmt->execute();

            // Auto login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['fullname'] = $fullname;

            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed: ' . $conn->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
    }
}


/**
 * Get user profile data
 * @param int $user_id User ID
 * @return array User data or false if not found
 */
function get_user_profile($user_id) {
    global $conn;
    
    $stmt = $conn->prepare(
        "SELECT u.*, s.email_notifications, s.lab_reminders, s.new_lab_alerts, s.security_alerts 
         FROM users u 
         LEFT JOIN user_settings s ON u.user_id = s.user_id 
         WHERE u.user_id = ?"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    } else {
        return false;
    }
}

/**
 * Update user profile
 * @param int $user_id User ID
 * @param array $profile_data Profile data to update
 * @return array Result with success status and message
 */
function update_user_profile($user_id, $profile_data) {
    global $conn;
    
    // Sanitize inputs
    $fullname = sanitize_input($profile_data['fullname']);
    $email = sanitize_input($profile_data['email']);
    $bio = isset($profile_data['bio']) ? sanitize_input($profile_data['bio']) : null;
    $country = isset($profile_data['country']) ? sanitize_input($profile_data['country']) : null;
    $timezone = isset($profile_data['timezone']) ? sanitize_input($profile_data['timezone']) : null;
    
    // Check if email already exists for another user
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    // Update user profile
    $stmt = $conn->prepare("UPDATE users SET fullname = ?, email = ?, bio = ?, country = ?, timezone = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $fullname, $email, $bio, $country, $timezone, $user_id);
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['fullname'] = $fullname;
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Profile update failed: ' . $conn->error];
    }
}

/**
 * Update user password
 * @param int $user_id User ID
 * @param string $current_password Current password
 * @param string $new_password New password
 * @return array Result with success status and message
 */
function update_user_password($user_id, $current_password, $new_password) {
    global $conn;
    
    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                return ['success' => true, 'message' => 'Password updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Password update failed: ' . $conn->error];
            }
        } else {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
    } else {
        return ['success' => false, 'message' => 'User not found'];
    }
}

/**
 * Update user settings
 * @param int $user_id User ID
 * @param array $settings_data Settings data to update
 * @return array Result with success status and message
 */
function update_user_settings($user_id, $settings_data) {
    global $conn;
    
    // Extract settings
    $email_notifications = isset($settings_data['email_notifications']) ? 1 : 0;
    $lab_reminders = isset($settings_data['lab_reminders']) ? 1 : 0;
    $new_lab_alerts = isset($settings_data['new_lab_alerts']) ? 1 : 0;
    $security_alerts = isset($settings_data['security_alerts']) ? 1 : 0;
    
    // Check if settings exist for user
    $check_stmt = $conn->prepare("SELECT setting_id FROM user_settings WHERE user_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing settings
        $stmt = $conn->prepare("
            UPDATE user_settings 
            SET email_notifications = ?, lab_reminders = ?, new_lab_alerts = ?, security_alerts = ? 
            WHERE user_id = ?
        ");
        $stmt->bind_param("iiiii", $email_notifications, $lab_reminders, $new_lab_alerts, $security_alerts, $user_id);
    } else {
        // Insert new settings
        $stmt = $conn->prepare("
            INSERT INTO user_settings (user_id, email_notifications, lab_reminders, new_lab_alerts, security_alerts) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiii", $user_id, $email_notifications, $lab_reminders, $new_lab_alerts, $security_alerts);
    }
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Settings updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Settings update failed: ' . $conn->error];
    }
}

/**
 * Log out the current user
 */
function user_logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    redirect('index.html');
}

/**
 * Check if the password meets the requirements
 * @param string $password Password to validate
 * @return boolean True if valid, false otherwise
 */
function is_valid_password($password) {
    // At least 8 characters, one uppercase, one lowercase, one number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return boolean True if valid, false otherwise
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>