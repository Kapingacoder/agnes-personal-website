<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Fetch consultancy videos
$stmt = $pdo->prepare('SELECT id, title, description, youtube_url, thumbnail, date FROM consultancy_videos ORDER BY date DESC, created_at DESC');
$stmt->execute();
$videos = $stmt->fetchAll();
?>

<main class="page">
  <div class="container">
    <header class="page-header">
      <h2 class="section-title">Consultancy</h2>
      <p class="section-sub">Selected consultancy presentations and videos</p>
    </header>

    <section class="section videos-list">
      <?php if(!$videos): ?>
        <p class="muted">No consultancy videos found.</p>
      <?php else: ?>
        <?php foreach($videos as $v): ?>
          <article class="video-item">
            <h4><?php echo esc($v['title']); ?></h4>
            <div class="pub-sub"><?php echo esc($v['date']); ?></div>

            <?php $embed = youtube_embed_url($v['youtube_url']); ?>
            <?php if($embed): ?>
              <div class="video-responsive">
                <iframe src="<?php echo esc($embed); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
              </div>
            <?php else: ?>
              <p class="muted">Invalid YouTube URL.</p>
            <?php endif; ?>

            <div class="expandable" data-collapsed-lines="3">
              <p><?php echo esc($v['description']); ?></p>
            </div>
            <button class="see-more-toggle btn btn-secondary" aria-expanded="false">See More</button>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
