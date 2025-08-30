<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if(isset($data->action)) {
        if($data->action === 'register') {
            // Check if user already exists
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $data->email);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(array("message" => "User already exists with this email."));
                exit;
            }
            
            // Registration logic
            $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $db->prepare($query);
            
            $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            
            $stmt->bindParam(':username', $data->username);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':password', $password_hash);
            
            if($stmt->execute()) {
                $user_id = $db->lastInsertId();
                
                // Create achievements record
                $achievementsQuery = "INSERT INTO achievements (user_id) VALUES (:user_id)";
                $achievementsStmt = $db->prepare($achievementsQuery);
                $achievementsStmt->bindParam(':user_id', $user_id);
                $achievementsStmt->execute();
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "User created successfully.",
                    "id" => $user_id,
                    "username" => $data->username
                ));
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Unable to create user."));
            }
        }
        elseif($data->action === 'login') {
            // Login logic
            $query = "SELECT id, username, password FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $id = $row['id'];
                $username = $row['username'];
                $password2 = $row['password'];
                
                if(password_verify($data->password, $password2)) {
                    // Generate token (userid:username:timestamp)
                    $token = base64_encode("$id:$username:" . time());
                    
                    http_response_code(200);
                    echo json_encode(
                        array(
                            "message" => "Login successful.",
                            "id" => $id,
                            "username" => $username,
                            "token" => $token
                        )
                    );
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid credentials."));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid credentials."));
            }
        }
    }
}
?>