<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_projects_table($pdo);
ensure_table_columns($pdo, 'projects', [
    'description' => 'TEXT', 'publication_date' => 'DATE', 'pdf_file' => 'TEXT',
    'pdf_file_name' => 'TEXT', 'cover_image' => 'TEXT', 'caption' => 'TEXT',
    'allow_download' => 'BOOLEAN DEFAULT TRUE', 'allow_print' => 'BOOLEAN DEFAULT TRUE',
    'viewer_enabled' => 'BOOLEAN DEFAULT TRUE',
]);

$stmt = $pdo->query('SELECT id, title, description, publication_date, pdf_file, pdf_file_name, cover_image, caption, allow_download, allow_print, viewer_enabled FROM projects ORDER BY publication_date DESC, created_at DESC');
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="page content-page content-page--projects">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Published Projects</span>
      <h2 class="page-hero__title">Projects</h2>
      <p class="page-hero__text">Read journals and project publications directly on this page.</p>
    </header>

    <section class="content-grid content-grid--stacked projects-list">
      <?php if (!$projects): ?>
        <div class="empty-state-card"><h3>No projects yet</h3><p>Project journals uploaded by the administrator will appear here.</p></div>
      <?php else: ?>
        <?php foreach ($projects as $project): ?>
          <?php
            $coverUrl = is_safe_local_path($project['cover_image']) ? $project['cover_image'] : safe_url($project['cover_image']);
            $hasCover = (bool)$coverUrl;
            $pdfUrl = is_safe_local_path($project['pdf_file']) ? $project['pdf_file'] : safe_url($project['pdf_file']);
            $date = format_display_date($project['publication_date']);
            $allowDownload = (int)$project['allow_download'] === 1;
            $allowPrint = (int)$project['allow_print'] === 1;
            $viewerEnabled = (int)$project['viewer_enabled'] === 1;
          ?>
          <article class="content-card project-card<?php echo $hasCover ? ' has-media' : ' no-media'; ?>">
            <?php if ($hasCover): ?>
              <div class="content-card__media project-card__media"><img src="<?php echo esc($coverUrl); ?>" alt="<?php echo esc($project['title']); ?>"><?php if (has_text($project['caption'])): ?><div class="content-card__caption"><?php echo esc($project['caption']); ?></div><?php endif; ?></div>
            <?php endif; ?>
            <div class="content-card__body">
              <div class="content-card__header"><div><span class="content-card__tag">Project Journal</span><h3 class="content-card__title"><?php echo esc($project['title']); ?></h3></div><?php if ($date): ?><div class="content-card__meta-pill"><?php echo esc($date); ?></div><?php endif; ?></div>
              <?php if (has_text($project['description'])): ?><div class="content-card__text"><p><?php echo nl2br(esc($project['description'])); ?></p></div><?php endif; ?>

              <?php if ($pdfUrl && $viewerEnabled): ?>
                <div class="journal-reader" data-pdf-url="<?php echo esc($pdfUrl); ?>" data-allow-print="<?php echo $allowPrint ? '1' : '0'; ?>">
                  <div class="journal-reader__toolbar">
                    <div class="journal-reader__group"><button type="button" class="journal-reader__button" data-action="prev">Previous</button><button type="button" class="journal-reader__button" data-action="next">Next</button></div>
                    <span class="journal-reader__counter" data-role="page-counter">Page 1 of 1</span>
                    <div class="journal-reader__group"><button type="button" class="journal-reader__button" data-action="zoom-out">-</button><button type="button" class="journal-reader__button" data-action="zoom-in">+</button><button type="button" class="journal-reader__button" data-action="fullscreen">Full screen</button><?php if ($allowPrint): ?><button type="button" class="journal-reader__button" data-action="print">Print</button><?php endif; ?><?php if ($allowDownload): ?><a class="journal-reader__button" href="<?php echo esc($pdfUrl); ?>" download>Download</a><?php endif; ?></div>
                  </div>
                  <div class="journal-reader__viewport is-loading"><canvas class="journal-reader__canvas"></canvas><div class="journal-reader__loading">Loading journal...</div></div>
                </div>
              <?php elseif ($pdfUrl): ?>
                <div class="content-card__actions"><a class="btn btn-primary" href="<?php echo esc($pdfUrl); ?>" target="_blank" rel="noopener">Open journal</a><?php if ($allowDownload): ?><a class="btn btn-secondary" href="<?php echo esc($pdfUrl); ?>" download>Download</a><?php endif; ?></div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<script src="/assets/vendor/pdfjs/pdf.min.js"></script>
<script src="/assets/js/projects-reader.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
