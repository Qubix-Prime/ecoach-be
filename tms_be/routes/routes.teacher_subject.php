<?php

/**
 * routes.teacher_subject.php
 * CRUD Operations for MAP_TCHR_SUB
 */

function handleTeacherSubject($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MAP_TCHR_SUB WHERE MTS_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Teacher-Subject mapping not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MAP_TCHR_SUB");
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

            $sql = "INSERT INTO MAP_TCHR_SUB (MTS_ID, MTS_TCHR_ID, MTS_SUB, MTS_BR_ID) VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MTS_ID'] ?? null,
                    $data['MTS_TCHR_ID'] ?? null,
                    $data['MTS_SUB'] ?? null,
                    $data['MTS_BR_ID'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Teacher-Subject mapping created successfully"]);
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
            $sql = "UPDATE MAP_TCHR_SUB SET MTS_TCHR_ID = ?, MTS_SUB = ?, MTS_BR_ID = ? WHERE MTS_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MTS_TCHR_ID'],
                    $data['MTS_SUB'],
                    $data['MTS_BR_ID'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Teacher-Subject mapping updated successfully"]);
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

            $stmt = $pdo->prepare("DELETE FROM MAP_TCHR_SUB WHERE MTS_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Teacher-Subject mapping deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
