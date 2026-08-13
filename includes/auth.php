<?php
// Authentication helpers using PHP sessions and the `admins` table.
session_start();

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
