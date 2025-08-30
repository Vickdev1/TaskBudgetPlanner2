<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
        getTasks($pdo, $user_id);
        break;
    case 'POST':
        createTask($pdo, $user_id);
        break;
    case 'PUT':
        updateTask($pdo, $user_id);
        break;
    case 'DELETE':
        deleteTask($pdo, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
}

function getTasks($pdo, $user_id) {
    $category = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $params = [$user_id];
    
    if (!empty($category) && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if (!empty($status) && $status !== 'all') {
        if ($status === 'completed') {
            $sql .= " AND completed = true";
        } else if ($status === 'pending') {
            $sql .= " AND completed = false";
        }
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        
        echo json_encode($tasks);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createTask($pdo, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['title', 'category', 'priority'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['message' => "$field is required"]);
            return;
        }
    }
    
    try {
        $sql = "INSERT INTO tasks (user_id, title, description, category, due_date, priority) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $data['title'],
            $data['description'] ?? '',
            $data['category'],
            $data['due_date'] ?? null,
            $data['priority']
        ]);
        
        $task_id = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode([
            'message' => 'Task created successfully',
            'task_id' => $task_id
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateTask($pdo, $user_id) {
    $task_id = $_GET['id'] ?? '';
    
    if (empty($task_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Task ID required']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Check if task belongs to user
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['message' => 'Task not found']);
            return;
        }
        
        $fields = [];
        $params = [];
        
        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $params[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $params[] = $data['description'];
        }
        
        if (isset($data['category'])) {
            $fields[] = 'category = ?';
            $params[] = $data['category'];
        }
        
        if (isset($data['due_date'])) {
            $fields[] = 'due_date = ?';
            $params[] = $data['due_date'];
        }
        
        if (isset($data['priority'])) {
            $fields[] = 'priority = ?';
            $params[] = $data['priority'];
        }
        
        if (isset($data['completed'])) {
            $fields[] = 'completed = ?';
            $params[] = $data['completed'];
        }
        
        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        
        $params[] = $task_id;
        $params[] = $user_id;
        
        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['message' => 'Task updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteTask($pdo, $user_id) {
    $task_id = $_GET['id'] ?? '';
    
    if (empty($task_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Task ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Task deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Task not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>