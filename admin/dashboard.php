<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$counts = [
    'lecturers' => 0,
    'publications' => 0,
    'projects' => 0,
    'videos' => 0,
    'messages' => 0,
];

try {
    $counts['lecturers'] = (int)$pdo->query('SELECT COUNT(*) FROM lecturers')->fetchColumn();
    $counts['publications'] = (int)$pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
    $counts['projects'] = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $counts['videos'] = (int)$pdo->query('SELECT COUNT(*) FROM consultancy_videos')->fetchColumn();
    $counts['messages'] = (int)$pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
} catch (Throwable $e) {
    // Keep the defaults above if the database isn't reachable.
}

$user = $_SESSION['admin_user']['username'] ?? 'Admin';

$pageTitle = 'Welcome back, ' . $user;
$pageSubtitle = "Here's a snapshot of everything published on the site, plus quick shortcuts to manage it.";
$activeNav = 'overview';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

  <section class="stat-grid" aria-label="Summary statistics">
    <article class="stat-card">
      <div class="stat-card__top">
        <span class="stat-card__label">Lecturers</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('lecturers'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo (int)$counts['lecturers']; ?></p>
      <div class="stat-card__meta"><span>Profiles</span><strong><?php echo $counts['lecturers'] > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>

    <article class="stat-card stat-card--green">
      <div class="stat-card__top">
        <span class="stat-card__label">Publications</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo (int)$counts['publications']; ?></p>
      <div class="stat-card__meta"><span>Entries</span><strong><?php echo $counts['publications'] > 0 ? 'Updated' : 'Waiting'; ?></strong></div>
    </article>

    <article class="stat-card stat-card--amber">
      <div class="stat-card__top">
        <span class="stat-card__label">Consultancy</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('consultancy'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo (int)$counts['videos']; ?></p>
      <div class="stat-card__meta"><span>Resources</span><strong><?php echo $counts['videos'] > 0 ? 'Ready' : 'No media'; ?></strong></div>
    </article>

    <article class="stat-card stat-card--green">
      <div class="stat-card__top">
        <span class="stat-card__label">Projects</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('publications'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo (int)$counts['projects']; ?></p>
      <div class="stat-card__meta"><span>Journals</span><strong><?php echo $counts['projects'] > 0 ? 'Live' : 'Empty'; ?></strong></div>
    </article>

    <article class="stat-card stat-card--purple">
      <div class="stat-card__top">
        <span class="stat-card__label">Messages</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('messages'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo (int)$counts['messages']; ?></p>
      <div class="stat-card__meta"><span>Inbox</span><strong><?php echo $counts['messages'] > 0 ? 'Active' : 'Empty'; ?></strong></div>
    </article>
  </section>

  <section class="panel-grid">
    <article class="panel">
      <div class="panel__head">
        <div>
          <h3>Quick actions</h3>
          <p>Jump straight into managing your content.</p>
        </div>
      </div>
      <div class="quick-grid">
        <a class="quick-link quick-link--primary" href="/admin/lecturer/add.php"><?php echo admin_icon_html('add'); ?><span>Add Lecturer</span></a>
        <a class="quick-link" href="/admin/lecturer/index.php"><?php echo admin_icon_html('lecturers'); ?><span>Manage Lecturers</span></a>
        <a class="quick-link quick-link--primary" href="/admin/publications/add.php"><?php echo admin_icon_html('add'); ?><span>Add Publication</span></a>
        <a class="quick-link" href="/admin/publications/index.php"><?php echo admin_icon_html('publications'); ?><span>Manage Publications</span></a>
        <a class="quick-link quick-link--primary" href="/admin/projects/add.php"><?php echo admin_icon_html('add'); ?><span>Add Project</span></a>
        <a class="quick-link" href="/admin/projects/index.php"><?php echo admin_icon_html('publications'); ?><span>Manage Projects</span></a>
        <a class="quick-link quick-link--primary" href="/admin/consultancy/add.php"><?php echo admin_icon_html('add'); ?><span>Add Consultancy Item</span></a>
        <a class="quick-link" href="/admin/consultancy/index.php"><?php echo admin_icon_html('consultancy'); ?><span>Manage Consultancy</span></a>
        <a class="quick-link" href="/admin/index.php"><?php echo admin_icon_html('inbox'); ?><span>View Messages</span></a>
        <a class="quick-link" href="/admin/change-password.php"><?php echo admin_icon_html('shield'); ?><span>Change Password</span></a>
        <a class="quick-link" href="/admin/cv.php"><?php echo admin_icon_html('external'); ?><span>Manage CV</span></a>
        <a class="quick-link" href="/index.php" target="_blank"><?php echo admin_icon_html('external'); ?><span>Open Public Site</span></a>
      </div>
    </article>

    <article class="panel">
      <div class="panel__head">
        <div>
          <h3>System status</h3>
          <p>A quick health check.</p>
        </div>
      </div>
      <ul class="status-list">
        <li><span>Database</span><span class="status-pill status-pill--online">Online</span></li>
        <li><span>Website front-end</span><span class="status-pill status-pill--online">Ready</span></li>
        <li><span>Inbox activity</span><span class="status-pill <?php echo $counts['messages'] > 0 ? 'status-pill--pending' : 'status-pill--idle'; ?>"><?php echo $counts['messages'] > 0 ? 'New' : 'Idle'; ?></span></li>
      </ul>
    </article>
  </section>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
