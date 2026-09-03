<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
if (strpos($currentPath, '/admin') === 0) {
    return;
}
?>
<header class="site-header">
  <div class="container header-inner">
    <div class="brand">
      <a href="/index.php" class="brand-link">
        <span class="brand-badge" aria-hidden="true">A</span>
        <span class="brand-name">Dr. Agnes Kapinga</span>
      </a>
    </div>

    <nav class="nav-desktop">
      <ul>
        <li><a href="/index.php">Home</a></li>
        <li><a href="/pages/resume.php">Resume</a></li>
        <li><a href="/pages/lecturer.php">Lecturer</a></li>
        <li><a href="/pages/research.php">Research &amp; Publications</a></li>
        <li><a href="/pages/projects.php">Projects</a></li>
        <li><a href="/pages/consultancy.php">Consultancy</a></li>
        <li><a href="/pages/contact.php">Contact</a></li>
      </ul>
    </nav>

    <button class="hamburger" id="hamburger" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <nav class="mobile-panel" id="mobilePanel" aria-hidden="true">
    <button class="mobile-close" id="mobileClose" aria-label="Close menu">✕</button>
    <ul>
      <li><a href="/index.php">Home</a></li>
      <li><a href="/pages/resume.php">Resume</a></li>
      <li><a href="/pages/lecturer.php">Lecturer</a></li>
      <li><a href="/pages/research.php">Research &amp; Publications</a></li>
      <li><a href="/pages/projects.php">Projects</a></li>
      <li><a href="/pages/consultancy.php">Consultancy</a></li>
      <li><a href="/pages/contact.php">Contact</a></li>
    </ul>
  </nav>
</header>

