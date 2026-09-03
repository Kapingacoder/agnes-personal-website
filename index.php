
<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Get admin's CV file
$site_config = require __DIR__ . '/config/site.php';
$cv_download_path = $site_config['fallback_cv_path']; // Default fallback

try {
    $stmt = $pdo->query('SELECT cv_file FROM admins WHERE cv_file IS NOT NULL LIMIT 1');
    $cv_result = $stmt->fetch();
    if ($cv_result && !empty($cv_result['cv_file'])) {
        $cv_download_path = $cv_result['cv_file'];
    }
} catch (Throwable $e) {
    // Keep fallback if database query fails
}
?>

<main class="hero">
  <?php $profile_img = '/assets/images/agnes.jpg'; ?>
  <div class="hero-inner container">
    <div class="hero-image">
      <div class="avatar">
        <img src="<?php echo esc($profile_img); ?>" alt="Dr. Agnes Kapinga">
      </div>
    </div>

    <div class="hero-content">
      <p class="hero-greeting">Hi, I'm</p>
      <h1 class="hero-name"><span class="hero-name-prefix">Dr.</span> <span class="hero-name-main">Agnes Kapinga</span></h1>
      <p class="hero-title">Lecturer, Researcher, and Consultant</p>

      <p class="hero-intro">I am a Lecturer and Researcher at the Tengeru Institute of Community Development in Arusha, Tanzania. My work focuses on climate resilience, environmental governance, social justice, and sustainable livelihoods.</p>

      <div class="hero-actions">
        <a class="btn btn-primary" href="<?php echo esc($cv_download_path); ?>" download="Agnes-Kapinga-CV.pdf">Download CV</a>
        <a class="btn btn-secondary" href="/pages/contact.php">Contact</a>
      </div>

      <div class="hero-social">
        <a href="#" aria-label="Facebook" class="social-icon">
          <!-- Facebook SVG -->
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S1.86 6.48 1.86 12.07c0 4.99 3.66 9.13 8.44 9.92v-7.02H8.08v-2.9h2.22V9.41c0-2.2 1.3-3.42 3.28-3.42.95 0 1.95.17 1.95.17v2.14h-1.1c-1.09 0-1.43.68-1.43 1.38v1.65h2.43l-.39 2.9h-2.04V22c4.78-.79 8.44-4.93 8.44-9.93z" fill="currentColor"/></svg>
        </a>
        <a href="#" aria-label="LinkedIn" class="social-icon">
          <!-- LinkedIn SVG -->
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.98 3.5a2.5 2.5 0 11.02 0zM3 8.98h4v12H3v-12zM9 8.98h3.84v1.64h.05c.54-1.02 1.86-2.1 3.83-2.1 4.1 0 4.86 2.7 4.86 6.2v7.26h-4v-6.44c0-1.54-.03-3.52-2.15-3.52-2.16 0-2.49 1.68-2.49 3.41v6.55H9v-12z" fill="currentColor"/></svg>
        </a>
        <a href="#" aria-label="Instagram" class="social-icon">
          <!-- Instagram SVG -->
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6.5A4.5 4.5 0 1016.5 13 4.5 4.5 0 0012 8.5zm6.5-3.8a1.2 1.2 0 11-1.2 1.2 1.2 1.2 0 011.2-1.2z" fill="currentColor"/></svg>
        </a>
        <a href="#" aria-label="ResearchGate" class="social-icon">
          <!-- Research/Globe SVG -->
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 2a8 8 0 015.29 13.7A9.97 9.97 0 0112 4zM4.71 6.29A8 8 0 0112 4v16a8 8 0 01-7.29-13.71z" fill="currentColor"/></svg>
        </a>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php';
