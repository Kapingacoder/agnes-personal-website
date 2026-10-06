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

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: /admin/lecturer/index.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM lecturers WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header('Location: /admin/lecturer/index.php?error=invalid');
    exit;
}

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

    if ($title === '') {
        $error = 'Please provide the lecturer name/title.';
    } elseif ($youtube_url !== '' && !youtube_embed_url($youtube_url)) {
        $error = 'The YouTube URL is not valid. Please use a standard youtube.com or youtu.be link.';
    } else {
        $imagePath         = $record['image'] ?? '';
        $primaryFilePath   = $record['primary_file'] ?? '';
        $secondaryFilePath = $record['secondary_file'] ?? '';
        $videoFilePath     = $record['video_file'] ?? '';
        $primaryFileName   = $record['primary_file_name'] ?? '';
        $secondaryFileName = $record['secondary_file_name'] ?? '';

        $image = $_FILES['image'] ?? null;
        if (!empty($image['name'])) {
            $newImage = safe_upload($image, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($newImage === false) { $error = 'The image upload failed. Please use JPG, PNG, WEBP, or GIF under 8MB.'; }
            else { $imagePath = $newImage; }
        }

        if ($error === '') {
            $primary = $_FILES['primary_file'] ?? null;
            if (!empty($primary['name'])) {
                $newPrimary = safe_upload($primary, ['allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()), 'max_size' => 25 * 1024 * 1024]);
                if ($newPrimary === false) { $error = 'The primary resource upload failed.'; }
                else { $primaryFilePath = $newPrimary; $primaryFileName = sanitize_original_filename($primary['name']); }
            }
        }

        if ($error === '') {
            $secondary = $_FILES['secondary_file'] ?? null;
            if (!empty($secondary['name'])) {
                $newSecondary = safe_upload($secondary, ['allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()), 'max_size' => 25 * 1024 * 1024]);
                if ($newSecondary === false) { $error = 'The secondary resource upload failed.'; }
                else { $secondaryFilePath = $newSecondary; $secondaryFileName = sanitize_original_filename($secondary['name']); }
            }
        }

        if ($error === '') {
            $video = $_FILES['video_file'] ?? null;
            if (!empty($video['name'])) {
                $newVideo = safe_upload($video, ['allowed_mimes' => allowed_video_mimes(), 'max_size' => 120 * 1024 * 1024]);
                if ($newVideo === false) { $error = 'The lecturer video upload failed. Please use MP4, WEBM, or MOV under 120MB.'; }
                else { $videoFilePath = $newVideo; }
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare('UPDATE lecturers SET title=?, description=?, image=?, caption=?, date=?, primary_file=?, secondary_file=?, video_file=?, primary_file_name=?, secondary_file_name=?, youtube_url=? WHERE id=?');
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
                    $id,
                ]);
                $success = 'Lecturer updated successfully.';
                $stmt2 = $pdo->prepare('SELECT * FROM lecturers WHERE id = ?');
                $stmt2->execute([$id]);
                $record = $stmt2->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $error = 'Unable to update the lecturer. DB error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle         = 'Edit Lecturer';
$pageSubtitle      = 'Update this lecturer profile.';
$activeNav         = 'lecturers';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/lecturer/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <div class="form-grid">

        <div class="field field--full">
          <label for="title">Lecturer name</label>
          <input id="title" name="title" value="<?php echo esc($record['title'] ?? ''); ?>" required>
        </div>

        <div class="field field--full">
          <label for="description">Biography / profile text <span class="hint-badge">Optional</span></label>
          <textarea id="description" name="description"><?php echo esc($record['description'] ?? ''); ?></textarea>
        </div>

        <div class="field">
          <label for="date">Date <span class="hint-badge">Optional</span></label>
          <input id="date" type="date" name="date" value="<?php echo esc($record['date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="caption">Media caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($record['caption'] ?? ''); ?>">
        </div>

        <!-- YouTube URL -->
        <div class="field field--full">
          <label for="youtube_url">YouTube Video URL <span class="hint-badge">Optional</span></label>
          <input id="youtube_url" name="youtube_url" type="url" value="<?php echo esc($record['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
          <span class="field__hint">Paste a YouTube link — the video will be embedded on the public Lecturer page.</span>
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('image'); ?> Featured media &amp; files</strong>
          <p>Upload replacements for any file. Leave a field blank to keep the existing file.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Cover image</span><strong>JPG, PNG, WEBP, GIF</strong></div>
            <div class="upload-tip"><span>Documents</span><strong>PDF, DOC, DOCX</strong></div>
            <div class="upload-tip"><span>Presentations</span><strong>PPT, PPTX</strong></div>
            <div class="upload-tip"><span>Local video</span><strong>MP4, WEBM, MOV</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="image">
            Replace cover image <span class="hint-badge">Optional</span>
            <?php if (has_text($record['image'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Image on file</span>
            <?php endif; ?>
          </label>
          <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="video_file">
            Replace lecturer video (local) <span class="hint-badge">Optional</span>
            <?php if (has_text($record['video_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Video on file</span>
            <?php endif; ?>
          </label>
          <input id="video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        </div>

        <div class="field">
          <label for="primary_file">
            Replace primary resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['primary_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['primary_file_name'] ?? null, $record['primary_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="primary_file" type="file" name="primary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="secondary_file">
            Replace secondary resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['secondary_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['secondary_file_name'] ?? null, $record['secondary_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="secondary_file" type="file" name="secondary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/lecturer/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save changes</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>


$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: /admin/lecturer/index.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM lecturers WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header('Location: /admin/lecturer/index.php?error=invalid');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $caption     = trim((string)($_POST['caption'] ?? ''));
    $date        = trim((string)($_POST['date'] ?? ''));

    if ($title === '') {
        $error = 'Please provide the lecturer name/title.';
    } else {
        $imagePath         = $record['image'] ?? '';
        $primaryFilePath   = $record['primary_file'] ?? '';
        $secondaryFilePath = $record['secondary_file'] ?? '';
        $videoFilePath     = $record['video_file'] ?? '';
        $primaryFileName   = $record['primary_file_name'] ?? '';
        $secondaryFileName = $record['secondary_file_name'] ?? '';

        // Image
        $image = $_FILES['image'] ?? null;
        if (!empty($image['name'])) {
            $newImage = safe_upload($image, ['allowed_mimes' => allowed_image_mimes(), 'max_size' => 8 * 1024 * 1024]);
            if ($newImage === false) {
                $error = 'The image upload failed. Please use JPG, PNG, WEBP, or GIF under 8MB.';
            } else {
                $imagePath = $newImage;
            }
        }

        // Primary file
        if ($error === '') {
            $primary = $_FILES['primary_file'] ?? null;
            if (!empty($primary['name'])) {
                $newPrimary = safe_upload($primary, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size'      => 25 * 1024 * 1024,
                ]);
                if ($newPrimary === false) {
                    $error = 'The primary resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $primaryFilePath = $newPrimary;
                    $primaryFileName = sanitize_original_filename($primary['name']);
                }
            }
        }

        // Secondary file
        if ($error === '') {
            $secondary = $_FILES['secondary_file'] ?? null;
            if (!empty($secondary['name'])) {
                $newSecondary = safe_upload($secondary, [
                    'allowed_mimes' => merge_allowed_mimes(allowed_document_mimes(), allowed_image_mimes()),
                    'max_size'      => 25 * 1024 * 1024,
                ]);
                if ($newSecondary === false) {
                    $error = 'The secondary resource upload failed. Allowed types: PDF, DOC, DOCX, PPT, PPTX, JPG, PNG, WEBP, GIF up to 25MB.';
                } else {
                    $secondaryFilePath = $newSecondary;
                    $secondaryFileName = sanitize_original_filename($secondary['name']);
                }
            }
        }

        // Video file
        if ($error === '') {
            $video = $_FILES['video_file'] ?? null;
            if (!empty($video['name'])) {
                $newVideo = safe_upload($video, ['allowed_mimes' => allowed_video_mimes(), 'max_size' => 120 * 1024 * 1024]);
                if ($newVideo === false) {
                    $error = 'The lecturer video upload failed. Please use MP4, WEBM, or MOV under 120MB.';
                } else {
                    $videoFilePath = $newVideo;
                }
            }
        }

        if ($error === '') {
            try {
                $stmt = $pdo->prepare('UPDATE lecturers SET title=?, description=?, image=?, caption=?, date=?, primary_file=?, secondary_file=?, video_file=?, primary_file_name=?, secondary_file_name=? WHERE id=?');
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
                    $id,
                ]);
                $success = 'Lecturer updated successfully.';
                $stmt2 = $pdo->prepare('SELECT * FROM lecturers WHERE id = ?');
                $stmt2->execute([$id]);
                $record = $stmt2->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $error = 'Unable to update the lecturer. DB error: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle         = 'Edit Lecturer';
$pageSubtitle      = 'Update this lecturer profile.';
$activeNav         = 'lecturers';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/lecturer/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <div class="form-grid">

        <div class="field field--full">
          <label for="title">Lecturer name</label>
          <input id="title" name="title" value="<?php echo esc($record['title'] ?? ''); ?>" required>
        </div>

        <div class="field field--full">
          <label for="description">Biography / profile text <span class="hint-badge">Optional</span></label>
          <textarea id="description" name="description"><?php echo esc($record['description'] ?? ''); ?></textarea>
        </div>

        <div class="field">
          <label for="date">Date <span class="hint-badge">Optional</span></label>
          <input id="date" type="date" name="date" value="<?php echo esc($record['date'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="caption">Media caption <span class="hint-badge">Optional</span></label>
          <input id="caption" name="caption" value="<?php echo esc($record['caption'] ?? ''); ?>">
        </div>

        <div class="upload-zone">
          <strong><?php echo admin_icon_html('image'); ?> Featured media</strong>
          <p>Upload replacements for any file. Leave a field blank to keep the existing file.</p>
          <div class="upload-tips">
            <div class="upload-tip"><span>Cover image</span><strong>JPG, PNG, WEBP, GIF</strong></div>
            <div class="upload-tip"><span>Documents</span><strong>PDF, DOC, DOCX</strong></div>
            <div class="upload-tip"><span>Presentations</span><strong>PPT, PPTX</strong></div>
          </div>
        </div>

        <div class="field">
          <label for="image">
            Replace cover image <span class="hint-badge">Optional</span>
            <?php if (has_text($record['image'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Image on file</span>
            <?php endif; ?>
          </label>
          <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="video_file">
            Replace lecturer video <span class="hint-badge">Optional</span>
            <?php if (has_text($record['video_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Video on file</span>
            <?php endif; ?>
          </label>
          <input id="video_file" type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        </div>

        <div class="field">
          <label for="primary_file">
            Replace primary resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['primary_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['primary_file_name'] ?? null, $record['primary_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="primary_file" type="file" name="primary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

        <div class="field">
          <label for="secondary_file">
            Replace secondary resource file <span class="hint-badge">Optional</span>
            <?php if (has_text($record['secondary_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Current: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($record['secondary_file_name'] ?? null, $record['secondary_file']), 28)); ?></span>
            <?php endif; ?>
          </label>
          <input id="secondary_file" type="file" name="secondary_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,image/webp,image/gif">
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/lecturer/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save changes</span></button>
      </div>
    </form>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
