<?php
// Include database configuration
require_once 'config.php';

/**
 * Get all labs with optional filtering
 * @param array $filters Optional filters (category, difficulty, status)
 * @return array List of labs
 */
function get_labs($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM labs";
    $where_clauses = [];
    $params = [];
    $types = "";
    
    // Apply category filter
    if (isset($filters['category']) && $filters['category'] != 'all') {
        $where_clauses[] = "category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    // Apply difficulty filter
    if (isset($filters['difficulty']) && $filters['difficulty'] != 'all') {
        $where_clauses[] = "difficulty = ?";
        $params[] = $filters['difficulty'];
        $types .= "s";
    }
    
    // Build the complete SQL query
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY difficulty, title";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters if any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labs = [];
    while ($row = $result->fetch_assoc()) {
        $labs[] = $row;
    }
    
    return $labs;
}

/**
 * Get labs with user progress data
 * @param int $user_id User ID
 * @param array $filters Optional filters (category, difficulty, status)
 * @return array List of labs with progress data
 */
function get_user_labs($user_id, $filters = []) {
    global $conn;
    
    $sql = "
        SELECT l.*, COALESCE(ulp.status, 'Not Started') as status, COALESCE(ulp.progress_percentage, 0) as progress_percentage 
        FROM labs l 
        LEFT JOIN user_lab_progress ulp ON l.lab_id = ulp.lab_id AND ulp.user_id = ?
    ";
    
    $params = [$user_id];
    $types = "i";
    $where_clauses = [];
    
    // Apply category filter
    if (isset($filters['category']) && $filters['category'] != 'all') {
        $where_clauses[] = "l.category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    // Apply difficulty filter
    if (isset($filters['difficulty']) && $filters['difficulty'] != 'all') {
        $where_clauses[] = "l.difficulty = ?";
        $params[] = $filters['difficulty'];
        $types .= "s";
    }
    
    // Apply status filter
    if (isset($filters['status']) && $filters['status'] != 'all') {
        $status = $filters['status'];
        
        if ($status == 'notStarted') {
            $where_clauses[] = "(ulp.status IS NULL OR ulp.status = 'Not Started')";
        } else if ($status == 'inProgress') {
            $where_clauses[] = "ulp.status = 'In Progress'";
        } else if ($status == 'completed') {
            $where_clauses[] = "ulp.status = 'Completed'";
        }
    }
    
    // Build the complete SQL query
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }
    
    $sql .= " ORDER BY l.category, l.difficulty, l.title";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labs = [];
    while ($row = $result->fetch_assoc()) {
        $labs[] = $row;
    }
    
    return $labs;
}

/**
 * Get user lab progress summary
 * @param int $user_id User ID
 * @return array Progress summary
 */
function get_user_progress_summary($user_id) {
    global $conn;
    
    // Get total labs count
    $total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM labs");
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_labs = $total_result->fetch_assoc()['total'];
    
    // Get completed labs count
    $completed_stmt = $conn->prepare("
        SELECT COUNT(*) as completed 
        FROM user_lab_progress 
        WHERE user_id = ? AND status = 'Completed'
    ");
    $completed_stmt->bind_param("i", $user_id);
    $completed_stmt->execute();
    $completed_result = $completed_stmt->get_result();
    $completed_labs = $completed_result->fetch_assoc()['completed'];
    
    // Get in-progress labs count
    $in_progress_stmt = $conn->prepare("
        SELECT COUNT(*) as in_progress 
        FROM user_lab_progress 
        WHERE user_id = ? AND status = 'In Progress'
    ");
    $in_progress_stmt->bind_param("i", $user_id);
    $in_progress_stmt->execute();
    $in_progress_result = $in_progress_stmt->get_result();
    $in_progress_labs = $in_progress_result->fetch_assoc()['in_progress'];
    
    // Calculate overall completion percentage
    $completion_percentage = ($total_labs > 0) ? round(($completed_labs / $total_labs) * 100) : 0;
    
    // Get category-wise progress
    $category_stmt = $conn->prepare("
        SELECT 
            l.category, 
            COUNT(l.lab_id) as total_in_category,
            SUM(CASE WHEN ulp.status = 'Completed' THEN 1 ELSE 0 END) as completed_in_category
        FROM labs l
        LEFT JOIN user_lab_progress ulp ON l.lab_id = ulp.lab_id AND ulp.user_id = ?
        GROUP BY l.category
    ");
    $category_stmt->bind_param("i", $user_id);
    $category_stmt->execute();
    $category_result = $category_stmt->get_result();
    
    $category_progress = [];
    while ($row = $category_result->fetch_assoc()) {
        $category_progress[$row['category']] = [
            'total' => $row['total_in_category'],
            'completed' => $row['completed_in_category'],
            'percentage' => ($row['total_in_category'] > 0) ? 
                round(($row['completed_in_category'] / $row['total_in_category']) * 100) : 0
        ];
    }
    
    return [
        'total_labs' => $total_labs,
        'completed_labs' => $completed_labs,
        'in_progress_labs' => $in_progress_labs,
        'completion_percentage' => $completion_percentage,
        'category_progress' => $category_progress
    ];
}

/**
 * Update user lab progress
 * @param int $user_id User ID
 * @param int $lab_id Lab ID
 * @param string $status Status (Not Started, In Progress, Completed)
 * @param int $progress_percentage Progress percentage
 * @return array Result with success status and message
 */
function update_lab_progress($user_id, $lab_id, $status, $progress_percentage) {
    global $conn;
    
    // Check if record exists
    $check_stmt = $conn->prepare("
        SELECT progress_id FROM user_lab_progress 
        WHERE user_id = ? AND lab_id = ?
    ");
    $check_stmt->bind_param("ii", $user_id, $lab_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("
            UPDATE user_lab_progress 
            SET status = ?, progress_percentage = ?, 
                completed_at = CASE WHEN ? = 'Completed' THEN NOW() ELSE completed_at END
            WHERE user_id = ? AND lab_id = ?
        ");
        $stmt->bind_param("sssii", $status, $progress_percentage, $status, $user_id, $lab_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("
            INSERT INTO user_lab_progress (user_id, lab_id, status, progress_percentage, completed_at) 
            VALUES (?, ?, ?, ?, CASE WHEN ? = 'Completed' THEN NOW() ELSE NULL END)
        ");
        $stmt->bind_param("iisss", $user_id, $lab_id, $status, $progress_percentage, $status);
    }
    
    if ($stmt->execute()) {
        // If lab is completed, check for achievements
        if ($status == 'Completed') {
            check_lab_achievements($user_id);
        }
        
        return ['success' => true, 'message' => 'Progress updated successfully'];
    } else {
        return ['success' => false, 'message' => 'Progress update failed: ' . $conn->error];
    }
}

/**
 * Check and award achievements based on lab completion
 * @param int $user_id User ID
 */
function check_lab_achievements($user_id) {
    global $conn;
    
    // Check for "First Blood" achievement (complete first lab)
    $completed_labs_stmt = $conn->prepare("
        SELECT COUNT(*) as completed_count 
        FROM user_lab_progress 
        WHERE user_id = ? AND status = 'Completed'
    ");
    $completed_labs_stmt->bind_param("i", $user_id);
    $completed_labs_stmt->execute();
    $result = $completed_labs_stmt->get_result();
    $completed_count = $result->fetch_assoc()['completed_count'];
    
    if ($completed_count == 1) {
        // Award "First Blood" achievement
        $achievement_id = 1; // First Blood achievement ID
        award_achievement($user_id, $achievement_id);
    }
    
    // Check for other achievements based on lab categories, etc.
    // For example, check for Crypto Master achievement
    $crypto_labs_stmt = $conn->prepare("
        SELECT COUNT(*) as crypto_completed 
        FROM user_lab_progress ulp
        JOIN labs l ON ulp.lab_id = l.lab_id
        WHERE ulp.user_id = ? AND ulp.status = 'Completed'
        AND (l.title LIKE '%crypto%' OR l.category = 'CTF' AND l.title LIKE '%Cryptography%')
    ");
    $crypto_labs_stmt->bind_param("i", $user_id);
    $crypto_labs_stmt->execute();
    $result = $crypto_labs_stmt->get_result();
    $crypto_completed = $result->fetch_assoc()['crypto_completed'];
    
    if ($crypto_completed >= 3) {
        // Award "Crypto Master" achievement
        $achievement_id = 3; // Crypto Master achievement ID
        award_achievement($user_id, $achievement_id);
    }
    
    // Similarly for other achievements...
}

/**
 * Award an achievement to a user
 * @param int $user_id User ID
 * @param int $achievement_id Achievement ID
 */
function award_achievement($user_id, $achievement_id) {
    global $conn;
    
    // Check if user already has this achievement
    $check_stmt = $conn->prepare("
        SELECT user_achievement_id 
        FROM user_achievements 
        WHERE user_id = ? AND achievement_id = ?
    ");
    $check_stmt->bind_param("ii", $user_id, $achievement_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Award new achievement
        $stmt = $conn->prepare("
            INSERT INTO user_achievements (user_id, achievement_id) 
            VALUES (?, ?)
        ");
        $stmt->bind_param("ii", $user_id, $achievement_id);
        $stmt->execute();
    }
}

/**
 * Get user achievements
 * @param int $user_id User ID
 * @return array User achievements
 */
function get_user_achievements($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, ua.achieved_at,
        CASE WHEN ua.user_achievement_id IS NOT NULL THEN 1 ELSE 0 END as unlocked
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.achievement_id = ua.achievement_id AND ua.user_id = ?
        ORDER BY a.achievement_id
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $achievements = [];
    while ($row = $result->fetch_assoc()) {
        $achievements[] = $row;
    }
    
    return $achievements;
}
?>