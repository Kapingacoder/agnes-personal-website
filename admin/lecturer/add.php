<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'lecturers', [
    'caption'             => 'TEXT',
    'date'                => 'DATE',
    'primary_file'        => 'TEXT',
    'secondary_file'      => 'TEXT',
    'video_file'          => 'TEXT',
    'primary_file_name'   => 'TEXT',
    'secondary_file_name' => 'TEXT',
    'youtube_url'         => 'VARCHAR(1000) DEFAULT NULL',
]);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $caption     = trim((string)($_POST['caption'] ?? ''));
    $date        = trim((string)($_POST['date'] ?? ''));
    $youtube_url = trim((string)($_POST['youtube_url'] ?? ''));

    $imagePath         = '';
    $primaryFilePath   = '';
    $secondaryFilePath = '';
    $videoFilePath     = '';
    $primaryFileName   = '';
    $secondaryFileName = '';

    if ($title === '') {
        $error = 'Please provide the lecturer name/title.';
    } elseif ($youtube_url !== '' && !youtube_embed_url($youtube_url)) {
        $error = 'The YouTube URL is not valid. Please use a standard youtube.com or youtu.be link.';
    }

    if ($error === '') {
        $image = $_FILES['image'] ?? null;
        if (!empty($image['name'])) {
            $imagePath = safe_upload($image, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($imagePath === false) {
                $error = 'The image upload failed. Please use JPG, PNG, WEBP, or GIF under 8MB.';
                $imagePath = '';
            }
        }
    }
    if ($error === '') {
        $primary = $_FILES['primary_file'] ?? null;
        if (!empty($primary['name'])) {
            $primaryFilePath = safe_upload($primary, ['allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()), 'max_size' => 25 * 1024 * 1024]);
            if ($primaryFilePath === false) {
                $error = 'The primary resource upload failed. Allowed: PDF, DOC, DOCX, PPT, PPTX, images up to 25MB.';
                $primaryFilePath = '';
            } else {
                $primaryFileName = sanitize_original_filename($primary['name']);
            }
        }
    }
    if ($error === '') {
        $secondary = $_FILES['secondary_file'] ?? null;
        if (!empty($secondary['name'])) {
            $secondaryFilePath = safe_upload($secondary, ['allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()), 'max_size' => 25 * 1024 * 1024]);
            if ($secondaryFilePath === false) {
                $error = 'The secondary resource upload failed.';
                $secondaryFilePath = '';
            } else {
                $secondaryFileName = sanitize_original_filename($secondary['name']);
            }
        }
    }
    if ($error === '') {
        $video = $_FILES['video_file'] ?? null;
        if (!empty($video['name'])) {
            $videoFilePath = safe_upload($video, ['allowed_mimes' => allowed_video_mimes(), 'max_size' => 120 * 1024 * 1024]);
            if ($videoFilePath === false) {
                $error = 'The lecturer video upload failed. Please use MP4, WEBM, or MOV under 120MB.';
                $videoFilePath = '';
            }
        }
    }

    if ($error === '') {
        try {
            $stmt = $pdo->prepare('INSERT INTO lecturers (title, description, image, caption, date, primary_file, secondary_file, video_file, primary_file_name, secondary_file_name, youtube_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $title,
                $description !== '' ? $description : null,
                $imagePath !== '' ? $imagePath : null,
                $caption !== '' ? $caption : null,
                $date !== '' ? $date : null,
                $primaryFilePath !== '' ? $primaryFilePath : null,
                $secondaryFilePath !== '' ? $secondaryFilePath : null,
                $videoFilePath !== '' ? $videoFilePath : null,
                $primaryFileName !== '' ? $primaryFileName : null,
                $secondaryFileName !== '' ? $secondaryFileName : null,
                $youtube_url !== '' ? $youtube_url : null,
            ]);
            $success = 'Lecturer profile created successfully.';
            $_POST = [];
        } catch (Throwable $e) {
            $error = 'Unable to save the lecturer profile right now. DB error: ' . $e->getMessage();
        }
    }
}

$pageTitle         = 'Add Lecturer';
$pageSubtitle      = 'Create a lecturer profile. Only the fields and files you fill in will appear on the public site.';
$activeNav         = 'lecturers';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/lecturer/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">
      <div class="form-grid">

        <div class="field field--full">
          <label for="title">Lecturer name</label>
          <input id="title" name="title" value="<?php echo esc($_POST['title'] ?? ''); ?>" required placeholder="e.g. Dr. John Doe">
        </div>

        <div class="field field--full">
          <label for="description">Biography / profile text <span class="hint-badge">Optional</span></label>
          <textarea id="description" name="description" placeholder="A short bio or profile description..."><?php echo esc($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="field">
          <label for="date">Date <span class="hint-badge">Optional</span></label>
          <input id="date" type="date" name="date" value="<?php echo esc($_POST['date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="caption">Media caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($_POST['caption'] ?? ''); ?>" placeholder="Short caption shown below media">
        </div>

        <div class="field field--full">
          <label for="youtube_url">YouTube Video URL <span class="hint-badge">Optional</span></label>
          <input id="youtube_url" name="youtube_url" type="url" value="<?php echo esc($_POST['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
          <span class="field__hint">Paste a YouTube link — the video will be embedded on the public Lecturer page.</span>
        </div>

        <div class="upload-zone field--full">
          <strong><?php echo admin_icon_html('image'); ?> Featured media &amp; files</strong>
          <p>Add a cover image, supporting documents, and a local video. Anything left blank stays hidden on the public page.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Cover image</span><strong>JPG, PNG, WEBP, GIF</strong></div>
            <div class="upload-tip"><span>Documents</span><strong>PDF, DOC, DOCX</strong></div>
            <div class="upload-tip"><span>Presentations</span><strong>PPT, PPTX</strong></div>
            <div class="upload-tip"><span>Local video</span><strong>MP4, WEBM, MOV</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="image">Cover image <span class="hint-badge">Optional</span></label>
          <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="video_file">Local video <span class="hint-badge">Optional</span></label>
          <input id="video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        </div>

        <div class="field">
          <label for="primary_file">Primary resource file <span class="hint-badge">Optional</span></label>
          <input id="primary_file" type="file" name="primary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="secondary_file">Secondary resource file <span class="hint-badge">Optional</span></label>
          <input id="secondary_file" type="file" name="secondary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/lecturer/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save lecturer</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
