<?php
/**
 * Shared admin dashboard shell (opening half).
 *
 * Before including this file, the calling script should set:
 *   $pageTitle        (string) required
 *   $pageSubtitle      (string) optional
 *   $activeNav         (string) optional, one of: overview, lecturers, publications, projects, consultancy, messages
 *   $user              (string) optional, defaults to the logged-in admin's username
 *   $topbarActionsHtml (string) optional, trusted raw HTML rendered in the topbar action slot
 *
 * Pair with includes/admin/layout_bottom.php to close the markup.
 */

$user = $user ?? ($_SESSION['admin_user']['username'] ?? 'Admin');
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Admin';
$pageSubtitle = $pageSubtitle ?? '';
$topbarActionsHtml = $topbarActionsHtml ?? '';

$adminNavItems = [
    'overview' => ['label' => 'Overview', 'href' => '/admin/dashboard.php', 'icon' => 'overview'],
    'lecturers' => ['label' => 'Lecturers', 'href' => '/admin/lecturer/index.php', 'icon' => 'lecturers'],
    'publications' => ['label' => 'Publications', 'href' => '/admin/publications/index.php', 'icon' => 'publications'],
    'projects' => ['label' => 'Projects', 'href' => '/admin/projects/index.php', 'icon' => 'publications'],
    'consultancy' => ['label' => 'Consultancy', 'href' => '/admin/consultancy/index.php', 'icon' => 'consultancy'],
    'messages' => ['label' => 'Messages', 'href' => '/admin/index.php', 'icon' => 'messages'],
    'settings' => ['label' => 'Settings', 'href' => '/admin/change-password.php', 'icon' => 'shield'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo esc($pageTitle); ?> &middot; Agnes Admin</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <meta name="color-scheme" content="dark">
</head>
<body class="admin-body">
  <div class="admin-shell">
    <div class="admin-overlay" id="adminOverlay" hidden></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="admin-sidebar__top">
        <a class="admin-brand" href="/admin/dashboard.php">
          <span class="admin-brand__mark">A</span>
          <span class="admin-brand__text">Agnes Admin</span>
        </a>
        <button class="admin-sidebar__close" id="adminSidebarClose" type="button" aria-label="Close menu"><?php echo admin_icon_html('close'); ?></button>
      </div>

      <nav class="admin-nav" aria-label="Admin navigation">
        <div class="admin-nav__title">Menu</div>
        <?php foreach ($adminNavItems as $key => $item): ?>
          <a class="admin-nav__link<?php echo $activeNav === $key ? ' is-active' : ''; ?>" href="<?php echo esc($item['href']); ?>">
            <span class="admin-nav__icon"><?php echo admin_icon_html($item['icon']); ?></span>
            <span class="admin-nav__label"><?php echo esc($item['label']); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="admin-user-card">
        <div class="admin-user-card__row">
          <div class="admin-user-card__avatar"><?php echo esc(strtoupper(substr($user, 0, 1))); ?></div>
          <div class="admin-user-card__info">
            <div class="admin-user-card__name"><?php echo esc($user); ?></div>
            <div class="admin-user-card__role">Administrator</div>
          </div>
        </div>
        <div class="admin-user-card__actions">
          <a class="btn btn-ghost btn-sm" href="/index.php" target="_blank"><?php echo admin_icon_html('view'); ?><span>View Site</span></a>
          <a class="btn btn-danger-ghost btn-sm" href="/admin/logout.php"><?php echo admin_icon_html('logout'); ?><span>Logout</span></a>
        </div>
      </div>
    </aside>

    <div class="admin-content">
      <header class="admin-topbar">
        <button class="admin-menu-btn" id="adminMenuBtn" type="button" aria-label="Open menu" aria-expanded="false"><?php echo admin_icon_html('menu'); ?></button>
        <div class="admin-topbar__heading">
          <h1><?php echo esc($pageTitle); ?></h1>
          <?php if ($pageSubtitle !== ''): ?><p><?php echo esc($pageSubtitle); ?></p><?php endif; ?>
        </div>
        <?php if ($topbarActionsHtml !== ''): ?>
          <div class="admin-topbar__actions"><?php echo $topbarActionsHtml; ?></div>
        <?php endif; ?>
      </header>

      <main class="admin-main">
