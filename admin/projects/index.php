<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_projects_table($pdo);
ensure_table_columns($pdo, 'projects', [
    'description' => 'TEXT', 'publication_date' => 'DATE', 'pdf_file' => 'TEXT',
    'pdf_file_name' => 'TEXT', 'cover_image' => 'TEXT', 'caption' => 'TEXT',
    'allow_download' => 'BOOLEAN DEFAULT TRUE', 'allow_print' => 'BOOLEAN DEFAULT TRUE',
    'viewer_enabled' => 'BOOLEAN DEFAULT TRUE',
]);
$status = $_GET['status'] ?? '';
$stmt = $pdo->query('SELECT id, title, description, publication_date, pdf_file, pdf_file_name, cover_image, allow_download, allow_print, viewer_enabled FROM projects ORDER BY publication_date DESC, created_at DESC');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($items);
$pageTitle = 'Projects';
$pageSubtitle = 'Manage journals published to the public Projects page.';
$activeNav = 'projects';
$topbarActionsHtml = '<a class="btn btn-primary btn-sm" href="/admin/projects/add.php">' . admin_icon_html('add') . '<span>Add project journal</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($status === 'deleted'): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span>Project journal deleted successfully.</span></div><?php endif; ?>
  <section class="stat-grid" aria-label="Project metrics">
    <article class="stat-card"><div class="stat-card__top"><span class="stat-card__label">Total projects</span><span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span></div><p class="stat-card__value"><?php echo $total; ?></p><div class="stat-card__meta"><span>Journals</span><strong><?php echo $total ? 'Live' : 'Empty'; ?></strong></div></article>
    <article class="stat-card stat-card--green"><div class="stat-card__top"><span class="stat-card__label">Readers enabled</span><span class="stat-card__icon"><?php echo admin_icon_html('view'); ?></span></div><p class="stat-card__value"><?php echo count(array_filter($items, static fn($item) => (int)$item['viewer_enabled'] === 1)); ?></p><div class="stat-card__meta"><span>Embedded</span><strong>Ready</strong></div></article>
    <article class="stat-card stat-card--purple"><div class="stat-card__top"><span class="stat-card__label">PDF files</span><span class="stat-card__icon"><?php echo admin_icon_html('external'); ?></span></div><p class="stat-card__value"><?php echo count(array_filter($items, static fn($item) => has_text($item['pdf_file']))); ?></p><div class="stat-card__meta"><span>Uploads</span><strong>Stored</strong></div></article>
  </section>

  <section class="panel">
    <div class="panel__head"><div><h3>Project journals</h3><p>All journals, newest first.</p></div><a class="btn btn-primary btn-sm" href="/admin/projects/add.php"><?php echo admin_icon_html('add'); ?><span>Add project journal</span></a></div>
    <?php if (!$items): ?>
      <div class="empty-state"><?php echo admin_icon_html('publications'); ?><strong>No project journals yet</strong><span>Use the add button to publish the first journal.</span></div>
    <?php else: ?>
      <div class="listing-grid">
        <?php foreach ($items as $item): ?>
          <?php $hasCover = has_text($item['cover_image']); $date = format_display_date($item['publication_date']); ?>
          <article class="listing-card<?php echo $hasCover ? '' : ' no-media'; ?>">
            <?php if ($hasCover): ?><div class="listing-media"><img src="<?php echo esc($item['cover_image']); ?>" alt="<?php echo esc($item['title']); ?>"></div><?php endif; ?>
            <div class="listing-content"><span class="listing-tag">Project journal</span><div class="listing-title-row"><h3 class="listing-title"><?php echo esc($item['title']); ?></h3><?php if ($date): ?><span class="listing-date"><?php echo esc($date); ?></span><?php endif; ?></div><?php if (has_text($item['description'])): ?><p class="listing-text"><?php echo nl2br(esc($item['description'])); ?></p><?php endif; ?><div class="listing-meta"><span class="chip">PDF: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['pdf_file_name'], $item['pdf_file']), 28)); ?></span><span class="chip"><?php echo (int)$item['viewer_enabled'] === 1 ? 'Reader enabled' : 'Reader disabled'; ?></span><span class="chip"><?php echo (int)$item['allow_download'] === 1 ? 'Downloads on' : 'Downloads off'; ?></span><span class="chip"><?php echo (int)$item['allow_print'] === 1 ? 'Printing on' : 'Printing off'; ?></span></div></div>
            <div class="listing-controls"><a class="btn btn-secondary btn-sm" href="/admin/projects/add.php"><?php echo admin_icon_html('edit'); ?><span>Add another</span></a><form method="post" action="/admin/projects/delete.php" onsubmit="return confirm('Delete this project journal?');"><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><button class="btn btn-danger-ghost btn-sm btn-block" type="submit"><?php echo admin_icon_html('delete'); ?><span>Delete</span></button></form></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
