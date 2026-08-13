<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Fetch publications
$stmt = $pdo->prepare('SELECT id, title, authors, journal, year, abstract, doi, publication_url, pdf_file, image FROM publications ORDER BY year DESC, created_at DESC');
$stmt->execute();
$publications = $stmt->fetchAll();
?>

<main class="page">
  <div class="container">
    <header class="page-header">
      <h2 class="section-title">Research &amp; Publications</h2>
      <p class="section-sub">Selected peer-reviewed publications and outputs</p>
    </header>

    <section class="section publications-list">
      <?php if(!$publications): ?>
        <p class="muted">No publications found.</p>
      <?php else: ?>
        <?php foreach($publications as $p): ?>
          <article class="publication-item">
            <div class="pub-meta">
              <h4><?php echo esc($p['title']); ?></h4>
              <div class="pub-sub"><?php echo esc($p['authors']); ?> &#8211; <?php echo esc($p['journal']); ?> (<?php echo esc($p['year']); ?>)</div>
            </div>

            <div class="pub-body">
              <div class="expandable" data-collapsed-lines="3">
                <p><?php echo esc($p['abstract']); ?></p>
              </div>
              <div class="pub-actions">
                <button class="see-more-toggle btn btn-secondary" aria-expanded="false">See More</button>
                <?php if($url = safe_url($p['publication_url'])): ?>
                  <a class="btn btn-primary" href="<?php echo esc($url); ?>" target="_blank" rel="noopener">View Publication</a>
                <?php endif; ?>
                <?php if(!empty($p['pdf_file'])): 
                    $pdfPath = $p['pdf_file'];
                    if(file_exists(__DIR__ . '/../' . ltrim($pdfPath, '/'))){ ?>
                      <a class="btn btn-primary" href="/<?php echo ltrim(esc($pdfPath), '/'); ?>" download>Download PDF</a>
                    <?php } else if(safe_url($p['pdf_file'])){ ?>
                      <a class="btn btn-primary" href="<?php echo esc($p['pdf_file']); ?>" target="_blank" rel="noopener">Download PDF</a>
                    <?php } endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
