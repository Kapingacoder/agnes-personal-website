<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/helpers.php';

$pageTitle = 'Edit Publication';
$pageSubtitle = 'This editing tool is being finalized.';
$activeNav = 'publications';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/publications/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <div class="coming-soon">
    <?php echo admin_icon_html('edit'); ?>
    <h2>Editing is coming soon</h2>
    <p>Direct in-place editing for publications isn't available yet. For now, you can delete an entry from the list and add it again with updated details.</p>
    <a class="btn btn-primary" href="/admin/publications/index.php"><?php echo admin_icon_html('publications'); ?><span>Back to Publications</span></a>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
