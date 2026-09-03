<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

ensure_table_columns($pdo, 'consultancy_videos', [
    'caption' => 'TEXT',
    'date' => 'DATE',
    'resource_file' => 'TEXT',
    'supporting_file' => 'TEXT',
    'video_file' => 'TEXT',
    'resource_file_name' => 'TEXT',
    'supporting_file_name' => 'TEXT',
]);

$stmt = $pdo->prepare('SELECT id, title, description, youtube_url, thumbnail, caption, date, resource_file, supporting_file, video_file, resource_file_name, supporting_file_name FROM consultancy_videos ORDER BY date DESC, created_at DESC');
$stmt->execute();
$videos = $stmt->fetchAll();
?>

<main class="page content-page content-page--consultancy">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Professional Engagement</span>
      <h2 class="page-hero__title">Consultancy Showcase</h2>
      <p class="page-hero__text">Consultancy work is presented in a refined, responsive experience with videos, documents, slides, images, and YouTube media shown only when actually uploaded.</p>
    </header>

    <section class="content-grid content-grid--stacked videos-list">
      <?php if (!$videos): ?>
        <div class="empty-state-card">
          <h3>No consultancy items yet</h3>
          <p>Consultancy content added by the administrator will appear here automatically.</p>
        </div>
      <?php else: ?>
        <?php foreach ($videos as $v): ?>
          <?php
            $hasThumbnail = has_text($v['thumbnail']);
            $hasDescription = has_text($v['description']);
            $displayDate = format_display_date($v['date']);
            $embed = youtube_embed_url($v['youtube_url']);
            $hasLocalVideo = has_text($v['video_file'] ?? null);
            $resources = [];
            foreach (['resource_file' => 'resource_file_name', 'supporting_file' => 'supporting_file_name'] as $resourceKey => $nameKey) {
                if (has_text($v[$resourceKey] ?? null)) {
                    $resources[] = [
                        'path' => $v[$resourceKey],
                        'kind' => file_kind_from_path($v[$resourceKey]),
                        'label' => file_label_from_path($v[$resourceKey]),
                        'name' => resolved_file_display_name($v[$nameKey] ?? null, $v[$resourceKey]),
                    ];
                }
            }
            $hasCaption = ($hasThumbnail || $embed || $hasLocalVideo || !empty($resources)) && has_text($v['caption']);
          ?>
          <article class="content-card consultancy-card">
            <div class="content-card__body content-card__body--wide">
              <div class="content-card__header">
                <div>
                  <span class="content-card__tag">Consultancy</span>
                  <h3 class="content-card__title"><?php echo esc($v['title']); ?></h3>
                </div>
                <?php if ($displayDate !== ''): ?>
                  <div class="content-card__meta-pill"><?php echo esc($displayDate); ?></div>
                <?php endif; ?>
              </div>

              <?php if ($embed || $hasLocalVideo || $hasThumbnail): ?>
                <div class="consultancy-media-wrap<?php echo ($embed || $hasLocalVideo) ? ' has-video' : ' image-only'; ?>">
                  <?php if ($embed): ?>
                    <div class="video-responsive content-card__embed">
                      <iframe src="<?php echo esc($embed); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                  <?php elseif ($hasLocalVideo): ?>
                    <div class="video-responsive content-card__embed video-responsive--native">
                      <video controls preload="metadata" playsinline poster="<?php echo $hasThumbnail ? esc($v['thumbnail']) : ''; ?>">
                        <source src="<?php echo esc($v['video_file']); ?>">
                        Your browser does not support the video tag.
                      </video>
                    </div>
                  <?php elseif ($hasThumbnail): ?>
                    <div class="content-card__media consultancy-card__media">
                      <img src="<?php echo esc($v['thumbnail']); ?>" alt="<?php echo esc($v['title']); ?>">
                    </div>
                  <?php endif; ?>

                  <?php if (!$embed && !$hasLocalVideo && $hasThumbnail): ?>
                    <div class="consultancy-image-note">Media preview</div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <?php if ($hasDescription): ?>
                <div class="content-card__text expandable" data-collapsed-lines="4">
                  <p><?php echo nl2br(esc($v['description'])); ?></p>
                </div>
                <button class="see-more-toggle btn btn-secondary" aria-expanded="false"><span class="toggle-label">See More</span></button>
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
                <div class="content-card__caption content-card__caption--standalone"><?php echo esc($v['caption']); ?></div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
