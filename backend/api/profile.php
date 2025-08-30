<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

// Get authorization header
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    http_response_code(401);
    echo json_encode(['message' => 'Authorization token required']);
    exit;
}

// Verify user token and get user data
try {
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE token = ?");
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
        getProfile($pdo, $user_id);
        break;
    case 'PUT':
        updateProfile($pdo, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
}

function getProfile($pdo, $user_id) {
    try {
        // Get user stats
        $stats = [];
        
        // Task stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN completed THEN 1 ELSE 0 END) as completed_tasks
            FROM tasks 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats['tasks'] = $stmt->fetch();
        
        // Transaction stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_transactions,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expenses
            FROM transactions 
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $stats['transactions'] = $stmt->fetch();
        
        // Achievement stats
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_achievements FROM achievements WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['achievements'] = $stmt->fetch();
        
        // Get user info
        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        echo json_encode([
            'user' => $user,
            'stats' => $stats
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateProfile($pdo, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['name'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Name is required']);
        return;
    }
    
    try {
        $sql = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$data['name'], $user_id]);
        
        echo json_encode(['message' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>