<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_social_life_table($pdo);

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post_exceeds_size_limit()) {
    $error = post_size_limit_message();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim((string)($_POST['title'] ?? ''));
    $caption    = trim((string)($_POST['caption'] ?? ''));
    $media_type = trim((string)($_POST['media_type'] ?? 'youtube')); // 'youtube' | 'image'
    $youtube_url = trim((string)($_POST['youtube_url'] ?? ''));

    if ($title === '') {
        $error = 'Please provide a title or caption for the post.';
    } elseif ($media_type === 'youtube') {
        // Validate YouTube URL
        if ($youtube_url === '') {
            $error = 'Please enter a YouTube URL.';
        } elseif (!youtube_embed_url($youtube_url)) {
            $error = 'The URL does not appear to be a valid YouTube link. Please use a standard youtube.com or youtu.be URL.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO social_life (title, caption, media_type, youtube_url) VALUES (?, ?, ?, ?)');
                $stmt->execute([$title, $caption !== '' ? $caption : null, 'youtube', $youtube_url]);
                $success = 'Post added successfully.';
                $_POST = [];
            } catch (Throwable $e) {
                $error = 'Unable to save the post. DB error: ' . $e->getMessage();
            }
        }
    } elseif ($media_type === 'image') {
        $imageFile = $_FILES['image_file'] ?? null;
        if (empty($imageFile['name'])) {
            $error = 'Please upload an image.';
        } else {
            $imagePath = safe_upload($imageFile, [
                'allowed_mimes' => allowed_image_mimes(),
                'max_size'      => 10 * 1024 * 1024,
            ]);
            if ($imagePath === false) {
                $error = 'Image upload failed. Please use JPG, PNG, WEBP, or GIF under 10MB.';
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO social_life (title, caption, media_type, image_file) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$title, $caption !== '' ? $caption : null, 'image', $imagePath]);
                    $success = 'Post added successfully.';
                    $_POST = [];
                } catch (Throwable $e) {
                    $error = 'Unable to save the post. DB error: ' . $e->getMessage();
                }
            }
        }
    } else {
        $error = 'Invalid media type selected.';
    }
}

$pageTitle         = 'Add Social Life Post';
$pageSubtitle      = 'Share a photo or YouTube video on the Social Life page.';
$activeNav         = 'social_life';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/social_life/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div><?php endif; ?>

  <div class="form-card">
    <form method="post" enctype="multipart/form-data" id="slForm">
      <div class="form-grid">

        <div class="field field--full">
          <label for="title">Title</label>
          <input id="title" name="title" value="<?php echo esc($_POST['title'] ?? ''); ?>" required placeholder="e.g. Team outing at Kilimanjaro">
        </div>

        <div class="field field--full">
          <label for="caption">Caption <span class="hint-badge">Optional</span></label>
          <textarea id="caption" name="caption" placeholder="A short description or note about this moment..."><?php echo esc($_POST['caption'] ?? ''); ?></textarea>
        </div>

        <div class="field field--full">
          <label>Media type</label>
          <div class="media-type-toggle">
            <label class="media-type-option">
              <input type="radio" name="media_type" value="youtube" <?php echo (($_POST['media_type'] ?? 'youtube') === 'youtube') ? 'checked' : ''; ?>>
              <span><?php echo admin_icon_html('view'); ?> YouTube Video</span>
            </label>
            <label class="media-type-option">
              <input type="radio" name="media_type" value="image" <?php echo (($_POST['media_type'] ?? '') === 'image') ? 'checked' : ''; ?>>
              <span><?php echo admin_icon_html('image'); ?> Upload Photo</span>
            </label>
          </div>
        </div>

        <!-- YouTube section -->
        <div class="field field--full" id="ytSection">
          <label for="youtube_url">YouTube URL</label>
          <input id="youtube_url" name="youtube_url" type="url" value="<?php echo esc($_POST['youtube_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
          <span class="field__hint">Paste any YouTube link — standard watch URL, youtu.be short link, or YouTube Shorts.</span>
        </div>

        <!-- Image upload section -->
        <div class="field field--full" id="imageSection" style="display:none;">
          <label for="image_file">Upload photo</label>
          <input id="image_file" type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
          <span class="field__hint">JPG, PNG, WEBP, or GIF — max 10MB.</span>
        </div>

      </div>

      <div class="form-actions">
        <a class="btn btn-secondary" href="/admin/social_life/index.php">Cancel</a>
        <button class="btn btn-primary" type="submit"><?php echo admin_icon_html('check'); ?><span>Save post</span></button>
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
      imgInput.required = true;
    }
  }

  radios.forEach(function (r) { r.addEventListener('change', toggle); });
  toggle();
})();
</script>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
