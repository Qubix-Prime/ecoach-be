<?php

/**
 * routes.branch.php
 * CRUD Operations for CC_BRANCH
 */

function handleBranch($pdo, $method, $endpoint)
{
    // Extract ID if present (e.g., /api/tms/v1/branch/ID)
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM CC_BRANCH WHERE CC_BR_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Branch not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM CC_BRANCH");
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
                return;
            }

            $sql = "INSERT INTO CC_BRANCH 
                    (CC_BR_ID, CC_BR_CENTER_ID, CC_BR_NM, CC_BR_ADDRESS, CC_BR_CITY, CC_BR_STATE, CC_BR_PIN, CC_BR_COUNTRY, CC_BR_EMAILID, CC_BR_CONTACTNO) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['CC_BR_ID'] ?? null,
                    $data['CC_BR_CENTER_ID'] ?? null,
                    $data['CC_BR_NM'] ?? null,
                    $data['CC_BR_ADDRESS'] ?? null,
                    $data['CC_BR_CITY'] ?? null,
                    $data['CC_BR_STATE'] ?? null,
                    $data['CC_BR_PIN'] ?? null,
                    $data['CC_BR_COUNTRY'] ?? null,
                    $data['CC_BR_EMAILID'] ?? null,
                    $data['CC_BR_CONTACTNO'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Branch created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Branch ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE CC_BRANCH SET 
                    CC_BR_CENTER_ID = ?, CC_BR_NM = ?, CC_BR_ADDRESS = ?, 
                    CC_BR_CITY = ?, CC_BR_STATE = ?, CC_BR_PIN = ?, 
                    CC_BR_COUNTRY = ?, CC_BR_EMAILID = ?, CC_BR_CONTACTNO = ? 
                    WHERE CC_BR_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['CC_BR_CENTER_ID'],
                    $data['CC_BR_NM'],
                    $data['CC_BR_ADDRESS'],
                    $data['CC_BR_CITY'],
                    $data['CC_BR_STATE'],
                    $data['CC_BR_PIN'],
                    $data['CC_BR_COUNTRY'],
                    $data['CC_BR_EMAILID'],
                    $data['CC_BR_CONTACTNO'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Branch updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Branch ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM CC_BRANCH WHERE CC_BR_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Branch deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
