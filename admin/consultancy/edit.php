<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'consultancy_videos', [
    'caption'              => 'TEXT',
    'date'                 => 'DATE',
    'resource_file'        => 'TEXT',
    'supporting_file'      => 'TEXT',
    'video_file'           => 'TEXT',
    'resource_file_name'   => 'TEXT',
    'supporting_file_name' => 'TEXT',
    'youtube_image_url'    => 'VARCHAR(1000) DEFAULT NULL',
]);

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: /admin/consultancy/index.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM consultancy_videos WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header('Location: /admin/consultancy/index.php?error=invalid');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim((string)($_POST['title'] ?? ''));
    $description  = trim((string)($_POST['description'] ?? ''));
    $youtube_url  = trim((string)($_POST['youtube_url'] ?? ''));
    $youtube_image_url = trim((string)($_POST['youtube_image_url'] ?? ''));
    $caption      = trim((string)($_POST['caption'] ?? ''));
    $date         = trim((string)($_POST['date'] ?? ''));

    if ($title === '') {
        $error = 'Please provide a title for the consultancy item.';
    } elseif ($youtube_image_url !== '' && !youtube_embed_url($youtube_image_url)) {
        $error = 'The YouTube image URL is not valid. Please use a standard youtube.com or youtu.be link.';
    } else {
        $thumbnailPath     = $record['thumbnail'] ?? '';
        $resourceFilePath  = $record['resource_file'] ?? '';
        $supportingFilePath = $record['supporting_file'] ?? '';
        $videoFilePath     = $record['video_file'] ?? '';
        $resourceFileName  = $record['resource_file_name'] ?? '';
        $supportingFileName = $record['supporting_file_name'] ?? '';

        // Thumbnail
        $thumb = $_FILES['thumbnail'] ?? null;
        if (!empty($thumb['name'])) {
            $newThumb = safe_upload($thumb, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($newThumb === false) {
                $error = 'The thumbnail upload failed. Please use JPG, PNG, WEBP, or GIF under 8MB.';
            } else {
                $thumbnailPath = $newThumb;
            }
        }

        // Resource file
        if ($error === '') {
            $resource = $_FILES['resource_file'] ?? null;
            if (!empty($resource['name'])) {
                $newResource = safe_upload($resource, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size'      => 25 * 1024 * 1024,
                ]);
                if ($newResource === false) {
                    $error = 'The main consultancy resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $resourceFilePath = $newResource;
                    $resourceFileName = sanitize_original_filename($resource['name']);
                }
            }
        }

        // Supporting file
        if ($error === '') {
            $support = $_FILES['supporting_file'] ?? null;
            if (!empty($support['name'])) {
                $newSupport = safe_upload($support, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size'      => 25 * 1024 * 1024,
                ]);
                if ($newSupport === false) {
                    $error = 'The supporting resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $supportingFilePath = $newSupport;
                    $supportingFileName = sanitize_original_filename($support['name']);
                }
            }
        }

        // Video file
        if ($error === '') {
            $video = $_FILES['video_file'] ?? null;
            if (!empty($video['name'])) {
                $newVideo = safe_upload($video, ['allowed_mimes' => allowed_video_mimes(), 'max_size' => 120 * 1024 * 1024]);
                if ($newVideo === false) {
                    $error = 'The consultancy video upload failed. Please use MP4, WEBM, or MOV under 120MB.';
                } else {
                    $videoFilePath = $newVideo;
                }
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare('UPDATE consultancy_videos SET title=?, description=?, youtube_url=?, thumbnail=?, caption=?, date=?, resource_file=?, supporting_file=?, video_file=?, resource_file_name=?, supporting_file_name=?, youtube_image_url=? WHERE id=?');
                $stmt->execute([
                    $title,
                    $description !== '' ? $description : null,
                    $youtube_url !== '' ? $youtube_url : null,
                    $thumbnailPath !== '' ? $thumbnailPath : null,
                    $caption !== '' ? $caption : null,
                    $date !== '' ? $date : null,
                    $resourceFilePath !== '' ? $resourceFilePath : null,
                    $supportingFilePath !== '' ? $supportingFilePath : null,
                    $videoFilePath !== '' ? $videoFilePath : null,
                    $resourceFileName !== '' ? $resourceFileName : null,
                    $supportingFileName !== '' ? $supportingFileName : null,
                    $youtube_image_url !== '' ? $youtube_image_url : null,
                    $id,
                ]);
                $success = 'Consultancy item updated successfully.';
                $stmt2 = $pdo->prepare('SELECT * FROM consultancy_videos WHERE id = ?');
                $stmt2->execute([$id]);
                $record = $stmt2->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $error = 'Unable to update the consultancy item. DB error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle         = 'Edit Consultancy Item';
$pageSubtitle      = 'Update this consultancy entry.';
$activeNav         = 'consultancy';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/consultancy/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
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
          <label for="date">Date <span class="hint-badge">Optional</span></label>
          <input id="date" type="date" name="date" value="<?php echo esc($record['date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="youtube_url">YouTube URL <span class="hint-badge">Optional</span></label>
          <input id="youtube_url" type="url" name="youtube_url" value="<?php echo esc($record['youtube_url'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="youtube_image_url"><?php echo admin_icon_html('image'); ?> YouTube Image URL <span class="hint-badge">Optional</span></label>
          <input id="youtube_image_url" type="url" name="youtube_image_url" value="<?php echo esc($record['youtube_image_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
          <span class="field__hint">Paste a YouTube link — its thumbnail will be used as the cover image (saves server storage). Leave blank to use an uploaded thumbnail instead.</span>
        </div>

        <div class="field field--full">
          <label for="caption">Media caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($record['caption'] ?? ''); ?>">
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('consultancy'); ?> Consultancy media &amp; files</strong>
          <p>Upload replacements for any file. Leave a field blank to keep the existing file.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Thumbnail</span><strong>JPG, PNG, WEBP, GIF</strong></div>
            <div class="upload-tip"><span>Video</span><strong>MP4, WEBM, MOV</strong></div>
            <div class="upload-tip"><span>Documents</span><strong>PDF, DOC, DOCX</strong></div>
            <div class="upload-tip"><span>Slides</span><strong>PPT, PPTX</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="thumbnail">
            Replace thumbnail <span class="hint-badge">Optional</span>
            <?php if (has_text($record['thumbnail'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Image on file</span>
            <?php endif; ?>
          </label>
          <input id="thumbnail" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="video_file">
            Replace local video <span class="hint-badge">Optional</span>
            <?php if (has_text($record['video_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Video on file</span>
            <?php endif; ?>
          </label>
          <input id="video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        </div>

        <div class="field">
          <label for="resource_file">
            Replace main resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['resource_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['resource_file_name'] ?? null, $record['resource_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="resource_file" type="file" name="resource_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="supporting_file">
            Replace supporting resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['supporting_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['supporting_file_name'] ?? null, $record['supporting_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="supporting_file" type="file" name="supporting_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/consultancy/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save changes</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
