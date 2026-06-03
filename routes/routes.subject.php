<?php

/**
 * routes.subject.php
 * CRUD Operations for MSTR_SUB
 */

function handleSubject($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MSTR_SUB WHERE SUB_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Subject not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MSTR_SUB");
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

            $sql = "INSERT INTO MSTR_SUB (SUB_ID, SUB_NAME, SUB_BOARD, SUB_CLASS, SUB_LANG) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['SUB_ID'] ?? null,
                    $data['SUB_NAME'] ?? null,
                    $data['SUB_BOARD'] ?? null,
                    $data['SUB_CLASS'] ?? null,
                    $data['SUB_LANG'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Subject created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Subject ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MSTR_SUB SET SUB_NAME = ?, SUB_BOARD = ?, SUB_CLASS = ?, SUB_LANG = ? WHERE SUB_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['SUB_NAME'],
                    $data['SUB_BOARD'],
                    $data['SUB_CLASS'],
                    $data['SUB_LANG'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Subject updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Subject ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM MSTR_SUB WHERE SUB_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Subject deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
