<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/helpers.php';

$pageTitle = 'Edit Lecturer';
$pageSubtitle = 'This editing tool is being finalized.';
$activeNav = 'lecturers';
$topbarActionsHtml = '<a class="btn btn-secondary btn-sm" href="/admin/lecturer/index.php">' . admin_icon_html('chevron') . '<span>Back to list</span></a>';
require __DIR__ . '/../../includes/admin/layout_top.php';
?>

  <div class="coming-soon">
    <?php echo admin_icon_html('edit'); ?>
    <h2>Editing is coming soon</h2>
    <p>Direct in-place editing for lecturer profiles isn't available yet. For now, you can delete an entry from the list and add it again with updated details.</p>
    <a class="btn btn-primary" href="/admin/lecturer/index.php"><?php echo admin_icon_html('lecturers'); ?><span>Back to Lecturers</span></a>
  </div>

<?php require __DIR__ . '/../../includes/admin/layout_bottom.php'; ?>
