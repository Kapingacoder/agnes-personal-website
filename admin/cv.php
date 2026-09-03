<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_admins_table($pdo);
ensure_table_columns($pdo, 'admins', [
    'cv_file' => 'VARCHAR(255)'
]);

$success = '';
$error = '';
$cv_path = '';

// Get current admin's CV
$admin_id = $_SESSION['admin_user']['id'];
$stmt = $pdo->prepare('SELECT cv_file FROM admins WHERE id = ?');
$stmt->execute([$admin_id]);
$admin_data = $stmt->fetch();
$cv_path = $admin_data['cv_file'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cv = $_FILES['cv_file'] ?? null;
    $token = $_POST['_csrf'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid request.';
    } elseif (empty($cv['name'])) {
        $error = 'Please select a CV file to upload.';
    } else {
        $cvPath = safe_upload($cv, [
            'allowed_mimes' => ['application/pdf'],
            'max_size' => 10 * 1024 * 1024, // 10MB
        ]);

        if ($cvPath === false) {
            $error = 'CV upload failed. Please upload a PDF file under 10MB.';
        } else {
            try {
                $updateStmt = $pdo->prepare('UPDATE admins SET cv_file = ? WHERE id = ?');
                $updateStmt->execute([$cvPath, $admin_id]);
                $cv_path = $cvPath;
                $success = 'CV updated successfully. The download button on the homepage will now use your new CV.';
            } catch (Throwable $e) {
                $error = 'Unable to update CV. Database error: ' . $e->getMessage();
            }
        }
    }
}

$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$pageTitle = 'CV Management';
$pageSubtitle = 'Manage your CV that appears on the homepage download button.';
$activeNav = 'overview';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/dashboard.php">' . admin_icon_html('chevron') . '<span>Back to Dashboard</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>CV Management</h3>
        <p>Upload your CV (PDF only) that will be used for the download button on the homepage.</p>
      </div>
    </div>

    <div class="cv-status">
      <?php if ($cv_path): ?>
        <div class="cv-status__current">
          <div class="cv-status__icon"><?php echo admin_icon_html('check'); ?></div>
          <div class="cv-status__content">
            <h4>Current CV</h4>
            <p>Your CV is currently uploaded and active.</p>
            <div class="cv-status__actions">
              <a class="btn btn-secondary btn-sm" href="<?php echo esc($cv_path); ?>" target="_blank" rel="noopener"><?php echo admin_icon_html('view'); ?><span>View Current CV</span></a>
              <a class="btn btn-primary btn-sm" href="<?php echo esc($cv_path); ?>" download="Agnes-Kapinga-CV.pdf"><?php echo admin_icon_html('external'); ?><span>Download Current CV</span></a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="cv-status__missing">
          <div class="cv-status__icon"><?php echo admin_icon_html('inbox'); ?></div>
          <div class="cv-status__content">
            <h4>No CV Uploaded</h4>
            <p>You haven't uploaded a CV yet. The homepage download button will use a placeholder link.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="form">
      <?php echo csrf_field(); ?>
      
      <div class="field field--full">
        <label for="cv_file">Upload CV (PDF) *</label>
        <input id="cv_file" type="file" name="cv_file" accept="application/pdf" required>
        <p class="field__hint">Accepted format: PDF only. Maximum size: 10MB. This will replace your current CV if one exists.</p>
      </div>

      <div class="form__actions">
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Update CV</span></button>
      </div>
    </form>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>CV Guidelines</h3>
        <p>Best practices for your CV upload.</p>
      </div>
    </div>

    <div class="security-tips">
      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>PDF Format Only</h4>
          <p>Upload your CV in PDF format to ensure it displays correctly across all devices and browsers.</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>File Size</h4>
          <p>Keep your CV under 10MB for faster downloads and better user experience.</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Regular Updates</h4>
          <p>Update your CV regularly to keep your information current and relevant.</p>
        </div>
      </div>

      <div class="security-tip">
        <div class="security-tip__icon"><?php echo admin_icon_html('check'); ?></div>
        <div class="security-tip__content">
          <h4>Professional Content</h4>
          <p>Ensure your CV contains accurate and professional information about your qualifications and experience.</p>
        </div>
      </div>
    </div>
  </section>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
