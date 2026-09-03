<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_projects_table($pdo);
ensure_table_columns($pdo, 'projects', [
    'description' => 'TEXT',
    'publication_date' => 'DATE',
    'pdf_file' => 'TEXT',
    'pdf_file_name' => 'TEXT',
    'cover_image' => 'TEXT',
    'caption' => 'TEXT',
    'allow_download' => 'BOOLEAN DEFAULT TRUE',
    'allow_print' => 'BOOLEAN DEFAULT TRUE',
    'viewer_enabled' => 'BOOLEAN DEFAULT TRUE',
]);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $publicationDate = trim((string)($_POST['publication_date'] ?? ''));
    $caption = trim((string)($_POST['caption'] ?? ''));
    $allowDownload = isset($_POST['allow_download']) ? 1 : 0;
    $allowPrint = isset($_POST['allow_print']) ? 1 : 0;
    $viewerEnabled = isset($_POST['viewer_enabled']) ? 1 : 0;
    $pdfPath = '';
    $pdfName = '';
    $coverPath = '';

    if ($title === '') {
        $error = 'Project title is required.';
    }

    if ($error === '') {
        $pdf = $_FILES['pdf_file'] ?? null;
        if (empty($pdf['name'])) {
            $error = 'Please upload the journal PDF.';
        } else {
            $pdfPath = safe_upload($pdf, ['allowed_mimes' => ['application/pdf'], 'max_size' => 50 * 1024 * 1024]);
            if ($pdfPath === false) {
                $error = 'The journal must be a PDF under 50MB.';
            } else {
                $pdfName = sanitize_original_filename($pdf['name']);
            }
        }
    }

    if ($error === '') {
        $cover = $_FILES['cover_image'] ?? null;
        if (!empty($cover['name'])) {
            $coverPath = safe_upload($cover, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($coverPath === false) {
                $error = 'The cover image upload failed. Use JPG, PNG, WEBP, or GIF under 8MB.';
            }
        }
    }

    if ($error === '') {
        try {
            $stmt = $pdo->prepare('INSERT INTO projects (title, description, publication_date, pdf_file, pdf_file_name, cover_image, caption, allow_download, allow_print, viewer_enabled) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $title,
                $description !== '' ? $description : null,
                $publicationDate !== '' ? $publicationDate : null,
                $pdfPath,
                $pdfName,
                $coverPath !== '' ? $coverPath : null,
                $caption !== '' ? $caption : null,
                $allowDownload,
                $allowPrint,
                $viewerEnabled,
            ]);
            $success = 'Project journal uploaded successfully.';
            $_POST = [];
        } catch (Throwable $e) {
            $error = 'Unable to save the project journal right now.';
        }
    }
}

$pageTitle = 'Add Project Journal';
$pageSubtitle = 'Upload a journal project for the public Projects page.';
$activeNav = 'projects';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/projects/index.php">' . admin_icon_html('chevron') . '<span>Back to Projects</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">
        <div class="field field--full">
          <label for="title">Title</label>
          <input id="title" name="title" value="<?php echo esc($_POST['title'] ?? ''); ?>" required>
        </div>

        <div class="field field--full">
          <label for="description">Description <span class="hint-badge">Optional</span></label>
          <textarea id="description" name="description"><?php echo esc($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="field">
          <label for="publication_date">Publication date <span class="hint-badge">Optional</span></label>
          <input id="publication_date" type="date" name="publication_date" value="<?php echo esc($_POST['publication_date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="caption">Cover caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($_POST['caption'] ?? ''); ?>">
        </div>

        <div class="upload-zone" id="projectDropzone">
          <strong><?php echo admin_icon_html('publications'); ?> Journal PDF</strong>
          <p id="projectDropzoneText">Drag and drop a PDF here, or click to choose one. Maximum size: 50MB.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Required</span><strong>PDF journal</strong></div>
            <div class="upload-tip"><span>Optional</span><strong>Cover image</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="pdf_file">Journal PDF</label>
          <input id="pdf_file" type="file" name="pdf_file" accept="application/pdf" required>
        </div>

        <div class="field">
          <label for="cover_image">Cover image <span class="hint-badge">Optional</span></label>
          <input id="cover_image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field field--full">
          <label class="checkbox-toggle"><input type="checkbox" name="viewer_enabled" value="1" checked><span>Show the embedded reader on the public Projects page</span></label>
          <label class="checkbox-toggle"><input type="checkbox" name="allow_download" value="1" checked><span>Allow visitors to download the PDF</span></label>
          <label class="checkbox-toggle"><input type="checkbox" name="allow_print" value="1" checked><span>Allow visitors to print the journal</span></label>
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/projects/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Publish project journal</span></button>
      </div>
    </form>
  </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var zone = document.getElementById('projectDropzone');
  var input = document.getElementById('pdf_file');
  var text = document.getElementById('projectDropzoneText');
  if (!zone || !input) return;
  zone.addEventListener('click', function () { input.click(); });
  ['dragenter', 'dragover'].forEach(function (eventName) {
    zone.addEventListener(eventName, function (event) { event.preventDefault(); zone.classList.add('is-dragging'); });
  });
  ['dragleave', 'drop'].forEach(function (eventName) {
    zone.addEventListener(eventName, function (event) { event.preventDefault(); zone.classList.remove('is-dragging'); });
  });
  zone.addEventListener('drop', function (event) {
    if (event.dataTransfer.files.length) {
      input.files = event.dataTransfer.files;
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });
  input.addEventListener('change', function () {
    if (input.files.length) text.textContent = 'Selected file: ' + input.files[0].name;
  });
});
</script>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
