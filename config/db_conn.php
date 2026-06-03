<?php

/**
 * Database Connection Configuration
 * Uses environment variables from the .env file in the root directory.
 */

function loadEnv($path)
{
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Set environment variables
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    return true;
}

// Load the .env file (check tms_be folder first, then root)
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    $envPath = __DIR__ . '/../../.env';
}

if (!loadEnv($envPath)) {
    die("Error: Could not find or load .env file at $envPath");
}

// Database Connection Parameters
$host     = getenv('DB_HOST') ?: '127.0.0.1';
$db       = getenv('DB_DATABASE');
$user     = getenv('DB_USERNAME');
$pass     = getenv('DB_PASSWORD');
$port     = getenv('DB_PORT') ?: '3306';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, you would want to log this and show a generic message
    die("Database connection failed: " . $e->getMessage());
}
