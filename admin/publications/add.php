<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'publications', [
    'caption' => 'TEXT',
    'pdf_file_name' => 'TEXT',
]);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $authors = trim((string)($_POST['authors'] ?? ''));
    $journal = trim((string)($_POST['journal'] ?? ''));
    $year = trim((string)($_POST['year'] ?? ''));
    $abstract = trim((string)($_POST['abstract'] ?? ''));
    $doi = trim((string)($_POST['doi'] ?? ''));
    $publication_url = trim((string)($_POST['publication_url'] ?? ''));
    $caption = trim((string)($_POST['caption'] ?? ''));

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $pdfPath = '';
        $imagePath = '';
        $pdfFileName = '';

        $pdfFile = $_FILES['pdf_file'] ?? null;
        if (!empty($pdfFile['name'])) {
            $pdfPath = safe_upload($pdfFile, ['allowed_mimes' => allowed_document_mimes(), 'max_size' => 25 * 1024 * 1024]);
            if ($pdfPath === false) {
                $error = 'The document upload failed. Please use a PDF, DOC, DOCX, PPT, or PPTX under 25MB.';
            } else {
                $pdfFileName = sanitize_original_filename($pdfFile['name']);
            }
        }

        if ($error === '') {
            $imageFile = $_FILES['image'] ?? null;
            if (!empty($imageFile['name'])) {
                $imagePath = safe_upload($imageFile, ['allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'], 'max_size' => 8 * 1024 * 1024]);
                if ($imagePath === false) {
                    $error = 'The image upload failed. Please use JPG, PNG, or WEBP under 8MB.';
                }
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare('INSERT INTO publications (title, authors, journal, year, abstract, doi, publication_url, pdf_file, image, caption, pdf_file_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $title,
                    $authors,
                    $journal !== '' ? $journal : null,
                    $year !== '' ? (int)$year : null,
                    $abstract,
                    $doi !== '' ? $doi : null,
                    $publication_url !== '' ? $publication_url : null,
                    $pdfPath !== '' ? $pdfPath : null,
                    $imagePath !== '' ? $imagePath : null,
                    $caption !== '' ? $caption : null,
                    $pdfFileName !== '' ? $pdfFileName : null,
                ]);
                $success = 'Publication saved successfully.';
                $_POST = [];
            } catch (Throwable $e) {
                $error = 'Unable to save the publication right now. DB error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Add Publication';
$pageSubtitle = 'Add a research item. Only the information you provide will be displayed on the public site.';
$activeNav = 'publications';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/publications/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
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

        <div class="field">
          <label for="authors">Authors <span class="hint-badge">Optional</span></label>
          <input id="authors" name="authors" value="<?php echo esc($_POST['authors'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="journal">Journal / outlet <span class="hint-badge">Optional</span></label>
          <input id="journal" name="journal" value="<?php echo esc($_POST['journal'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="year">Year <span class="hint-badge">Optional</span></label>
          <input id="year" type="number" name="year" min="1900" max="2100" value="<?php echo esc($_POST['year'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="doi">DOI <span class="hint-badge">Optional</span></label>
          <input id="doi" name="doi" value="<?php echo esc($_POST['doi'] ?? ''); ?>">
        </div>

        <div class="field field--full">
          <label for="publication_url">Publication URL <span class="hint-badge">Optional</span></label>
          <input id="publication_url" name="publication_url" type="url" value="<?php echo esc($_POST['publication_url'] ?? ''); ?>">
        </div>

        <div class="field field--full">
          <label for="abstract">Abstract <span class="hint-badge">Optional</span></label>
          <textarea id="abstract" name="abstract"><?php echo esc($_POST['abstract'] ?? ''); ?></textarea>
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('publications'); ?> Attachments</strong>
          <p>Attach a document plus an optional cover image. Only uploaded items appear on the public site.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Document</span><strong>PDF, DOC, DOCX, PPT, PPTX</strong></div>
            <div class="upload-tip"><span>Cover image</span><strong>JPG, PNG, WEBP</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="pdf_file">Upload document <span class="hint-badge">Optional</span></label>
          <input id="pdf_file" type="file" name="pdf_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation">
        </div>

        <div class="field">
          <label for="image">Upload image <span class="hint-badge">Optional</span></label>
          <input id="image" type="file" name="image" accept="image/*">
        </div>

        <div class="field field--full">
          <label for="caption">Caption under the media <span class="hint-badge">Optional</span></label>
          <textarea id="caption" name="caption"><?php echo esc($_POST['caption'] ?? ''); ?></textarea>
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/publications/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save publication</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
