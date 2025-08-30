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
        getTransactions($pdo, $user_id);
        break;
    case 'POST':
        createTransaction($pdo, $user_id);
        break;
    case 'PUT':
        updateTransaction($pdo, $user_id);
        break;
    case 'DELETE':
        deleteTransaction($pdo, $user_id);
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method not allowed']);
}

function getTransactions($pdo, $user_id) {
    $type = $_GET['type'] ?? '';
    $category = $_GET['category'] ?? '';
    $month = $_GET['month'] ?? '';
    
    $sql = "SELECT * FROM transactions WHERE user_id = ?";
    $params = [$user_id];
    
    if (!empty($type) && $type !== 'all') {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    
    if (!empty($category) && $category !== 'all') {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if (!empty($month)) {
        $sql .= " AND DATE_TRUNC('month', date::date) = DATE_TRUNC('month', ?::date)";
        $params[] = $month . '-01';
    }
    
    $sql .= " ORDER BY date DESC, created_at DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll();
        
        echo json_encode($transactions);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createTransaction($pdo, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $required = ['amount', 'description', 'category', 'type', 'date'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['message' => "$field is required"]);
            return;
        }
    }
    
    if (!in_array($data['type'], ['income', 'expense'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Type must be income or expense']);
        return;
    }
    
    try {
        $sql = "INSERT INTO transactions (user_id, amount, description, category, type, date) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id,
            $data['amount'],
            $data['description'],
            $data['category'],
            $data['type'],
            $data['date']
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode([
            'message' => 'Transaction created successfully',
            'transaction_id' => $transaction_id
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateTransaction($pdo, $user_id) {
    $transaction_id = $_GET['id'] ?? '';
    
    if (empty($transaction_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Transaction ID required']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Check if transaction belongs to user
        $stmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$transaction_id, $user_id]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['message' => 'Transaction not found']);
            return;
        }
        
        $fields = [];
        $params = [];
        
        if (isset($data['amount'])) {
            $fields[] = 'amount = ?';
            $params[] = $data['amount'];
        }
        
        if (isset($data['description'])) {
            $fields[] = 'description = ?';
            $params[] = $data['description'];
        }
        
        if (isset($data['category'])) {
            $fields[] = 'category = ?';
            $params[] = $data['category'];
        }
        
        if (isset($data['type'])) {
            if (!in_array($data['type'], ['income', 'expense'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Type must be income or expense']);
                return;
            }
            $fields[] = 'type = ?';
            $params[] = $data['type'];
        }
        
        if (isset($data['date'])) {
            $fields[] = 'date = ?';
            $params[] = $data['date'];
        }
        
        $params[] = $transaction_id;
        $params[] = $user_id;
        
        $sql = "UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['message' => 'Transaction updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteTransaction($pdo, $user_id) {
    $transaction_id = $_GET['id'] ?? '';
    
    if (empty($transaction_id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Transaction ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
        $stmt->execute([$transaction_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Transaction deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Transaction not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>