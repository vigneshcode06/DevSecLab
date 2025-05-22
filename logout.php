<?php
// Include authentication functions
require_once 'includes/auth.php';

// Log out the user
user_logout();

// This line will not be reached as user_logout() redirects to index.php
?>