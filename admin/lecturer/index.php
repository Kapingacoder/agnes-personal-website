<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

ensure_table_columns($pdo, 'lecturers', [
    'caption' => 'TEXT',
    'date' => 'DATE',
    'primary_file' => 'TEXT',
    'secondary_file' => 'TEXT',
    'video_file' => 'TEXT',
    'primary_file_name' => 'TEXT',
    'secondary_file_name' => 'TEXT',
]);

$status = $_GET['status'] ?? '';
$error = $_GET['error'] ?? '';

$stmt = $pdo->prepare('SELECT id, title, description, image, caption, date, primary_file, secondary_file, video_file, primary_file_name, secondary_file_name FROM lecturers ORDER BY date DESC, created_at DESC');
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$total = count($items);
$withImages = 0;
$withFiles = 0;
$latestDate = '';
foreach ($items as $item) {
    if (has_text($item['image'])) {
        $withImages++;
    }
    if (has_text($item['primary_file'] ?? null) || has_text($item['secondary_file'] ?? null) || has_text($item['video_file'] ?? null)) {
        $withFiles++;
    }
    if ($latestDate === '' && has_text($item['date'])) {
        $latestDate = format_display_date($item['date']);
    }
}

$pageTitle = 'Manage Lecturers';
$pageSubtitle = 'Review lecturer profiles, uploaded files, and media in one organized place.';
$activeNav = 'lecturers';
$topbarActionsHtml = '<a class="btn btn-primary btn-sm" href="/admin/lecturer/add.php">' . admin_icon_html('add') . '<span>Add lecturer</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <?php if ($status === 'deleted'): ?><div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span>Lecturer deleted successfully.</span></div><?php endif; ?>
  <?php if ($error === 'invalid'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Invalid lecturer selected.</span></div><?php endif; ?>
  <?php if ($error === 'failed'): ?><div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span>Unable to delete this lecturer.</span></div><?php endif; ?>

  <section class="stat-grid" aria-label="Lecturer metrics">
    <article class="stat-card">
      <div class="stat-card__top"><span class="stat-card__label">Total</span><span class="stat-card__icon"><?php echo admin_icon_html('lecturers'); ?></span></div>
      <p class="stat-card__value"><?php echo $total; ?></p>
      <div class="stat-card__meta"><span>Profiles</span><strong><?php echo $total > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--green">
      <div class="stat-card__top"><span class="stat-card__label">With cover images</span><span class="stat-card__icon"><?php echo admin_icon_html('image'); ?></span></div>
      <p class="stat-card__value"><?php echo $withImages; ?></p>
      <div class="stat-card__meta"><span>Visuals</span><strong><?php echo $withImages > 0 ? 'Available' : 'None'; ?></strong></div>
    </article>
    <article class="stat-card stat-card--purple">
      <div class="stat-card__top"><span class="stat-card__label">With files/media</span><span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span></div>
      <p class="stat-card__value"><?php echo $withFiles; ?></p>
      <div class="stat-card__meta"><span>Resources</span><strong><?php echo $withFiles > 0 ? 'Ready' : 'Empty'; ?></strong></div>
    </article>
  </section>

  <section class="panel">
    <div class="panel__head">
      <div>
        <h3>Lecturer listings</h3>
        <p>All lecturer profiles, newest first.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="/admin/lecturer/add.php"><?php echo admin_icon_html('add'); ?><span>Add lecturer</span></a>
    </div>

    <?php if (empty($items)): ?>
      <div class="empty-state">
        <?php echo admin_icon_html('lecturers'); ?>
        <strong>No lecturer profiles yet</strong>
        <span>Use the "Add lecturer" button to create the first profile.</span>
      </div>
    <?php else: ?>
      <div class="listing-grid">
        <?php foreach ($items as $item): ?>
          <?php
            $hasImage = has_text($item['image']);
            $hasDescription = has_text($item['description']);
            $displayDate = format_display_date($item['date']);
          ?>
          <article class="listing-card<?php echo $hasImage ? '' : ' no-media'; ?>">
            <?php if ($hasImage): ?><div class="listing-media"><img src="<?php echo esc($item['image']); ?>" alt="<?php echo esc($item['title']); ?>"></div><?php endif; ?>
            <div class="listing-content">
              <span class="listing-tag">Lecturer</span>
              <div class="listing-title-row">
                <h3 class="listing-title"><?php echo esc($item['title']); ?></h3>
                <?php if ($displayDate !== ''): ?><span class="listing-date"><?php echo esc($displayDate); ?></span><?php endif; ?>
              </div>
              <?php if ($hasDescription): ?><p class="listing-text"><?php echo nl2br(esc($item['description'])); ?></p><?php endif; ?>
              <div class="listing-meta">
                <?php if (has_text($item['caption'])): ?><span class="chip"><?php echo esc($item['caption']); ?></span><?php endif; ?>
                <?php if (has_text($item['primary_file'] ?? null)): ?><span class="chip"><?php echo esc(file_label_from_path($item['primary_file'])); ?>: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['primary_file_name'] ?? null, $item['primary_file']), 28)); ?></span><?php endif; ?>
                <?php if (has_text($item['secondary_file'] ?? null)): ?><span class="chip"><?php echo esc(file_label_from_path($item['secondary_file'])); ?>: <?php echo esc(truncate_filename_for_display(resolved_file_display_name($item['secondary_file_name'] ?? null, $item['secondary_file']), 28)); ?></span><?php endif; ?>
                <?php if (has_text($item['video_file'] ?? null)): ?><span class="chip">Video uploaded</span><?php endif; ?>
              </div>
            </div>
            <div class="listing-controls">
              <a class="btn btn-secondary btn-sm" href="/admin/lecturer/edit.php?id=<?php echo (int)$item['id']; ?>"><?php echo admin_icon_html('edit'); ?><span>Edit</span></a>
              <form method="post" action="/admin/lecturer/delete.php" onsubmit="return confirm('Delete this lecturer?');">
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
