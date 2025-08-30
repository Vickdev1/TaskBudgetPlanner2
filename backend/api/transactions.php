<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get user ID from token
function getUserIdFromToken($db) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return null;
    }
    
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token = str_replace('Bearer ', '', $authHeader);
    $decoded = base64_decode($token);
    $parts = explode(':', $decoded);
    
    if (count($parts) < 2) {
        return null;
    }
    
    return $parts[0]; // user ID is the first part
}

$user_id = getUserIdFromToken($db);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized access."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all transactions for user
    $query = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $transactions = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $transactions[] = $row;
    }
    
    http_response_code(200);
    echo json_encode($transactions);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new transaction
    $data = json_decode(file_get_contents("php://input"));
    
    $query = "INSERT INTO transactions (user_id, amount, description, category, type) 
              VALUES (:user_id, :amount, :description, :category, :type)";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':amount', $data->amount);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':category', $data->category);
    $stmt->bindParam(':type', $data->type);
    
    if($stmt->execute()) {
        $transaction_id = $db->lastInsertId();
        
        // Get the created transaction
        $query = "SELECT * FROM transactions WHERE id = :transaction_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id);
        $stmt->execute();
        
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        http_response_code(201);
        echo json_encode(array("message" => "Transaction created successfully.", "transaction" => $transaction));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create transaction."));
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Delete transaction
    $transaction_id = $_GET['id'];
    
    $query = "DELETE FROM transactions WHERE id = :transaction_id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':transaction_id', $transaction_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if($stmt->execute()) {
        http_response_code(200);
        echo json_encode(array("message" => "Transaction deleted successfully."));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to delete transaction."));
    }
}
?>