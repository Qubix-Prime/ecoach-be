<?php

/**
 * routes.teacher.php
 * CRUD Operations for MSTR_TEACHER
 */

function handleTeacher($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MSTR_TEACHER WHERE TCHR_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Teacher not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MSTR_TEACHER");
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

            $sql = "INSERT INTO MSTR_TEACHER 
                    (TCHR_ID, TCHR_NM, TCHR_AADHAR, TCHR_PANCARD, TCHR_EMAILID, TCHR_CONTACTNO, TCHR_ADDRESS, TCHR_CITY, TCHR_STATE, TCHR_PIN, TCHR_COUNTRY) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['TCHR_ID'] ?? null,
                    $data['TCHR_NM'] ?? null,
                    $data['TCHR_AADHAR'] ?? null,
                    $data['TCHR_PANCARD'] ?? null,
                    $data['TCHR_EMAILID'] ?? null,
                    $data['TCHR_CONTACTNO'] ?? null,
                    $data['TCHR_ADDRESS'] ?? null,
                    $data['TCHR_CITY'] ?? null,
                    $data['TCHR_STATE'] ?? null,
                    $data['TCHR_PIN'] ?? null,
                    $data['TCHR_COUNTRY'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Teacher created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Teacher ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MSTR_TEACHER SET 
                    TCHR_NM = ?, TCHR_AADHAR = ?, TCHR_PANCARD = ?, 
                    TCHR_EMAILID = ?, TCHR_CONTACTNO = ?, TCHR_ADDRESS = ?, 
                    TCHR_CITY = ?, TCHR_STATE = ?, TCHR_PIN = ?, 
                    TCHR_COUNTRY = ? 
                    WHERE TCHR_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['TCHR_NM'],
                    $data['TCHR_AADHAR'],
                    $data['TCHR_PANCARD'],
                    $data['TCHR_EMAILID'],
                    $data['TCHR_CONTACTNO'],
                    $data['TCHR_ADDRESS'],
                    $data['TCHR_CITY'],
                    $data['TCHR_STATE'],
                    $data['TCHR_PIN'],
                    $data['TCHR_COUNTRY'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Teacher updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Teacher ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM MSTR_TEACHER WHERE TCHR_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Teacher deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
