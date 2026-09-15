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

$status = $_GET['status'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->prepare('SELECT id, title, description, youtube_url, thumbnail, caption, date, resource_file, supporting_file, video_file, resource_file_name, supporting_file_name FROM consultancy_videos ORDER BY date DESC, created_at DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$total = count($items);
$withVideo = 0;
$withThumb = 0;
$withResources = 0;
foreach ($items as $item) {
    if (youtube_embed_url($item['youtube_url']) || has_text($item['video_file'] ?? null)) {
        $withVideo++;
    }
    if (has_text($item['thumbnail'])) {
        $withThumb++;
    }
    if (has_text($item['resource_file'] ?? null) || has_text($item['supporting_file'] ?? null)) {
        $withResources++;
    }
}

$pageTitle = 'Manage Consultancy';
$pageSubtitle = 'Browse consultancy items, uploaded files, YouTube links, and local videos.';
$activeNav = 'consultancy';
$topbarActionsHtml = '<a class="btn btn-primary btn-sm" href="/admin/consultancy/add.php">' . admin_icon_html('add') . '<span>Add consultancy item</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($status === 'deleted'): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span>Consultancy item deleted successfully.</span></div><?php endif; ?>
  <?php if ($error === 'invalid'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Invalid consultancy item selected.</span></div><?php endif; ?>
  <?php if ($error === 'failed'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Unable to delete this consultancy item.</span></div><?php endif; ?>

  <section class="stat-grid" aria-label="Consultancy metrics">
    <article class="stat-card">
      <div class="stat-card__top"><span class="stat-card__label">Total</span><span class="stat-card__icon"><?php echo admin_icon_html('consultancy'); ?></span></div>
      <p class="stat-card__value"><?php echo $total; ?></p>
      <div class="stat-card__meta"><span>Entries</span><strong><?php echo $total > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--green">
      <div class="stat-card__top"><span class="stat-card__label">With video</span><span class="stat-card__icon"><?php echo admin_icon_html('view'); ?></span></div>
      <p class="stat-card__value"><?php echo $withVideo; ?></p>
      <div class="stat-card__meta"><span>Media</span><strong><?php echo $withVideo > 0 ? 'Playable' : 'None'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--purple">
      <div class="stat-card__top"><span class="stat-card__label">With resource files</span><span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span></div>
      <p class="stat-card__value"><?php echo $withResources; ?></p>
      <div class="stat-card__meta"><span>Downloads</span><strong><?php echo $withResources > 0 ? 'Available' : 'Empty'; ?></strong></div>
    </article>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>Consultancy listings</h3>
        <p>All consultancy items, newest first.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="/admin/consultancy/add.php"><?php echo admin_icon_html('add'); ?><span>Add consultancy item</span></a>
    </div>

    <?php if (empty($items)): ?>
      <div class="empty-state">
        <?php echo admin_icon_html('consultancy'); ?>
        <strong>No consultancy items yet</strong>
        <span>Use the "Add consultancy item" button to create the first entry.</span>
      </div>
    <?php else: ?>
      <div class="listing-grid">
        <?php foreach ($items as $item): ?>
          <?php $hasThumb = has_text($item['thumbnail']); $hasDesc = has_text($item['description']); $displayDate = format_display_date($item['date']); ?>
          <article class="listing-card<?php echo $hasThumb ? '' : ' no-media'; ?>">
            <?php if ($hasThumb): ?><div class="listing-media"><img src="<?php echo esc($item['thumbnail']); ?>" alt="<?php echo esc($item['title']); ?>"></div><?php endif; ?>
            <div class="listing-content">
              <span class="listing-tag">Consultancy</span>
              <div class="listing-title-row">
                <h3 class="listing-title"><?php echo esc($item['title']); ?></h3>
                <?php if ($displayDate !== ''): ?><span class="listing-date"><?php echo esc($displayDate); ?></span><?php endif; ?>
              </div>
              <?php if ($hasDesc): ?><p class="listing-text"><?php echo nl2br(esc($item['description'])); ?></p><?php endif; ?>
              <div class="listing-meta">
                <?php if (youtube_embed_url($item['youtube_url'])): ?><span class="chip">YouTube linked</span><?php endif; ?>
                <?php if (has_text($item['video_file'] ?? null)): ?><span class="chip">Local video uploaded</span><?php endif; ?>
                <?php if (has_text($item['resource_file'] ?? null)): ?><span class="chip"><?php echo esc(file_label_from_path($item['resource_file'])); ?>: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['resource_file_name'] ?? null, $item['resource_file']), 28)); ?></span><?php endif; ?>
                <?php if (has_text($item['supporting_file'] ?? null)): ?><span class="chip"><?php echo esc(file_label_from_path($item['supporting_file'])); ?>: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['supporting_file_name'] ?? null, $item['supporting_file']), 28)); ?></span><?php endif; ?>
                <?php if (has_text($item['caption'])): ?><span class="chip"><?php echo esc($item['caption']); ?></span><?php endif; ?>
              </div>
            </div>
            <div class="listing-controls">
              <a class="btn btn-secondary btn-sm" href="/admin/consultancy/edit.php?id=<?php echo (int)$item['id']; ?>"><?php echo admin_icon_html('edit'); ?><span>Edit</span></a>
              <form method="post" action="/admin/consultancy/delete.php" onsubmit="return confirm('Delete this consultancy item?');">
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
