<?php

/**
 * routes.parent.php
 * CRUD Operations for MSTR_PRNT
 */

function handleParent($pdo, $method, $endpoint)
{
    // Extract ID if present
    $parts = explode('/', trim($endpoint, '/'));
    $resourceId = isset($parts[1]) ? $parts[1] : null;
    $subAction = isset($parts[2]) ? $parts[2] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                if ($subAction === 'students') {
                    $sql = "SELECT stu.STU_ID, stu.STU_NM, stu.STU_EMAILID, stu.STU_CONTACTNO, stu.STU_CITY, stu.STU_DOB 
                            FROM MSTR_STU stu
                            WHERE stu.STU_PRNT_ID = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$resourceId]);
                    echo json_encode($stmt->fetchAll());
                    return;
                }

                $stmt = $pdo->prepare("SELECT * FROM MSTR_PRNT WHERE PRNT_ID = ?");
                $stmt->execute([$resourceId]);
                $result = $stmt->fetch();
                if ($result) {
                    echo json_encode($result);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "error", "message" => "Parent not found"]);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM MSTR_PRNT");
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

            $sql = "INSERT INTO MSTR_PRNT 
                    (PRNT_ID, PRNT_NM, PRNT_AADHAR, PRNT_EMAILID, PRNT_CONTACTNO, PRNT_ADDRESS, PRNT_CITY, PRNT_STATE, PRNT_PIN, PRNT_COUNTRY) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['PRNT_ID'] ?? null,
                    $data['PRNT_NM'] ?? null,
                    $data['PRNT_AADHAR'] ?? null,
                    $data['PRNT_EMAILID'] ?? null,
                    $data['PRNT_CONTACTNO'] ?? null,
                    $data['PRNT_ADDRESS'] ?? null,
                    $data['PRNT_CITY'] ?? null,
                    $data['PRNT_STATE'] ?? null,
                    $data['PRNT_PIN'] ?? null,
                    $data['PRNT_COUNTRY'] ?? null
                ]);

                // Auto create parent user
                $userEmail = $data['PRNT_EMAILID'] ?? '';
                $userNm = $data['PRNT_NM'] ?? '';
                $userId = $data['PRNT_ID'] ?? '';
                if ($userEmail && $userId) {
                    $userStmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES (?, ?, 'Parent', ?, ?, ?)");
                    $userStmt->execute([
                        $userId,
                        password_hash('parent@123', PASSWORD_DEFAULT),
                        $userId,
                        $userEmail,
                        $userNm
                    ]);
                }

                echo json_encode(["status" => "success", "message" => "Parent created successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'PUT':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Parent ID is required for update"]);
                return;
            }

            $data = json_decode(file_get_contents("php://input"), true);
            $sql = "UPDATE MSTR_PRNT SET 
                    PRNT_NM = ?, PRNT_AADHAR = ?, PRNT_EMAILID = ?, 
                    PRNT_CONTACTNO = ?, PRNT_ADDRESS = ?, PRNT_CITY = ?, 
                    PRNT_STATE = ?, PRNT_PIN = ?, PRNT_COUNTRY = ? 
                    WHERE PRNT_ID = ?";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    $data['PRNT_NM'],
                    $data['PRNT_AADHAR'],
                    $data['PRNT_EMAILID'],
                    $data['PRNT_CONTACTNO'],
                    $data['PRNT_ADDRESS'],
                    $data['PRNT_CITY'],
                    $data['PRNT_STATE'],
                    $data['PRNT_PIN'],
                    $data['PRNT_COUNTRY'],
                    $resourceId
                ]);

                // Sync credentials
                $userStmt = $pdo->prepare("UPDATE MSTR_USER SET EMAIL = ?, NAME = ? WHERE REF_ID = ? AND ROLE = 'Parent'");
                $userStmt->execute([
                    $data['PRNT_EMAILID'],
                    $data['PRNT_NM'],
                    $resourceId
                ]);

                echo json_encode(["status" => "success", "message" => "Parent updated successfully"]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;

        case 'DELETE':
            if (!$resourceId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Parent ID is required for deletion"]);
                return;
            }

            // Sync user deletion
            $userStmt = $pdo->prepare("DELETE FROM MSTR_USER WHERE REF_ID = ? AND ROLE = 'Parent'");
            $userStmt->execute([$resourceId]);

            $stmt = $pdo->prepare("DELETE FROM MSTR_PRNT WHERE PRNT_ID = ?");
            $stmt->execute([$resourceId]);
            echo json_encode(["status" => "success", "message" => "Parent deleted successfully"]);
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            break;
    }
}
