<?php

/**
 * routes.coashcenter.php
 * CRUD Operations for MSTR_COACH_CENTER
 */

function handleCoachCenter($pdo, $method, $endpoint)
{
    // Extract ID if present (e.g., /api/tms/v1/coach-center/ID)
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                $stmt = $pdo->prepare("SELECT * FROM MSTR_COACH_CENTER WHERE MC_CENTER_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Coach Center not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MSTR_COACH_CENTER");
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

            $sql = "INSERT INTO MSTR_COACH_CENTER 
                    (MC_CENTER_ID, MIC_CENTER_NM, MIC_CENTER_ADDRESS, MIC_CENTER_CITY, MIC_CENTER_STATE, MIC_CENTER_PIN, MIC_CENTER_COUNTRY, MIC_CENTER_EMAILID, MIC_CENTER_CONTACTNO) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MC_CENTER_ID'] ?? null,
                    $data['MIC_CENTER_NM'] ?? null,
                    $data['MIC_CENTER_ADDRESS'] ?? null,
                    $data['MIC_CENTER_CITY'] ?? null,
                    $data['MIC_CENTER_STATE'] ?? null,
                    $data['MIC_CENTER_PIN'] ?? null,
                    $data['MIC_CENTER_COUNTRY'] ?? null,
                    $data['MIC_CENTER_EMAILID'] ?? null,
                    $data['MIC_CENTER_CONTACTNO'] ?? null
                ]);
                echo json_encode(["status" => "success", "message" => "Coach Center created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Coach Center ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MSTR_COACH_CENTER SET 
                    MIC_CENTER_NM = ?, MIC_CENTER_ADDRESS = ?, MIC_CENTER_CITY = ?, 
                    MIC_CENTER_STATE = ?, MIC_CENTER_PIN = ?, MIC_CENTER_COUNTRY = ?, 
                    MIC_CENTER_EMAILID = ?, MIC_CENTER_CONTACTNO = ? 
                    WHERE MC_CENTER_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['MIC_CENTER_NM'],
                    $data['MIC_CENTER_ADDRESS'],
                    $data['MIC_CENTER_CITY'],
                    $data['MIC_CENTER_STATE'],
                    $data['MIC_CENTER_PIN'],
                    $data['MIC_CENTER_COUNTRY'],
                    $data['MIC_CENTER_EMAILID'],
                    $data['MIC_CENTER_CONTACTNO'],
                    $resourceId
                ]);
                echo json_encode(["status" => "success", "message" => "Coach Center updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Coach Center ID is required for deletion"]);
                return;
            }

            $stmt = $pdo->prepare("DELETE FROM MSTR_COACH_CENTER WHERE MC_CENTER_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Coach Center deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
