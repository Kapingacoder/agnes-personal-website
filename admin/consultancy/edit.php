<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/helpers.php';

$pageTitle = 'Edit Consultancy Item';
$pageSubtitle = 'This editing tool is being finalized.';
$activeNav = 'consultancy';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/consultancy/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <div class="coming-soon">
    <?php echo admin_icon_html('edit'); ?>
    <h2>Editing is coming soon</h2>
    <p>Direct in-place editing for consultancy items isn't available yet. For now, you can delete an entry from the list and add it again with updated details.</p>
    <a class="btn btn-primary" href="/admin/consultancy/index.php"><?php echo admin_icon_html('consultancy'); ?><span>Back to Consultancy</span></a>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
