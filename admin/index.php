<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_message') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM contacts WHERE id = ?');
            $stmt->execute([$id]);
            $success = 'Message deleted successfully.';
        } catch (Throwable $e) {
            $error = 'Unable to delete this message right now.';
        }
    } else {
        $error = 'Invalid message selected.';
    }
}

$stmt = $pdo->prepare('SELECT id, name, email, message, created_at FROM contacts ORDER BY created_at DESC');
$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$selected = null;
if (!empty($messages)) {
    if ($selectedId > 0) {
        foreach ($messages as $message) {
            if ((int)$message['id'] === $selectedId) {
                $selected = $message;
                break;
            }
        }
    }
    if ($selected === null) {
        $selected = $messages[0];
    }
}

$user = $_SESSION['admin_user']['username'] ?? 'Admin';
$total = count($messages);

$pageTitle = 'Messages Inbox';
$pageSubtitle = 'Read and manage messages submitted through the public contact form.';
$activeNav = 'messages';
require __DIR__ . '/../includes/admin/layout_top.php';
?>

  <?php if ($success !== ''): ?>
    <div class="notice notice-success"><?php echo admin_icon_html('check'); ?><span><?php echo esc($success); ?></span></div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="notice notice-error"><?php echo admin_icon_html('close'); ?><span><?php echo esc($error); ?></span></div>
  <?php endif; ?>

  <section class="stat-grid" aria-label="Inbox metrics">
    <article class="stat-card">
      <div class="stat-card__top">
        <span class="stat-card__label">Total</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('inbox'); ?></span>
      </div>
      <p class="stat-card__value"><?php echo $total; ?></p>
      <div class="stat-card__meta"><span>Messages</span><strong>Inbox</strong></div>
    </article>

    <article class="stat-card stat-card--green">
      <div class="stat-card__top">
        <span class="stat-card__label">Latest sender</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('user'); ?></span>
      </div>
      <p class="stat-card__value" style="font-size:1.4rem"><?php echo !empty($messages) ? esc(mb_substr($messages[0]['name'], 0, 16)) : '&mdash;'; ?></p>
      <div class="stat-card__meta"><span>Sender</span><strong><?php echo !empty($messages) ? 'New' : 'Empty'; ?></strong></div>
    </article>

    <article class="stat-card stat-card--purple">
      <div class="stat-card__top">
        <span class="stat-card__label">Status</span>
        <span class="stat-card__icon"><?php echo admin_icon_html('shield'); ?></span>
      </div>
      <p class="stat-card__value" style="font-size:1.4rem"><?php echo !empty($messages) ? 'Live' : 'Idle'; ?></p>
      <div class="stat-card__meta"><span>Inbox</span><strong><?php echo !empty($messages) ? 'Active' : 'Waiting'; ?></strong></div>
    </article>
  </section>

  <section class="mail-layout">
    <div class="panel">
      <div class="panel__head">
        <h3>Recent messages</h3>
        <span class="badge"><?php echo $total; ?></span>
      </div>

      <?php if (empty($messages)): ?>
        <div class="empty-state">
          <?php echo admin_icon_html('inbox'); ?>
          <strong>No messages yet</strong>
          <span>New contact form submissions will appear here automatically.</span>
        </div>
      <?php else: ?>
        <div class="message-list">
          <?php foreach ($messages as $message): ?>
            <a class="message-item<?php echo ($selected && (int)$message['id'] === (int)$selected['id']) ? ' is-active' : ''; ?>" href="/admin/index.php?id=<?php echo (int)$message['id']; ?>">
              <div class="message-item__top">
                <span class="message-name"><?php echo esc($message['name']); ?></span>
                <span class="message-time"><?php echo esc(date('M d, Y', strtotime($message['created_at']))); ?></span>
              </div>
              <p class="message-email"><?php echo esc($message['email']); ?></p>
              <p class="message-preview"><?php echo esc(mb_substr(trim($message['message']), 0, 120)); ?><?php echo mb_strlen(trim($message['message'])) > 120 ? '&hellip;' : ''; ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel detail-panel">
      <?php if ($selected): ?>
        <div class="detail-header">
          <div>
            <h2><?php echo esc($selected['name']); ?></h2>
            <div class="detail-meta">
              <span>From: <?php echo esc($selected['email']); ?></span>
              <span>&bull;</span>
              <span><?php echo esc(date('F d, Y H:i', strtotime($selected['created_at']))); ?></span>
            </div>
          </div>
          <span class="badge">Message</span>
        </div>

        <div class="detail-body"><?php echo nl2br(esc($selected['message'])); ?></div>

        <div class="detail-actions">
          <a class="btn btn-primary" href="mailto:<?php echo rawurlencode($selected['email']); ?>?subject=Reply%20to%20your%20message"><?php echo admin_icon_html('messages'); ?><span>Reply</span></a>
          <a class="btn btn-secondary" href="/admin/index.php"><?php echo admin_icon_html('chevron'); ?><span>Back to inbox</span></a>
          <form method="post" onsubmit="return confirm('Delete this message?');" style="display:inline">
            <input type="hidden" name="action" value="delete_message">
            <input type="hidden" name="id" value="<?php echo (int)$selected['id']; ?>">
            <button class="btn btn-danger-ghost" type="submit"><?php echo admin_icon_html('delete'); ?><span>Delete</span></button>
          </form>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <?php echo admin_icon_html('messages'); ?>
          <strong>Select a message</strong>
          <span>Choose a message from the list to read the full content here.</span>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php require __DIR__ . '/../includes/admin/layout_bottom.php'; ?>
