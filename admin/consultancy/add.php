<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'consultancy_videos', [
    'caption' => 'TEXT',
    'date' => 'DATE',
    'resource_file' => 'TEXT',
    'supporting_file' => 'TEXT',
    'video_file' => 'TEXT',
    'resource_file_name' => 'TEXT',
    'supporting_file_name' => 'TEXT',
]);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $youtube_url = trim((string)($_POST['youtube_url'] ?? ''));
    $caption = trim((string)($_POST['caption'] ?? ''));
    $date = trim((string)($_POST['date'] ?? ''));

    $thumbnailPath = '';
    $resourceFilePath = '';
    $supportingFilePath = '';
    $videoFilePath = '';
    $resourceFileName = '';
    $supportingFileName = '';

    if ($title === '') {
        $error = 'Please provide a title for the consultancy item.';
    } else {
        $thumb = $_FILES['thumbnail'] ?? null;
        if (!empty($thumb['name'])) {
            $thumbnailPath = safe_upload($thumb, [
                'allowed_mimes' => allowed_image_mimes(),
                'max_size' => 8 * 1024 * 1024,
            ]);
            if ($thumbnailPath === false) {
                $error = 'The thumbnail upload failed. Please use JPG, PNG, WEBP, or GIF under 8MB.';
            }
        }

        if ($error === '') {
            $resource = $_FILES['resource_file'] ?? null;
            if (!empty($resource['name'])) {
                $resourceFilePath = safe_upload($resource, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size' => 25 * 1024 * 1024,
                ]);
                if ($resourceFilePath === false) {
                    $error = 'The main consultancy resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $resourceFileName = sanitize_original_filename($resource['name']);
                }
            }
        }

        if ($error === '') {
            $support = $_FILES['supporting_file'] ?? null;
            if (!empty($support['name'])) {
                $supportingFilePath = safe_upload($support, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size' => 25 * 1024 * 1024,
                ]);
                if ($supportingFilePath === false) {
                    $error = 'The supporting resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $supportingFileName = sanitize_original_filename($support['name']);
                }
            }
        }

        if ($error === '') {
            $video = $_FILES['video_file'] ?? null;
            if (!empty($video['name'])) {
                $videoFilePath = safe_upload($video, [
                    'allowed_mimes' => allowed_video_mimes(),
                    'max_size' => 120 * 1024 * 1024,
                ]);
                if ($videoFilePath === false) {
                    $error = 'The consultancy video upload failed. Please use MP4, WEBM, or MOV under 120MB.';
                }
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare('INSERT INTO consultancy_videos (title, description, youtube_url, thumbnail, caption, date, resource_file, supporting_file, video_file, resource_file_name, supporting_file_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $title,
                    $description !== '' ? $description : null,
                    $youtube_url !== '' ? $youtube_url : null,
                    $thumbnailPath ?: null,
                    $caption !== '' ? $caption : null,
                    $date !== '' ? $date : null,
                    $resourceFilePath !== '' ? $resourceFilePath : null,
                    $supportingFilePath !== '' ? $supportingFilePath : null,
                    $videoFilePath !== '' ? $videoFilePath : null,
                    $resourceFileName !== '' ? $resourceFileName : null,
                    $supportingFileName !== '' ? $supportingFileName : null,
                ]);
                $success = 'Consultancy content added successfully.';
                $_POST = [];
            } catch (Throwable $e) {
                $error = 'Unable to save the consultancy content right now. DB error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Add Consultancy Item';
$pageSubtitle = 'Upload consultancy media and resources. Only filled fields and uploads appear on the public site.';
$activeNav = 'consultancy';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/consultancy/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
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
          <label for="date">Date <span class="hint-badge">Optional</span></label>
          <input id="date" type="date" name="date" value="<?php echo esc($_POST['date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="youtube_url">YouTube URL <span class="hint-badge">Optional</span></label>
          <input id="youtube_url" type="url" name="youtube_url" value="<?php echo esc($_POST['youtube_url'] ?? ''); ?>">
        </div>

        <div class="field field--full">
          <label for="caption">Media caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($_POST['caption'] ?? ''); ?>">
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('consultancy'); ?> Consultancy media &amp; files</strong>
          <p>Upload a thumbnail, local video, documents, presentations, or images. You can also add a YouTube link. Blank items remain hidden on the public site.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Thumbnail</span><strong>JPG, PNG, WEBP, GIF</strong></div>
            <div class="upload-tip"><span>Video</span><strong>MP4, WEBM, MOV</strong></div>
            <div class="upload-tip"><span>Documents</span><strong>PDF, DOC, DOCX</strong></div>
            <div class="upload-tip"><span>Slides</span><strong>PPT, PPTX</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="thumbnail">Upload thumbnail <span class="hint-badge">Optional</span></label>
          <input id="thumbnail" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="video_file">Upload local video <span class="hint-badge">Optional</span></label>
          <input id="video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        </div>

        <div class="field">
          <label for="resource_file">Main resource file <span class="hint-badge">Optional</span></label>
          <input id="resource_file" type="file" name="resource_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="supporting_file">Supporting resource file <span class="hint-badge">Optional</span></label>
          <input id="supporting_file" type="file" name="supporting_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/consultancy/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save consultancy item</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
