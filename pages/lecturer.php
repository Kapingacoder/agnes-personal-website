<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_table_columns($pdo, 'lecturers', [
    'caption' => 'TEXT',
    'date' => 'DATE',
    'primary_file' => 'TEXT',
    'secondary_file' => 'TEXT',
    'video_file' => 'TEXT',
    'primary_file_name' => 'TEXT',
    'secondary_file_name' => 'TEXT',
    'youtube_url' => 'VARCHAR(1000) DEFAULT NULL',
]);

$stmt = $pdo->prepare('SELECT id, title, description, image, caption, date, primary_file, secondary_file, video_file, primary_file_name, secondary_file_name, youtube_url FROM lecturers ORDER BY date DESC, created_at DESC');
$stmt->execute();
$lecturers = $stmt->fetchAll();
?>

<main class="page content-page content-page--lecturers">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Academic Profiles</span>
      <h2 class="page-hero__title">Lecturer Highlights</h2>
      <p class="page-hero__text">Explore lecturer profiles, documents, presentations, videos, and supporting materials in a clean, elegant layout that only shows the content actually provided.</p>
    </header>

    <section class="content-grid content-grid--stacked lecturers-list">
      <?php if (!$lecturers): ?>
        <div class="empty-state-card">
          <h3>No lecturer entries yet</h3>
          <p>Lecturer profiles added by the administrator will appear here automatically.</p>
        </div>
      <?php else: ?>
        <?php foreach ($lecturers as $l): ?>
          <?php
            $hasImage = has_text($l['image']);
            $hasDescription = has_text($l['description']);
            $displayDate = format_display_date($l['date']);
            $youtubeEmbed = youtube_embed_url($l['youtube_url'] ?? '');
            $resources = [];
            foreach (['primary_file' => 'primary_file_name', 'secondary_file' => 'secondary_file_name'] as $resourceKey => $nameKey) {
                if (has_text($l[$resourceKey] ?? null)) {
                    $resources[] = [
                        'path' => $l[$resourceKey],
                        'kind' => file_kind_from_path($l[$resourceKey]),
                        'label' => file_label_from_path($l[$resourceKey]),
                        'name' => resolved_file_display_name($l[$nameKey] ?? null, $l[$resourceKey]),
                    ];
                }
            }
            $hasVideoFile = has_text($l['video_file'] ?? null);
            $hasCaption = ($hasImage || $hasVideoFile || $youtubeEmbed || !empty($resources)) && has_text($l['caption']);
          ?>
          <article class="content-card lecturer-card<?php echo $hasImage ? ' has-media' : ' no-media'; ?>">
            <?php if ($hasImage): ?>
              <div class="content-card__media lecturer-card__media">
                <img src="<?php echo esc($l['image']); ?>" alt="<?php echo esc($l['title']); ?>">
              </div>
            <?php endif; ?>

            <div class="content-card__body">
              <div class="content-card__header">
                <div>
                  <span class="content-card__tag">Lecturer</span>
                  <h3 class="content-card__title"><?php echo esc($l['title']); ?></h3>
                </div>
                <?php if ($displayDate !== ''): ?>
                  <div class="content-card__meta-pill"><?php echo esc($displayDate); ?></div>
                <?php endif; ?>
              </div>

              <?php if ($hasDescription): ?>
                <div class="content-card__text expandable" data-collapsed-lines="4">
                  <p><?php echo nl2br(esc($l['description'])); ?></p>
                </div>
                <button class="see-more-toggle btn btn-secondary" aria-expanded="false"><span class="toggle-label">See More</span></button>
              <?php endif; ?>

              <?php if ($hasVideoFile): ?>
                <div class="media-showcase media-showcase--single">
                  <div class="video-responsive content-card__embed">
                    <video controls preload="metadata" playsinline>
                      <source src="<?php echo esc($l['video_file']); ?>">
                      Your browser does not support the video tag.
                    </video>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($youtubeEmbed): ?>
                <div class="content-card__embed video-responsive">
                  <iframe
                    src="<?php echo esc($youtubeEmbed); ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                    title="<?php echo esc($l['title']); ?>"
                  ></iframe>
                </div>
              <?php endif; ?>

              <?php if (!empty($resources)): ?>
                <div class="asset-grid">
                  <?php foreach ($resources as $resource): ?>
                    <article class="asset-card asset-card--<?php echo esc($resource['kind']); ?>">
                      <div class="asset-card__row">
                        <?php if ($resource['kind'] === 'image'): ?>
                          <div class="asset-card__thumb">
                            <img src="<?php echo esc($resource['path']); ?>" alt="<?php echo esc($resource['name']); ?>">
                          </div>
                        <?php else: ?>
                          <div class="asset-card__icon"><?php echo file_icon_svg(); ?></div>
                        <?php endif; ?>
                        <div class="asset-card__info">
                          <div class="asset-card__name" title="<?php echo esc($resource['name']); ?>"><?php echo esc(truncate_filename_for_display($resource['name'])); ?></div>
                          <div class="asset-card__meta">
                            <span class="asset-card__type"><?php echo esc($resource['label']); ?></span>
                            <span class="asset-card__ext"><?php echo esc(strtoupper(file_extension_from_path($resource['path']))); ?></span>
                          </div>
                        </div>
                      </div>

                      <div class="asset-card__actions">
                        <a class="btn btn-secondary" href="<?php echo esc($resource['path']); ?>" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-primary" href="<?php echo esc($resource['path']); ?>" download>Download</a>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if ($hasCaption): ?>
                <div class="content-card__caption content-card__caption--standalone"><?php echo esc($l['caption']); ?></div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
