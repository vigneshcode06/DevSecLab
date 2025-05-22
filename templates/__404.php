<?php
// Include authentication functions
require_once 'includes/auth.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VLab - Page Not Found</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">
                <span>4</span>
                <span class="error-code-middle">0</span>
                <span>4</span>
            </div>
            <h1 class="error-title">System Breach</h1>
            <p class="error-message">The page you are looking for does not exist or has been moved to another dimension.</p>
            <div class="error-actions">
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="btn-primary"><i class="fas fa-home"></i> Back to Dashboard</a>
                <?php else: ?>
                    <a href="index.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Login</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="error-animation">
            <div class="glitch-effect"></div>
        </div>
    </div>

    <div class="cyber-grid error-grid">
        <div class="grid-line horizontal"></div>
        <div class="grid-line horizontal"></div>
        <div class="grid-line horizontal"></div>
        <div class="grid-line vertical"></div>
        <div class="grid-line vertical"></div>
        <div class="grid-line vertical"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add glitch effect animation
        const glitchElement = document.querySelector('.glitch-effect');
        
        setInterval(function() {
            glitchElement.classList.add('active');
            
            setTimeout(function() {
                glitchElement.classList.remove('active');
            }, 200);
        }, 3000);
    });
    </script>
</body>
</html>