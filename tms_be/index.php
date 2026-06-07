<?php

/**
 * TMS API Entry Point
 * Port: 9090 (configured in .env)
 * Base Endpoint: api/tms/v1
 */

// Allow all CORS origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include Database Connection
require_once __DIR__ . '/config/db_conn.php';

// Routing Logic
$requestUri = $_SERVER['REQUEST_URI'];
$apiBase = '/api/tms/v1';

// Normalize URI (remove query parameters and trailing slashes)
$path = parse_url($requestUri, PHP_URL_PATH);
$path = rtrim($path, '/');

// Route: Swagger Documentation
if ($path === '/api-docs') {
    header('Content-Type: text/html');
    include __DIR__ . '/swagger/index.html';
    exit;
}

if ($path === '/swagger.json') {
    header('Content-Type: application/json');
    include __DIR__ . '/swagger/swagger.json';
    exit;
}

header('Content-Type: application/json');

if (strpos($path, $apiBase) === 0 || $path === '' || $path === '/') {
    // Determine the specific resource requested
    $endpoint = (strpos($path, $apiBase) === 0) ? substr($path, strlen($apiBase)) : $path;
    $method = $_SERVER['REQUEST_METHOD'];

    // Route: Auth
    if (strpos($endpoint, '/auth') === 0) {
        require_once __DIR__ . '/routes/routes.auth.php';
        handleAuth($pdo, $method, $endpoint);
        exit;
    }

    // Route: Coach Center
    if (strpos($endpoint, '/coach-center') === 0) {
        require_once __DIR__ . '/routes/routes.coashcenter.php';
        handleCoachCenter($pdo, $method, $endpoint);
        exit;
    }

    // Route: Branch
    if (strpos($endpoint, '/branch') === 0) {
        require_once __DIR__ . '/routes/routes.branch.php';
        handleBranch($pdo, $method, $endpoint);
        exit;
    }

    // Route: Teacher
    if (strpos($endpoint, '/teacher') === 0) {
        require_once __DIR__ . '/routes/routes.teacher.php';
        handleTeacher($pdo, $method, $endpoint);
        exit;
    }

    // Route: Parent
    if (strpos($endpoint, '/parent') === 0) {
        require_once __DIR__ . '/routes/routes.parent.php';
        handleParent($pdo, $method, $endpoint);
        exit;
    }

    // Route: Student
    if (strpos($endpoint, '/student') === 0) {
        require_once __DIR__ . '/routes/routes.student.php';
        handleStudent($pdo, $method, $endpoint);
        exit;
    }

    // Route: Subject
    if (strpos($endpoint, '/subject') === 0) {
        require_once __DIR__ . '/routes/routes.subject.php';
        handleSubject($pdo, $method, $endpoint);
        exit;
    }

    // Route: Teacher-Subject Mapping
    if (strpos($endpoint, '/teacher-subject') === 0) {
        require_once __DIR__ . '/routes/routes.teacher_subject.php';
        handleTeacherSubject($pdo, $method, $endpoint);
        exit;
    }

    // Route: Student-Subject Mapping
    if (strpos($endpoint, '/student-subject') === 0) {
        require_once __DIR__ . '/routes/routes.student_subject.php';
        handleStudentSubject($pdo, $method, $endpoint);
        exit;
    }
    
    // Default success response for root or API base
    echo json_encode([
        "status" => "success",
        "message" => "TMS API v1 is active",
        "base_url" => $apiBase,
        "timestamp" => date('Y-m-d H:i:s'),
        "db_status" => isset($pdo) ? "Connected" : "Disconnected"
    ]);
} else {
    // Fallback for non-API routes
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => "Endpoint not found. Use $apiBase for API access.",
        "received_path" => $path
    ]);
}
