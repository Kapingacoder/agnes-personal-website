<?php
// Database configuration for Agnes personal website
// Update credentials as needed or load from environment in production
// Use environment variables in production. Falls back to local defaults for dev.
return [
  'host' => getenv('DB_HOST') ?: '127.0.0.1',
  'dbname' => getenv('DB_NAME') ?: 'agnes_personal_website',
  'user' => getenv('DB_USER') ?: 'agnes',
  'pass' => getenv('DB_PASS') ?: 'change_this_password',
  'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];
