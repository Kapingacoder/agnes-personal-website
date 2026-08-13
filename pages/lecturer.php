<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Fetch lecturers
$stmt = $pdo->prepare('SELECT id, title, description, image, date FROM lecturers ORDER BY date DESC, created_at DESC');
$stmt->execute();
$lecturers = $stmt->fetchAll();
?>

<main class="page">
  <div class="container">
    <header class="page-header">
      <h2 class="section-title">Lecturer</h2>
      <p class="section-sub">Profiles and academic information</p>
    </header>

    <section class="section lecturers-list">
      <?php if(!$lecturers): ?>
        <p class="muted">No lecturer entries found.</p>
      <?php else: ?>
        <?php foreach($lecturers as $l): ?>
          <article class="lecturer-item">
            <div class="lecturer-media">
              <?php if(!empty($l['image'])): ?>
                <img src="<?php echo esc($l['image']); ?>" alt="<?php echo esc($l['title']); ?>">
              <?php else: ?>
                <div class="placeholder-img"></div>
              <?php endif; ?>
            </div>
            <div class="lecturer-body">
              <h4><?php echo esc($l['title']); ?></h4>
              <div class="expandable" data-collapsed-lines="3">
                <p><?php echo esc($l['description']); ?></p>
              </div>
              <button class="see-more-toggle btn btn-secondary" aria-expanded="false">See More</button>
              <div class="meta"><?php echo esc($l['date']); ?></div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
