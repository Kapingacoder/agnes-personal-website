<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_social_life_table($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: /admin/social_life/index.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM social_life WHERE id = ?');
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    header('Location: /admin/social_life/index.php?error=invalid');
    exit;
}

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string)($_POST['title'] ?? ''));
    $caption     = trim((string)($_POST['caption'] ?? ''));
    $media_type  = trim((string)($_POST['media_type'] ?? $record['media_type']));
    $youtube_url = trim((string)($_POST['youtube_url'] ?? ''));

    if ($title === '') {
        $error = 'Please provide a title for the post.';
    } elseif ($media_type === 'youtube') {
        if ($youtube_url === '') {
            $error = 'Please enter a YouTube URL.';
        } elseif (!youtube_embed_url($youtube_url)) {
            $error = 'The URL does not appear to be a valid YouTube link.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE social_life SET title=?, caption=?, media_type=?, youtube_url=?, image_file=NULL WHERE id=?');
                $stmt->execute([$title, $caption !== '' ? $caption : null, 'youtube', $youtube_url, $id]);
                $success = 'Post updated successfully.';
                $stmt2 = $pdo->prepare('SELECT * FROM social_life WHERE id = ?');
                $stmt2->execute([$id]);
                $record = $stmt2->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $error = 'Unable to update the post. DB error: ' . $e->getMessage();
            }
        }
    } elseif ($media_type === 'image') {
        $imagePath = $record['image_file'] ?? '';
        $imageFile = $_FILES['image_file'] ?? null;

        if (!empty($imageFile['name'])) {
            $newImage = safe_upload($imageFile, [
                'allowed_mimes' => allowed_image_mimes(),
                'max_size'      => 10 * 1024 * 1024,
            ]);
            if ($newImage === false) {
                $error = 'Image upload failed. Please use JPG, PNG, WEBP, or GIF under 10MB.';
            } else {
                $imagePath = $newImage;
            }
        }

        if ($error === '') {
            if ($imagePath === '') {
                $error = 'Please upload an image.';
            } else {
                try {
                    $stmt = $pdo->prepare('UPDATE social_life SET title=?, caption=?, media_type=?, image_file=?, youtube_url=NULL WHERE id=?');
                    $stmt->execute([$title, $caption !== '' ? $caption : null, 'image', $imagePath, $id]);
                    $success = 'Post updated successfully.';
                    $stmt2 = $pdo->prepare('SELECT * FROM social_life WHERE id = ?');
                    $stmt2->execute([$id]);
                    $record = $stmt2->fetch(PDO::FETCH_ASSOC);
                } catch (Throwable $e) {
                    $error = 'Unable to update the post. DB error: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle         = 'Edit Social Life Post';
$pageSubtitle      = 'Update this post.';
$activeNav         = 'social_life';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/social_life/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
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
          <label for="caption">Caption <span class="hint-badge">Optional</span></label>
          <textarea id="caption" name="caption"><?php echo esc($record['caption'] ?? ''); ?></textarea>
        </div>

        <div class="field field--full">
          <label>Media type</label>
          <div class="media-type-toggle">
            <label class="media-type-option">
              <input type="radio" name="media_type" value="youtube" <?php echo $record['media_type'] === 'youtube' ? 'checked' : ''; ?>>
              <span><?php echo admin_icon_html('view'); ?> YouTube Video</span>
            </label>
            <label class="media-type-option">
              <input type="radio" name="media_type" value="image" <?php echo $record['media_type'] === 'image' ? 'checked' : ''; ?>>
              <span><?php echo admin_icon_html('image'); ?> Upload Photo</span>
            </label>
          </div>
        </div>

        <!-- YouTube section -->
        <div class="field field--full" id="ytSection" <?php echo $record['media_type'] !== 'youtube' ? 'style="display:none;"' : ''; ?>>
          <label for="youtube_url">YouTube URL</label>
          <input id="youtube_url" name="youtube_url" type="url" value="<?php echo esc($record['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=...">
          <span class="field__hint">Paste any YouTube link — standard watch URL, youtu.be short link, or YouTube Shorts.</span>
        </div>

        <!-- Image upload section -->
        <div class="field field--full" id="imageSection" <?php echo $record['media_type'] !== 'image' ? 'style="display:none;"' : ''; ?>>
          <label for="image_file">
            Replace photo <span class="hint-badge">Optional</span>
            <?php if (has_text($record['image_file'] ?? '')): ?>
              <span class="hint-badge" style="background:var(--color-green-light,#d1fae5);color:var(--color-green,#065f46);">Photo on file</span>
            <?php endif; ?>
          </label>
          <input id="image_file" type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
          <span class="field__hint">Leave blank to keep the existing photo. JPG, PNG, WEBP, or GIF — max 10MB.</span>
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/social_life/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save changes</span></button>
      </div>
    </form>
  </div>

<script>
(function () {
  var radios = document.querySelectorAll('input[name="media_type"]');
  var ytSection = document.getElementById('ytSection');
  var imgSection = document.getElementById('imageSection');
  var ytInput = document.getElementById('youtube_url');
  var imgInput = document.getElementById('image_file');

  function toggle() {
    var val = document.querySelector('input[name="media_type"]:checked').value;
    if (val === 'youtube') {
      ytSection.style.display = '';
      imgSection.style.display = 'none';
      ytInput.required = true;
      imgInput.required = false;
    } else {
      ytSection.style.display = 'none';
      imgSection.style.display = '';
      ytInput.required = false;
      imgInput.required = false;
    }
  }

  radios.forEach(function (r) { r.addEventListener('change', toggle); });
  toggle();
})();
</script>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
