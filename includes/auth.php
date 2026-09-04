<?php
// Authentication helpers using PHP sessions and the `admins` table.
// Harden session cookie settings before starting session
if(session_status() !== PHP_SESSION_ACTIVE){
  // Suppress session warnings and handle errors gracefully
  error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
  
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
  $cookieParams = session_get_cookie_params();
  session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'] ?? 0,
    'path' => $cookieParams['path'] ?? '/',
    'domain' => $cookieParams['domain'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  
  @session_start();
  
  // Restore error reporting
  error_reporting(E_ALL);
}

require_once __DIR__ . '/db.php';

function is_logged_in(): bool {
  return !empty($_SESSION['admin_user']);
}

function require_auth(){
  if(!is_logged_in()){
    // Redirect to admin login
    header('Location: /admin/login.php');
    exit;
  }
}

function login_user(string $username, string $password): bool {
  global $pdo;
  $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
  $stmt->execute([$username]);
  $row = $stmt->fetch();
  if($row && password_verify($password, $row['password_hash'])){
    // Regenerate session id
    session_regenerate_id(true);
    $_SESSION['admin_user'] = ['id' => $row['id'], 'username' => $row['username']];
    // Track last activity for session timeout (30 minutes)
    $_SESSION['last_activity'] = time();
    return true;
  }
  return false;
}

function logout_user(){
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
  }
  session_destroy();
}

// Optional check for session timeout
function session_is_expired($timeout = 1800){
    if(empty($_SESSION['last_activity'])) return false;
    return (time() - $_SESSION['last_activity']) > $timeout;
}

// Extend session on each request
if(!empty($_SESSION['admin_user'])){
    if(session_is_expired()){
        logout_user();
    } else {
        $_SESSION['last_activity'] = time();
    }
}
