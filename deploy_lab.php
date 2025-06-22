<?php
require_once 'includes/auth.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$lab_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$username = $_SESSION['username'];

$lab_folders = [
    1 => "ubuntu",
    2 => "java_dev",
    3 => "mysql_webbench",
    10 => "mysql_test"
];

$lab_folder = $lab_folders[$lab_id] ?? "ubuntu";
$user_lab_path = "C:/xampp/htdocs/betaboy/SentinelLab/user_labs/$username/$lab_folder";

// Decide action: start or stop
$action = $_POST['action'] ?? 'start';

if ($action === 'stop') {
    $command = "cd /d $user_lab_path && docker compose down 2>&1";
    $title = "🛑 Stopping Lab Container";
} else {
    $command = "cd /d $user_lab_path && docker compose up -d 2>&1";
    $title = "🚀 Deploying Lab Container";
}

$output = shell_exec($command);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Deployment - <?php echo htmlspecialchars($username); ?></title>
    <style>
        body {
            font-family: monospace;
            background: #0d1117;
            color: #c9d1d9;
            padding: 20px;
        }
        pre {
            background: #161b22;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
        form {
            margin-top: 20px;
        }
        button {
            background-color: #21262d;
            color: #58a6ff;
            border: 1px solid #30363d;
            padding: 8px 16px;
            margin-right: 10px;
            cursor: pointer;
            border-radius: 6px;
        }
        a {
            color: #58a6ff;
        }
    </style>
</head>
<body>
    <h1><?php echo $title; ?> - <?php echo htmlspecialchars($username); ?></h1>
    <p><strong>Lab ID:</strong> <?php echo $lab_id; ?></p>
    <p><strong>Path:</strong> <?php echo htmlspecialchars($user_lab_path); ?></p>

    <h3>📝 Output:</h3>
    <pre><?php echo htmlspecialchars($output); ?></pre>

    <form method="post">
        <button type="submit" name="action" value="start">🚀 Start Again</button>
        <button type="submit" name="action" value="stop">🛑 Stop Container</button>
    </form>
    <br>
    <a href="lab_detail.php?id=<?php echo $lab_id; ?>">⬅️ Back to Lab</a>
</body>
</html>
