<?php
/**
 * routes.auth.php
 * Authentication routing for user login
 */

function handleAuth($pdo, $method, $endpoint)
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
        return;
    }

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';

    if (empty($username) || empty($password) || empty($role)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Username, password, and role are required."]);
        return;
    }

    try {
        // Find user by username or email, and exact role
        $stmt = $pdo->prepare("SELECT * FROM MSTR_USER WHERE (USERNAME = ? OR EMAIL = ?) AND ROLE = ?");
        $stmt->execute([$username, $username, $role]);
        $user = $stmt->fetch();

        if ($user) {
            // Check password
            if (password_verify($password, $user['PASSWORD']) || $password === $user['PASSWORD']) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "user" => [
                        "username" => $user['USERNAME'],
                        "role" => $user['ROLE'],
                        "name" => $user['NAME'],
                        "email" => $user['EMAIL'],
                        "ref_id" => $user['REF_ID']
                    ]
                ]);
                return;
            }
        }

        // Authentication failed
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid username, password, or role selector."]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database query failed: " . $e->getMessage()]);
    }
}
