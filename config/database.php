<?php
// Database configuration for Agnes personal website
// Defaults are set for MySQL local development.

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
            $_ENV[trim($key)] = trim($value);
        }
    }
}

return [
  'driver' => getenv('DB_DRIVER') ?: 'mysql',
  'host' => getenv('DB_HOST') ?: '127.0.0.1',
  'port' => getenv('DB_PORT') ?: '3306',
  'dbname' => getenv('DB_NAME') ?: 'agnes_personal_website',
  'user' => getenv('DB_USER') ?: 'root',
  'pass' => getenv('DB_PASS') ?: '',
  'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
