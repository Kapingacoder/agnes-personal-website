
<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
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
        <a class="btn btn-primary" href="/database/agnes_cv.pdf" download>Download CV</a>
        <a class="btn btn-secondary" href="/pages/contact.php">Contact</a>
      </div>

      <div class="hero-social">
        <a href="#" aria-label="Twitter" class="social-icon">
          <!-- Twitter SVG -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 5.92c-.63.28-1.3.47-2 .56.72-.43 1.27-1.11 1.53-1.92-.67.4-1.42.7-2.22.86A3.48 3.48 0 0015.5 4c-1.92 0-3.48 1.6-3.48 3.57 0 .28.03.56.09.82-2.9-.15-5.48-1.6-7.21-3.8-.3.52-.47 1.12-.47 1.76 0 1.22.9 2.24 2.14 2.54-.53-.02-1.03-.17-1.47-.42v.04c0 1.7 1.22 3.12 2.85 3.44-.3.08-.62.12-.95.12-.23 0-.45-.02-.66-.06.45 1.44 1.77 2.5 3.32 2.53A6.98 6.98 0 012 19.54 9.86 9.86 0 006.29 21c7.55 0 11.68-6.6 11.68-12.32 0-.19 0-.37-.01-.56.8-.6 1.5-1.36 2.05-2.23-.73.33-1.5.55-2.3.65z" fill="currentColor"/></svg>
        </a>
        <a href="#" aria-label="LinkedIn" class="social-icon">
          <!-- LinkedIn SVG -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.98 3.5a2.5 2.5 0 11.02 0zM3 8.98h4v12H3v-12zM9 8.98h3.84v1.64h.05c.54-1.02 1.86-2.1 3.83-2.1 4.1 0 4.86 2.7 4.86 6.2v7.26h-4v-6.44c0-1.54-.03-3.52-2.15-3.52-2.16 0-2.49 1.68-2.49 3.41v6.55H9v-12z" fill="currentColor"/></svg>
        </a>
        <a href="#" aria-label="ResearchGate" class="social-icon">
          <!-- Globe/Research SVG -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 2a8 8 0 015.29 13.7A9.97 9.97 0 0112 4zM4.71 6.29A8 8 0 0112 4v16a8 8 0 01-7.29-13.71z" fill="currentColor"/></svg>
        </a>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php';
