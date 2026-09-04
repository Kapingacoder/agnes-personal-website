<?php
// Disable error display for production, enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: /admin/dashboard.php');
    exit;
}

error_reporting(E_ALL);
$error = '';
$success = '';

if (isset($_GET['password_changed']) && $_GET['password_changed'] === '1') {
    $success = 'Password changed successfully. Please login with your new password.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $token = $_POST['_csrf'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid request.';
    } elseif ($username === '' || $password === '') {
        $error = 'Missing username or password.';
    } else {
        if (login_user($username, $password)) {
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login &middot; Agnes Admin</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <meta name="color-scheme" content="dark">
</head>
<body class="admin-body">
  <div class="admin-auth">
    <div class="admin-auth__card">
      <div class="admin-auth__brand">
        <span class="admin-brand__mark"><?php echo admin_icon_html('shield'); ?></span>
        <span class="admin-brand__text">Agnes Admin</span>
      </div>
      <h1 class="admin-auth__title">Administrator Login</h1>
      <p class="admin-auth__subtitle">Sign in to manage lecturers, publications, consultancy content, and messages.</p>

      <?php if ($error): ?>
        <div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div>
      <?php endif; ?>

      <form method="post" action="/admin/login.php">
        <?php echo csrf_field(); ?>
        <div class="field" style="margin-bottom:16px">
          <label for="username">Username</label>
          <input id="username" name="username" required autofocus>
        </div>
        <div class="field" style="margin-bottom:8px">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required>
        </div>
        <div class="admin-auth__actions">
          <button class="btn btn-primary btn-block" type="submit"><?php echo admin_icon_html('shield'); ?><span>Login</span></button>
        </div>
        <div class="admin-auth__actions">
          <a class="btn btn-ghost btn-block" href="/index.php"><?php echo admin_icon_html('chevron'); ?><span>Back to site</span></a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
