<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if(is_logged_in()){
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

  // verify CSRF token
  $token = $_POST['_csrf'] ?? '';
  if(!verify_csrf($token)){
    $error = 'Invalid request.';
  } else {

    if($username === '' || $password === ''){
        $error = 'Missing username or password.';
    } else {
        if(login_user($username, $password)){
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}

// Present a standalone login page (also used by modal form)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-primary">
  <div class="container" style="padding:60px 20px;max-width:480px">
    <div class="card" style="background:var(--bg-secondary);padding:28px;border-radius:12px">
      <h2 style="color:var(--white)">Administrator Login</h2>
      <?php if($error): ?><div style="color:#ff8b8b;margin-bottom:12px"><?php echo esc($error); ?></div><?php endif; ?>
      <form method="post" action="/admin/login.php">
        <label style="display:block;margin-bottom:8px;color:var(--text-secondary)">Username
          <input name="username" required autofocus style="width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)">
        </label>
        <label style="display:block;margin-bottom:12px;color:var(--text-secondary)">Password
          <input name="password" type="password" required style="width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)">
        </label>
        <div style="display:flex;gap:10px;align-items:center">
          <button class="btn btn-primary" type="submit">Login</button>
          <a href="/index.php" class="btn btn-secondary">Back</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<?php
// Simple login placeholder
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Login</title></head>
<body>
<h1>Admin Login</h1>
<form method="post" action="dashboard.php">
  <label>Username: <input name="user"></label>
  <label>Password: <input type="password" name="pass"></label>
  <button type="submit">Login</button>
</form>
</body>
</html>
