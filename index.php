
<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="hero">
  <div class="hero-inner container">
    <div class="hero-image">
      <img src="/assets/images/agnes.jpg" alt="Dr. Agnes Kapinga">
    </div>

    <div class="hero-content">
      <p class="hero-greeting">Hi, I'm</p>
      <h1 class="hero-name">Dr. Agnes Kapinga</h1>
      <p class="hero-title">Lecturer, Researcher, and Consultant</p>

      <p class="hero-intro">I am a Lecturer and Researcher at the Tengeru Institute of Community Development in Arusha, Tanzania. My work focuses on climate resilience, environmental governance, social justice, and sustainable livelihoods.</p>

      <div class="hero-actions">
        <a class="btn btn-primary" href="/database/agnes_cv.pdf" download>Download CV</a>
        <a class="btn btn-secondary" href="/pages/contact.php">Contact</a>
      </div>

      <div class="hero-social">
        <a href="#" aria-label="Twitter" class="social">Twitter</a>
        <a href="#" aria-label="LinkedIn" class="social">LinkedIn</a>
        <a href="#" aria-label="ResearchGate" class="social">ResearchGate</a>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php';
