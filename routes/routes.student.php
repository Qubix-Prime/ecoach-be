<?php

/**
 * routes.student.php
 * CRUD Operations for MSTR_STU
 */

function handleStudent($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MSTR_STU WHERE STU_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Student not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MSTR_STU");
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

            $sql = "INSERT INTO MSTR_STU 
                    (STU_ID, STU_NM, STU_AADHAR, STU_EMAILID, STU_CONTACTNO, STU_ADDRESS, STU_CITY, STU_STATE, STU_PIN, STU_COUNTRY, STU_DOB, STU_PRNT_ID) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['STU_ID'] ?? null,
                    $data['STU_NM'] ?? null,
                    $data['STU_AADHAR'] ?? null,
                    $data['STU_EMAILID'] ?? null,
                    $data['STU_CONTACTNO'] ?? null,
                    $data['STU_ADDRESS'] ?? null,
                    $data['STU_CITY'] ?? null,
                    $data['STU_STATE'] ?? null,
                    $data['STU_PIN'] ?? null,
                    $data['STU_COUNTRY'] ?? null,
                    $data['STU_DOB'] ?? null,
                    $data['STU_PRNT_ID'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Student created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Student ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MSTR_STU SET 
                    STU_NM = ?, STU_AADHAR = ?, STU_EMAILID = ?, 
                    STU_CONTACTNO = ?, STU_ADDRESS = ?, STU_CITY = ?, 
                    STU_STATE = ?, STU_PIN = ?, STU_COUNTRY = ?, 
                    STU_DOB = ?, STU_PRNT_ID = ? 
                    WHERE STU_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['STU_NM'],
                    $data['STU_AADHAR'],
                    $data['STU_EMAILID'],
                    $data['STU_CONTACTNO'],
                    $data['STU_ADDRESS'],
                    $data['STU_CITY'],
                    $data['STU_STATE'],
                    $data['STU_PIN'],
                    $data['STU_COUNTRY'],
                    $data['STU_DOB'],
                    $data['STU_PRNT_ID'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Student updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Student ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM MSTR_STU WHERE STU_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Student deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
