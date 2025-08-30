<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config.php';

// Get authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['message' => 'Authorization token required']);
    exit;
}

// Verify user token
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid token']);
        exit;
    }
    
    $user_id = $user['id'];
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getAchievements($pdo, $user_id);
        break;
    case 'POST':
        checkAndAwardAchievements($pdo, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
}

function getAchievements($pdo, $user_id) {
    try {
        $sql = "SELECT * FROM achievements WHERE user_id = ? ORDER BY earned_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $achievements = $stmt->fetchAll();
        
        echo json_encode($achievements);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function checkAndAwardAchievements($pdo, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    try {
        $newAchievements = [];
        
        // Check for task completion achievements
        if ($action === 'task_completed') {
            // Check for "Productive Planner" achievement (5+ tasks completed)
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tasks WHERE user_id = ? AND completed = true");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            if ($result['count'] >= 5) {
                $achievementId = awardAchievement($pdo, $user_id, 'task_master', 'Task Master', 'Completed 5 or more tasks');
                if ($achievementId) {
                    $newAchievements[] = $achievementId;
                }
            }
            
            // Check for daily streak
            checkStreakAchievement($pdo, $user_id, $newAchievements);
        }
        
        // Check for budget tracking achievements
        if ($action === 'transaction_added') {
            // Check for "Budget Master" achievement (7+ days of tracking)
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT date) as days FROM transactions WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            if ($result['days'] >= 7) {
                $achievementId = awardAchievement($pdo, $user_id, 'budget_master', 'Budget Master', 'Tracked budget for 7+ days');
                if ($achievementId) {
                    $newAchievements[] = $achievementId;
                }
            }
        }
        
        echo json_encode([
            'message' => 'Achievements checked',
            'new_achievements' => $newAchievements
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function checkStreakAchievement($pdo, $user_id, &$newAchievements) {
    // Check for 3-day streak
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT DATE(updated_at)) as streak 
        FROM tasks 
        WHERE user_id = ? 
        AND completed = true 
        AND updated_at >= CURRENT_DATE - INTERVAL '7 days'
        ORDER BY updated_at DESC
        LIMIT 3
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result['streak'] >= 3) {
        $achievementId = awardAchievement($pdo, $user_id, 'streak_3', '3-Day Streak', 'Completed tasks for 3 consecutive days');
        if ($achievementId) {
            $newAchievements[] = $achievementId;
        }
    }
}

function awardAchievement($pdo, $user_id, $type, $name, $description) {
    // Check if achievement already awarded
    $stmt = $pdo->prepare("SELECT id FROM achievements WHERE user_id = ? AND type = ?");
    $stmt->execute([$user_id, $type]);
    
    if ($stmt->fetch()) {
        return null; // Achievement already awarded
    }
    
    // Award new achievement
    $stmt = $pdo->prepare("INSERT INTO achievements (user_id, type, name, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $name, $description]);
    
    return $pdo->lastInsertId();
}
?>