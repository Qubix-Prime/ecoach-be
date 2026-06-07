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
    $subAction = isset($parts[2]) ? $parts[2] : null;

    switch ($method) {
        case 'GET':
            if ($resourceId) {
                if ($subAction === 'subjects') {
                    $sql = "SELECT mts.MTS_ID, sub.SUB_ID, sub.SUB_NAME, sub.SUB_BOARD, sub.SUB_CLASS, sub.SUB_LANG, br.CC_BR_ID, br.CC_BR_NM, br.CC_BR_CITY 
                            FROM MAP_TCHR_SUB mts
                            JOIN MSTR_SUB sub ON mts.MTS_SUB = sub.SUB_ID
                            JOIN CC_BRANCH br ON mts.MTS_BR_ID = br.CC_BR_ID
                            WHERE mts.MTS_TCHR_ID = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$resourceId]);
                    echo json_encode($stmt->fetchAll());
                    return;
                }

                if ($subAction === 'students') {
                    $sql = "SELECT mss.MSS_ID, mss.MSS_STU_DOW, mss.MSS_STU_TIME, stu.STU_ID, stu.STU_NM, stu.STU_EMAILID, stu.STU_CONTACTNO, sub.SUB_NAME, sub.SUB_CLASS 
                            FROM MAP_STU_SUB mss
                            JOIN MSTR_STU stu ON mss.MSS_STU_ID = stu.STU_ID
                            JOIN MAP_TCHR_SUB mts ON mss.MSS_STU_TCHR = mts.MTS_ID
                            JOIN MSTR_SUB sub ON mts.MTS_SUB = sub.SUB_ID
                            WHERE mts.MTS_TCHR_ID = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$resourceId]);
                    echo json_encode($stmt->fetchAll());
                    return;
                }

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

                // Auto create teacher user
                $userEmail = $data['TCHR_EMAILID'] ?? '';
                $userNm = $data['TCHR_NM'] ?? '';
                $userId = $data['TCHR_ID'] ?? '';
                if ($userEmail && $userId) {
                    $userStmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES (?, ?, 'Teacher', ?, ?, ?)");
                    $userStmt->execute([
                        $userId,
                        password_hash('teach@123', PASSWORD_DEFAULT),
                        $userId,
                        $userEmail,
                        $userNm
                    ]);
                }

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

                // Sync credentials
                $userStmt = $pdo->prepare("UPDATE MSTR_USER SET EMAIL = ?, NAME = ? WHERE REF_ID = ? AND ROLE = 'Teacher'");
                $userStmt->execute([
                    $data['TCHR_EMAILID'],
                    $data['TCHR_NM'],
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

            // Sync user deletion
            $userStmt = $pdo->prepare("DELETE FROM MSTR_USER WHERE REF_ID = ? AND ROLE = 'Teacher'");
            $userStmt->execute([$resourceId]);

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
