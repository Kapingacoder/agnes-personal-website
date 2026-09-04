<?php
// Disable error display for production, enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Custom error handler to log errors without displaying them
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[$errno] $errstr in $errfile on line $errline");
    return true; // Don't display errors
});

// PDO helper - reusable connection
$config = require __DIR__ . '/../config/database.php';

try {
    $driver = strtolower((string)($config['driver'] ?? 'mysql'));

    if ($driver === 'pgsql') {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $config['host'],
            $config['port'] ?? '5432',
            $config['dbname']
        );
    } else {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'] ?? '3306',
            $config['dbname'],
            $config['charset'] ?? 'utf8mb4'
        );
    }

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Log to a temp file for local debugging
    $msg = $e->getMessage();
    error_log("DB_CONN_ERROR: " . $msg . "\n", 3, sys_get_temp_dir() . '/db_error.log');
    http_response_code(500);
    // In development / local CLI server show details to help debugging.
    if (getenv('APP_ENV') === 'development' || PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli') {
        echo 'Database connection error: ' . htmlspecialchars($msg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    } else {
        echo 'Database connection error';
    }
    exit;
}

// Usage: require 'includes/db.php'; then use $pdo with prepared statements
