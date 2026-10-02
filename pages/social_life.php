<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_social_life_table($pdo);

$stmt = $pdo->query('SELECT id, title, caption, media_type, youtube_url, image_file, created_at FROM social_life ORDER BY created_at DESC');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page content-page content-page--social-life">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Behind the Scenes</span>
      <h2 class="page-hero__title">Social Life</h2>
      <p class="page-hero__text">A glimpse into life beyond the lecture hall — moments, travels, events, and everything in between.</p>
    </header>

    <?php if (!$posts): ?>
      <div class="empty-state-card">
        <h3>Nothing here yet</h3>
        <p>Social life moments will appear here once they are added.</p>
      </div>
    <?php else: ?>
      <div class="sl-grid">
        <?php foreach ($posts as $post): ?>
          <?php
            $isYT    = $post['media_type'] === 'youtube';
            $isImage = $post['media_type'] === 'image';
            $embed   = $isYT ? youtube_embed_url($post['youtube_url']) : false;
            $hasTitle   = has_text($post['title']);
            $hasCaption = has_text($post['caption']);
          ?>
          <article class="sl-card<?php echo $isYT ? ' sl-card--video' : ' sl-card--photo'; ?>">

            <!-- Media -->
            <?php if ($embed): ?>
              <div class="sl-card__media sl-card__media--video">
                <div class="video-responsive">
                  <iframe
                    src="<?php echo esc($embed); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                    title="<?php echo esc($post['title'] ?? 'Video'); ?>"
                  ></iframe>
                </div>
              </div>
            <?php elseif ($isImage && has_text($post['image_file'])): ?>
              <div class="sl-card__media sl-card__media--photo">
                <img
                  src="<?php echo esc($post['image_file']); ?>"
                  alt="<?php echo esc($post['title'] ?? 'Social life photo'); ?>"
                  loading="lazy"
                >
              </div>
            <?php endif; ?>

            <!-- Caption / title overlay -->
            <?php if ($hasTitle || $hasCaption): ?>
              <div class="sl-card__caption">
                <?php if ($hasTitle): ?>
                  <h3 class="sl-card__title"><?php echo esc($post['title']); ?></h3>
                <?php endif; ?>
                <?php if ($hasCaption): ?>
                  <p class="sl-card__text"><?php echo esc($post['caption']); ?></p>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <!-- Type badge -->
            <span class="sl-card__badge"><?php echo $isYT ? 'Video' : 'Photo'; ?></span>

          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
