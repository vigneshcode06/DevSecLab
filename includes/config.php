<?php
// Database configuration
$db_host = "";
$db_user = ""; // Change this to your MySQL username
$db_pass = ""; // Change this to your MySQL password
$db_name = "";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Helper function to sanitize user input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Helper function to redirect to a page
function redirect($location) {
    header("Location: $location");
    exit;
}

// Helper function to display error message
function display_error($message) {
    return "<div class='form-error'>$message</div>";
}

// Helper function to display success message
function display_success($message) {
    return "<div class='form-success'>$message</div>";
}
?>
