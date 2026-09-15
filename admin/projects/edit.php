<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_projects_table($pdo);
ensure_table_columns($pdo, 'projects', [
    'description'      => 'TEXT',
    'publication_date' => 'DATE',
    'pdf_file'         => 'TEXT',
    'pdf_file_name'    => 'TEXT',
    'cover_image'      => 'TEXT',
    'caption'          => 'TEXT',
    'allow_download'   => 'BOOLEAN DEFAULT TRUE',
    'allow_print'      => 'BOOLEAN DEFAULT TRUE',
    'viewer_enabled'   => 'BOOLEAN DEFAULT TRUE',
]);

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: /admin/projects/index.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header('Location: /admin/projects/index.php?error=invalid');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title           = trim((string)($_POST['title'] ?? ''));
    $description     = trim((string)($_POST['description'] ?? ''));
    $publicationDate = trim((string)($_POST['publication_date'] ?? ''));
    $caption         = trim((string)($_POST['caption'] ?? ''));
    $allowDownload   = isset($_POST['allow_download']) ? 1 : 0;
    $allowPrint      = isset($_POST['allow_print']) ? 1 : 0;
    $viewerEnabled   = isset($_POST['viewer_enabled']) ? 1 : 0;

    if ($title === '') {
        $error = 'Project title is required.';
    }

    $pdfPath   = $record['pdf_file'] ?? '';
    $pdfName   = $record['pdf_file_name'] ?? '';
    $coverPath = $record['cover_image'] ?? '';

    // Handle new PDF upload (optional on edit)
    if ($error === '') {
        $pdf = $_FILES['pdf_file'] ?? null;
        if (!empty($pdf['name'])) {
            $newPdf = safe_upload($pdf, ['allowed_mimes' => ['application/pdf'], 'max_size' => 50 * 1024 * 1024]);
            if ($newPdf === false) {
                $error = 'The journal must be a PDF under 50MB.';
            } else {
                $pdfPath = $newPdf;
                $pdfName = sanitize_original_filename($pdf['name']);
            }
        }
    }

    // Handle new cover image
    if ($error === '') {
        $cover = $_FILES['cover_image'] ?? null;
        if (!empty($cover['name'])) {
            $newCover = safe_upload($cover, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($newCover === false) {
                $error = 'The cover image upload failed. Use JPG, PNG, WEBP, or GIF under 8MB.';
            } else {
                $coverPath = $newCover;
            }
        }
    }

    if ($error === '') {
        try {
            $stmt = $pdo->prepare('UPDATE projects SET title=?, description=?, publication_date=?, pdf_file=?, pdf_file_name=?, cover_image=?, caption=?, allow_download=?, allow_print=?, viewer_enabled=? WHERE id=?');
            $stmt->execute([
                $title,
                $description !== '' ? $description : null,
                $publicationDate !== '' ? $publicationDate : null,
                $pdfPath !== '' ? $pdfPath : null,
                $pdfName !== '' ? $pdfName : null,
                $coverPath !== '' ? $coverPath : null,
                $caption !== '' ? $caption : null,
                $allowDownload,
                $allowPrint,
                $viewerEnabled,
                $id,
            ]);
            $success = 'Project journal updated successfully.';
            $stmt2 = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
            $stmt2->execute([$id]);
            $record = $stmt2->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $error = 'Unable to update the project journal. DB error: ' . $e->getMessage();
        }
    }
}

$pageTitle         = 'Edit Project Journal';
$pageSubtitle      = 'Update this project journal.';
$activeNav         = 'projects';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/projects/index.php">' . admin_icon_html('chevron') . '<span>Back to Projects</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <div class="form-grid">

        <div class="field field--full">
          <label for="title">Title</label>
          <input id="title" name="title" value="<?php echo esc($record['title'] ?? ''); ?>" required>
        </div>

        <div class="field field--full">
          <label for="description">Description <span class="hint-badge">Optional</span></label>
          <textarea id="description" name="description"><?php echo esc($record['description'] ?? ''); ?></textarea>
        </div>

        <div class="field">
          <label for="publication_date">Publication date <span class="hint-badge">Optional</span></label>
          <input id="publication_date" type="date" name="publication_date" value="<?php echo esc($record['publication_date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="caption">Cover caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($record['caption'] ?? ''); ?>">
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('publications'); ?> Journal PDF &amp; Cover</strong>
          <p>Upload a replacement file. Leave blank to keep the existing file.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Current PDF</span><strong><?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['pdf_file_name'] ?? null, $record['pdf_file'] ?? ''), 28)); ?></strong></div>
            <div class="upload-tip"><span>Cover image</span><strong><?php echo has_text($record['cover_image'] ?? '') ? 'On file' : 'None'; ?></strong></div>
          </div>
        </div>

        <div class="field">
          <label for="pdf_file">
            Replace journal PDF <span class="hint-badge">Optional</span>
            <?php if (has_text($record['pdf_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['pdf_file_name'] ?? null, $record['pdf_file']), 30)); ?></span>
            <?php endif; ?>
          </label>
          <input id="pdf_file" type="file" name="pdf_file" accept="application/pdf">
        </div>

        <div class="field">
          <label for="cover_image">
            Replace cover image <span class="hint-badge">Optional</span>
            <?php if (has_text($record['cover_image'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Image on file</span>
            <?php endif; ?>
          </label>
          <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field field--full">
          <label class="checkbox-toggle">
            <input type="checkbox" name="viewer_enabled" value="1" <?php echo (int)($record['viewer_enabled'] ?? 1) === 1 ? 'checked' : ''; ?>>
            <span>Show the embedded reader on the public Projects page</span>
          </label>
          <label class="checkbox-toggle">
            <input type="checkbox" name="allow_download" value="1" <?php echo (int)($record['allow_download'] ?? 1) === 1 ? 'checked' : ''; ?>>
            <span>Allow visitors to download the PDF</span>
          </label>
          <label class="checkbox-toggle">
            <input type="checkbox" name="allow_print" value="1" <?php echo (int)($record['allow_print'] ?? 1) === 1 ? 'checked' : ''; ?>>
            <span>Allow visitors to print the journal</span>
          </label>
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/projects/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save changes</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
