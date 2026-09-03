<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_table_columns($pdo, 'publications', [
    'caption' => 'TEXT',
    'pdf_file_name' => 'TEXT',
]);

$stmt = $pdo->prepare('SELECT id, title, authors, journal, year, abstract, doi, publication_url, pdf_file, image, caption, pdf_file_name FROM publications ORDER BY year DESC, created_at DESC');
$stmt->execute();
$publications = $stmt->fetchAll();
?>

<main class="page content-page content-page--publications">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Research Output</span>
      <h2 class="page-hero__title">Publications &amp; Scholarly Work</h2>
      <p class="page-hero__text">A polished showcase of published work, designed to present each research item beautifully while hiding fields that were never supplied.</p>
    </header>

    <section class="content-grid content-grid--stacked publications-list">
      <?php if (!$publications): ?>
        <div class="empty-state-card">
          <h3>No publications yet</h3>
          <p>Research outputs added by the administrator will appear here automatically.</p>
        </div>
      <?php else: ?>
        <?php foreach ($publications as $p): ?>
          <?php
            $hasImage = has_text($p['image']);
            $hasCaption = $hasImage && has_text($p['caption']);
            $hasAuthors = has_text($p['authors']);
            $hasJournal = has_text($p['journal']);
            $hasYear = has_text($p['year']);
            $hasAbstract = has_text($p['abstract']);
            $hasDoi = has_text($p['doi']);
            $publicationUrl = safe_url($p['publication_url']);
            $pdfUrl = null;

            if (has_text($p['pdf_file'])) {
                $pdfPath = trim((string)$p['pdf_file']);
                if (is_safe_local_path($pdfPath)) {
                    $pdfUrl = $pdfPath;
                } elseif (safe_url($pdfPath)) {
                    $pdfUrl = $pdfPath;
                }
            }

            $pdfKind = $pdfUrl ? file_kind_from_path($pdfUrl) : '';
            $pdfLabel = $pdfUrl ? file_label_from_path($pdfUrl) : '';
            $pdfDisplayName = $pdfUrl ? resolved_file_display_name($p['pdf_file_name'] ?? null, $pdfUrl) : '';
            $pdfDownloadable = $pdfUrl && str_starts_with($pdfUrl, '/');
          ?>
          <article class="content-card publication-card<?php echo $hasImage ? ' has-media' : ' no-media'; ?>">
            <?php if ($hasImage): ?>
              <div class="content-card__media publication-card__media">
                <img src="<?php echo esc($p['image']); ?>" alt="<?php echo esc($p['title']); ?>">
                <?php if ($hasCaption): ?>
                  <div class="content-card__caption"><?php echo esc($p['caption']); ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="content-card__body">
              <div class="content-card__header">
                <div>
                  <span class="content-card__tag">Publication</span>
                  <h3 class="content-card__title"><?php echo esc($p['title']); ?></h3>
                </div>
                <?php if ($hasYear): ?>
                  <div class="content-card__meta-pill"><?php echo esc($p['year']); ?></div>
                <?php endif; ?>
              </div>

              <?php if ($hasAuthors || $hasJournal || $hasDoi): ?>
                <div class="content-card__details">
                  <?php if ($hasAuthors): ?>
                    <div class="detail-chip"><span>Authors</span><strong><?php echo esc($p['authors']); ?></strong></div>
                  <?php endif; ?>
                  <?php if ($hasJournal): ?>
                    <div class="detail-chip"><span>Journal</span><strong><?php echo esc($p['journal']); ?></strong></div>
                  <?php endif; ?>
                  <?php if ($hasDoi): ?>
                    <div class="detail-chip"><span>DOI</span><strong><?php echo esc($p['doi']); ?></strong></div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <?php if ($hasAbstract): ?>
                <div class="content-card__text expandable" data-collapsed-lines="4">
                  <p><?php echo nl2br(esc($p['abstract'])); ?></p>
                </div>
                <button class="see-more-toggle btn btn-secondary" aria-expanded="false"><span class="toggle-label">See More</span></button>
              <?php endif; ?>

              <?php if ($publicationUrl): ?>
                <div class="content-card__actions">
                  <a class="btn btn-primary" href="<?php echo esc($publicationUrl); ?>" target="_blank" rel="noopener">View Publication</a>
                </div>
              <?php endif; ?>

              <?php if ($pdfUrl): ?>
                <div class="asset-grid asset-grid--single">
                  <article class="asset-card asset-card--<?php echo esc($pdfKind); ?>">
                    <div class="asset-card__row">
                      <div class="asset-card__icon"><?php echo file_icon_svg(); ?></div>
                      <div class="asset-card__info">
                        <div class="asset-card__name" title="<?php echo esc($pdfDisplayName); ?>"><?php echo esc(truncate_filename_for_display($pdfDisplayName)); ?></div>
                        <div class="asset-card__meta">
                          <span class="asset-card__type"><?php echo esc($pdfLabel); ?></span>
                          <span class="asset-card__ext"><?php echo esc(strtoupper(file_extension_from_path($pdfUrl))); ?></span>
                        </div>
                      </div>
                    </div>
                    <div class="asset-card__actions">
                      <a class="btn btn-secondary" href="<?php echo esc($pdfUrl); ?>" target="_blank" rel="noopener">View</a>
                      <a class="btn btn-primary" href="<?php echo esc($pdfUrl); ?>" <?php echo $pdfDownloadable ? 'download' : 'target="_blank" rel="noopener"'; ?>>Download</a>
                    </div>
                  </article>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
