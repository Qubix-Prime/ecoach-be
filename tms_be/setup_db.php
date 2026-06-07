<?php
// setup_db.php
require_once __DIR__ . '/config/db_conn.php';

try {
    // 1. Create table MSTR_USER if not exists
    $sql = "CREATE TABLE IF NOT EXISTS MSTR_USER (
        USER_ID INT AUTO_INCREMENT PRIMARY KEY,
        USERNAME VARCHAR(100) NOT NULL UNIQUE,
        PASSWORD VARCHAR(255) NOT NULL,
        ROLE VARCHAR(50) NOT NULL,
        REF_ID VARCHAR(100) NULL,
        EMAIL VARCHAR(150) NOT NULL UNIQUE,
        NAME VARCHAR(150) NOT NULL,
        CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Table MSTR_USER checked/created successfully.\n";

    // 2. Insert admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM MSTR_USER WHERE USERNAME = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES ('admin', ?, 'Admin', NULL, 'admin@aimtech.com', 'Rahul Sharma')");
        $stmt->execute([password_hash('admin@123', PASSWORD_DEFAULT)]);
        echo "Admin user seeded.\n";
    }

    // 3. Insert teacher if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM MSTR_USER WHERE USERNAME = 'teacher'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES ('teacher', ?, 'Teacher', 'T001', 'rajesh@example.com', 'Dr. Rajesh Kumar')");
        $stmt->execute([password_hash('teach@123', PASSWORD_DEFAULT)]);
        echo "Teacher user seeded.\n";
    }

    // 4. Insert student if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM MSTR_USER WHERE USERNAME = 'student'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES ('student', ?, 'Student', 'S001', 'amit@student.com', 'Amit Sharma')");
        $stmt->execute([password_hash('student@123', PASSWORD_DEFAULT)]);
        echo "Student user seeded.\n";
    }

    // 5. Insert parent if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM MSTR_USER WHERE USERNAME = 'parent'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO MSTR_USER (USERNAME, PASSWORD, ROLE, REF_ID, EMAIL, NAME) VALUES ('parent', ?, 'Parent', 'P001', 'suresh@example.com', 'Mr. Suresh Sharma')");
        $stmt->execute([password_hash('parent@123', PASSWORD_DEFAULT)]);
        echo "Parent user seeded.\n";
    }

    echo "Database setup script execution completed successfully!\n";

} catch (Exception $e) {
    echo "Database setup error: " . $e->getMessage() . "\n";
    exit(1);
}
