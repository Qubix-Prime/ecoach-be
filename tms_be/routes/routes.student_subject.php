<?php

/**
 * routes.student_subject.php
 * CRUD Operations for MAP_STU_SUB
 */

function handleStudentSubject($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MAP_STU_SUB WHERE MSS_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Student-Subject mapping not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MAP_STU_SUB");
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

            $sql = "INSERT INTO MAP_STU_SUB (MSS_ID, MSS_STU_ID, MSS_STU_TCHR, MSS_STU_DOW, MSS_STU_TIME) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MSS_ID'] ?? null,
                    $data['MSS_STU_ID'] ?? null,
                    $data['MSS_STU_TCHR'] ?? null,
                    $data['MSS_STU_DOW'] ?? null,
                    $data['MSS_STU_TIME'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Student-Subject mapping created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Mapping ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MAP_STU_SUB SET MSS_STU_ID = ?, MSS_STU_TCHR = ?, MSS_STU_DOW = ?, MSS_STU_TIME = ? WHERE MSS_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MSS_STU_ID'],
                    $data['MSS_STU_TCHR'],
                    $data['MSS_STU_DOW'],
                    $data['MSS_STU_TIME'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Student-Subject mapping updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Mapping ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM MAP_STU_SUB WHERE MSS_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Student-Subject mapping deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
