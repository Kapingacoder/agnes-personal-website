<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'publications', [
    'caption' => 'TEXT',
    'pdf_file_name' => 'TEXT',
]);

$status = $_GET['status'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->prepare('SELECT id, title, authors, journal, year, abstract, doi, publication_url, pdf_file, image, caption, pdf_file_name FROM publications ORDER BY year DESC, created_at DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$total = count($items);
$withMedia = 0;
$withFiles = 0;
foreach ($items as $item) {
    if (has_text($item['image'])) {
        $withMedia++;
    }
    if (has_text($item['pdf_file']) || has_text($item['publication_url'])) {
        $withFiles++;
    }
}

$pageTitle = 'Manage Publications';
$pageSubtitle = 'View research items with clean spacing, clear hierarchy, and full control.';
$activeNav = 'publications';
$topbarActionsHtml = '<a class="btn btn-primary btn-sm" href="/admin/publications/add.php">' . admin_icon_html('add') . '<span>Add publication</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($status === 'deleted'): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span>Publication deleted successfully.</span></div><?php endif; ?>
  <?php if ($error === 'invalid'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Invalid publication selected.</span></div><?php endif; ?>
  <?php if ($error === 'failed'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Unable to delete this publication.</span></div><?php endif; ?>

  <section class="stat-grid" aria-label="Publication metrics">
    <article class="stat-card">
      <div class="stat-card__top"><span class="stat-card__label">Total</span><span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span></div>
      <p class="stat-card__value"><?php echo $total; ?></p>
      <div class="stat-card__meta"><span>Entries</span><strong><?php echo $total > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--green">
      <div class="stat-card__top"><span class="stat-card__label">With images</span><span class="stat-card__icon"><?php echo admin_icon_html('image'); ?></span></div>
      <p class="stat-card__value"><?php echo $withMedia; ?></p>
      <div class="stat-card__meta"><span>Media</span><strong><?php echo $withMedia > 0 ? 'Available' : 'None'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--purple">
      <div class="stat-card__top"><span class="stat-card__label">With links/files</span><span class="stat-card__icon"><?php echo admin_icon_html('external'); ?></span></div>
      <p class="stat-card__value"><?php echo $withFiles; ?></p>
      <div class="stat-card__meta"><span>Access</span><strong><?php echo $withFiles > 0 ? 'Ready' : 'Pending'; ?></strong></div>
    </article>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>Publication listings</h3>
        <p>All research items, newest first.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="/admin/publications/add.php"><?php echo admin_icon_html('add'); ?><span>Add publication</span></a>
    </div>

    <?php if (empty($items)): ?>
      <div class="empty-state">
        <?php echo admin_icon_html('publications'); ?>
        <strong>No publications yet</strong>
        <span>Use the "Add publication" button to create the first entry.</span>
      </div>
    <?php else: ?>
      <div class="listing-grid">
        <?php foreach ($items as $item): ?>
          <?php
            $hasImage = has_text($item['image']);
            $hasAbstract = has_text($item['abstract']);
            $hasAuthors = has_text($item['authors']);
            $hasJournal = has_text($item['journal']);
            $hasDoi = has_text($item['doi']);
          ?>
          <article class="listing-card<?php echo $hasImage ? '' : ' no-media'; ?>">
            <?php if ($hasImage): ?><div class="listing-media"><img src="<?php echo esc($item['image']); ?>" alt="<?php echo esc($item['title']); ?>"></div><?php endif; ?>
            <div class="listing-content">
              <span class="listing-tag">Publication</span>
              <div class="listing-title-row">
                <h3 class="listing-title"><?php echo esc($item['title']); ?></h3>
                <?php if (has_text($item['year'])): ?><span class="listing-date"><?php echo esc($item['year']); ?></span><?php endif; ?>
              </div>
              <?php if ($hasAbstract): ?><p class="listing-text"><?php echo nl2br(esc($item['abstract'])); ?></p><?php endif; ?>
              <div class="listing-meta">
                <?php if ($hasAuthors): ?><span class="chip">Authors: <?php echo esc($item['authors']); ?></span><?php endif; ?>
                <?php if ($hasJournal): ?><span class="chip">Journal: <?php echo esc($item['journal']); ?></span><?php endif; ?>
                <?php if ($hasDoi): ?><span class="chip">DOI: <?php echo esc($item['doi']); ?></span><?php endif; ?>
                <?php if (has_text($item['caption'])): ?><span class="chip"><?php echo esc($item['caption']); ?></span><?php endif; ?>
                <?php if (has_text($item['pdf_file'])): ?><span class="chip"><?php echo esc(file_label_from_path($item['pdf_file'])); ?>: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['pdf_file_name'] ?? null, $item['pdf_file']), 28)); ?></span><?php endif; ?>
                <?php if (has_text($item['publication_url'])): ?><span class="chip">External link</span><?php endif; ?>
              </div>
            </div>
            <div class="listing-controls">
              <a class="btn btn-secondary btn-sm" href="/admin/publications/add.php"><?php echo admin_icon_html('edit'); ?><span>Edit</span></a>
              <form method="post" action="/admin/publications/delete.php" onsubmit="return confirm('Delete this publication?');">
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
