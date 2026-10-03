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
            $videoId = $isYT ? youtube_video_id($post['youtube_url']) : '';
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
                <!-- Fullscreen button for video -->
                <button
                  class="sl-fullscreen-btn"
                  aria-label="View fullscreen"
                  data-type="video"
                  data-videoid="<?php echo esc($videoId); ?>"
                  data-title="<?php echo esc($post['title'] ?? ''); ?>"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                </button>
              </div>
            <?php elseif ($isImage && has_text($post['image_file'])): ?>
              <div class="sl-card__media sl-card__media--photo">
                <img
                  src="<?php echo esc($post['image_file']); ?>"
                  alt="<?php echo esc($post['title'] ?? 'Social life photo'); ?>"
                  loading="lazy"
                >
                <!-- Fullscreen button for photo -->
                <button
                  class="sl-fullscreen-btn"
                  aria-label="View fullscreen"
                  data-type="image"
                  data-src="<?php echo esc($post['image_file']); ?>"
                  data-title="<?php echo esc($post['title'] ?? ''); ?>"
                  data-caption="<?php echo esc($post['caption'] ?? ''); ?>"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                </button>
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

<!-- ── Lightbox / Fullscreen Modal ── -->
<div class="sl-lightbox" id="slLightbox" aria-modal="true" role="dialog" aria-label="Fullscreen view" hidden>
  <div class="sl-lightbox__backdrop" id="slLightboxBackdrop"></div>
  <button class="sl-lightbox__close" id="slLightboxClose" aria-label="Close fullscreen">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
  </button>
  <div class="sl-lightbox__inner">
    <!-- Image view -->
    <div class="sl-lightbox__img-wrap" id="slLbImgWrap">
      <img src="" alt="" id="slLbImg">
    </div>
    <!-- Video view -->
    <div class="sl-lightbox__video-wrap" id="slLbVideoWrap">
      <div class="sl-lightbox__iframe-wrap">
        <iframe id="slLbIframe" src="" frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen></iframe>
      </div>
    </div>
    <!-- Caption -->
    <div class="sl-lightbox__caption" id="slLbCaption">
      <h3 id="slLbTitle"></h3>
      <p id="slLbText"></p>
    </div>
  </div>
</div>

<script>
(function () {
  var lightbox   = document.getElementById('slLightbox');
  var backdrop   = document.getElementById('slLightboxBackdrop');
  var closeBtn   = document.getElementById('slLightboxClose');
  var imgWrap    = document.getElementById('slLbImgWrap');
  var videoWrap  = document.getElementById('slLbVideoWrap');
  var img        = document.getElementById('slLbImg');
  var iframe     = document.getElementById('slLbIframe');
  var lbTitle    = document.getElementById('slLbTitle');
  var lbText     = document.getElementById('slLbText');
  var lbCaption  = document.getElementById('slLbCaption');

  function openLightbox(btn) {
    var type    = btn.dataset.type;
    var title   = btn.dataset.title || '';
    var caption = btn.dataset.caption || '';

    // Reset both panels
    imgWrap.style.display   = 'none';
    videoWrap.style.display = 'none';
    iframe.src = '';

    if (type === 'image') {
      img.src = btn.dataset.src;
      img.alt = title;
      imgWrap.style.display = 'flex';
    } else if (type === 'video') {
      var vid = btn.dataset.videoid;
      iframe.src = 'https://www.youtube.com/embed/' + vid + '?autoplay=1&rel=0';
      videoWrap.style.display = 'flex';
    }

    // Caption
    lbTitle.textContent = title;
    lbText.textContent  = caption;
    lbCaption.style.display = (title || caption) ? 'block' : 'none';

    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';

    // Focus close button for accessibility
    setTimeout(function () { closeBtn.focus(); }, 50);
  }

  function closeLightbox() {
    lightbox.hidden = true;
    iframe.src = ''; // stop video playback
    document.body.style.overflow = '';
  }

  // Attach click to all fullscreen buttons
  document.querySelectorAll('.sl-fullscreen-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      openLightbox(btn);
    });
  });

  // Close on backdrop click
  backdrop.addEventListener('click', closeLightbox);

  // Close button
  closeBtn.addEventListener('click', closeLightbox);

  // Close on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !lightbox.hidden) closeLightbox();
  });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
