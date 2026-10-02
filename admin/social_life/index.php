<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Ensure table exists with all columns
ensure_social_life_table($pdo);

$status = $_GET['status'] ?? '';
$error  = $_GET['error'] ?? '';

$stmt = $pdo->query('SELECT id, title, caption, media_type, youtube_url, image_file, created_at FROM social_life ORDER BY created_at DESC');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total     = count($items);
$withYT    = count(array_filter($items, fn($i) => $i['media_type'] === 'youtube'));
$withImage = count(array_filter($items, fn($i) => $i['media_type'] === 'image'));

$pageTitle         = 'Social Life';
$pageSubtitle      = 'Manage photos and YouTube videos shared on the Social Life page.';
$activeNav         = 'social_life';
$topbarActionsHtml = '<a class="btn btn-primary btn-sm" href="/admin/social_life/add.php">' . admin_icon_html('add') . '<span>Add post</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($status === 'deleted'): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span>Post deleted successfully.</span></div><?php endif; ?>
  <?php if ($error === 'invalid'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Invalid post selected.</span></div><?php endif; ?>
  <?php if ($error === 'failed'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Unable to delete this post.</span></div><?php endif; ?>

  <section class="stat-grid" aria-label="Social Life metrics">
    <article class="stat-card">
      <div class="stat-card__top"><span class="stat-card__label">Total posts</span><span class="stat-card__icon"><?php echo admin_icon_html('image'); ?></span></div>
      <p class="stat-card__value"><?php echo $total; ?></p>
      <div class="stat-card__meta"><span>Posts</span><strong><?php echo $total > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--green">
      <div class="stat-card__top"><span class="stat-card__label">YouTube videos</span><span class="stat-card__icon"><?php echo admin_icon_html('view'); ?></span></div>
      <p class="stat-card__value"><?php echo $withYT; ?></p>
      <div class="stat-card__meta"><span>Links</span><strong><?php echo $withYT > 0 ? 'Live' : 'None'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--purple">
      <div class="stat-card__top"><span class="stat-card__label">Uploaded photos</span><span class="stat-card__icon"><?php echo admin_icon_html('image'); ?></span></div>
      <p class="stat-card__value"><?php echo $withImage; ?></p>
      <div class="stat-card__meta"><span>Images</span><strong><?php echo $withImage > 0 ? 'Stored' : 'None'; ?></strong></div>
    </article>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div><h3>Social Life posts</h3><p>All posts, newest first.</p></div>
      <a class="btn btn-primary btn-sm" href="/admin/social_life/add.php"><?php echo admin_icon_html('add'); ?><span>Add post</span></a>
    </div>

    <?php if (!$items): ?>
      <div class="empty-state">
        <?php echo admin_icon_html('image'); ?>
        <strong>No posts yet</strong>
        <span>Use the "Add post" button to share your first Social Life moment.</span>
      </div>
    <?php else: ?>
      <div class="listing-grid">
        <?php foreach ($items as $item): ?>
          <?php
            $isYT    = $item['media_type'] === 'youtube';
            $isImage = $item['media_type'] === 'image';
            $thumb   = $isYT && has_text($item['youtube_url'])
                         ? 'https://img.youtube.com/vi/' . esc(youtube_video_id($item['youtube_url'])) . '/mqdefault.jpg'
                         : ($isImage && has_text($item['image_file']) ? $item['image_file'] : '');
          ?>
          <article class="listing-card<?php echo $thumb ? '' : ' no-media'; ?>">
            <?php if ($thumb): ?>
              <div class="listing-media"><img src="<?php echo esc($thumb); ?>" alt="<?php echo esc($item['title'] ?? 'Social post'); ?>"></div>
            <?php endif; ?>
            <div class="listing-content">
              <span class="listing-tag"><?php echo $isYT ? 'YouTube' : 'Photo'; ?></span>
              <div class="listing-title-row">
                <h3 class="listing-title"><?php echo esc($item['title'] ?? '(No title)'); ?></h3>
                <span class="listing-date"><?php echo esc(date('M j, Y', strtotime($item['created_at']))); ?></span>
              </div>
              <?php if (has_text($item['caption'])): ?><p class="listing-text"><?php echo esc($item['caption']); ?></p><?php endif; ?>
              <div class="listing-meta">
                <span class="chip"><?php echo $isYT ? 'YouTube video' : 'Uploaded image'; ?></span>
                <?php if ($isYT && has_text($item['youtube_url'])): ?>
                  <span class="chip"><?php echo esc(truncate_filename_for_display($item['youtube_url'], 40)); ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="listing-controls">
              <a class="btn btn-secondary btn-sm" href="/admin/social_life/edit.php?id=<?php echo (int)$item['id']; ?>"><?php echo admin_icon_html('edit'); ?><span>Edit</span></a>
              <form method="post" action="/admin/social_life/delete.php" onsubmit="return confirm('Delete this post?');">
                <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                <button class="btn btn-danger-ghost btn-sm btn-block" type="submit"><?php echo admin_icon_html('delete'); ?><span>Delete</span></button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
