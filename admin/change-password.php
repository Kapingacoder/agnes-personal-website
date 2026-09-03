<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

error_reporting(E_ALL);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $token = $_POST['_csrf'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid request.';
    } elseif ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'All fields are required.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } else {
        // Verify current password
        $username = $_SESSION['admin_user']['username'];
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        
        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $updateStmt->execute([$newPasswordHash, $row['id']]);
            
            $success = 'Password changed successfully. Please login with your new password.';
            
            // Logout user after password change for security
            logout_user();
            header('Location: /admin/login.php?password_changed=1');
            exit;
        }
    }
}

$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$pageTitle = 'Change Password';
$pageSubtitle = 'Update your admin account password for security.';
$activeNav = 'overview';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

  <?php if ($error): ?>
    <div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div>
  <?php endif; ?>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>Change Password</h3>
        <p>Enter your current password and choose a new secure password.</p>
      </div>
    </div>

    <form method="post" action="/admin/change-password.php" class="form">
      <?php echo csrf_field(); ?>
      
      <div class="field">
        <label for="current_password">Current Password</label>
        <input id="current_password" name="current_password" type="password" required autofocus>
        <p class="field__hint">Enter your current password to verify your identity.</p>
      </div>

      <div class="field">
        <label for="new_password">New Password</label>
        <input id="new_password" name="new_password" type="password" required minlength="8">
        <p class="field__hint">Password must be at least 8 characters long. Use a mix of letters, numbers, and symbols for better security.</p>
      </div>

      <div class="field">
        <label for="confirm_password">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password" required minlength="8">
        <p class="field__hint">Re-enter your new password to confirm it matches.</p>
      </div>

      <div class="form__actions">
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('shield'); ?><span>Change Password</span></button>
        <a class="btn btn-secondary" href="/admin/dashboard.php"><?php echo admin_icon_html('close'); ?><span>Cancel</span></a>
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>Password Security Tips</h3>
        <p>Follow these guidelines to keep your account secure.</p>
      </div>
    </div>

    <div class="security-tips">
      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Use Strong Passwords</h4>
          <p>Include uppercase and lowercase letters, numbers, and special characters.</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Avoid Common Patterns</h4>
          <p>Don't use birthdays, names, or common words like "password123".</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Unique Passwords</h4>
          <p>Use different passwords for different accounts and services.</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Regular Updates</h4>
          <p>Change your password periodically, especially if you suspect it may be compromised.</p>
        </div>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
