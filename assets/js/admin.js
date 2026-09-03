document.addEventListener('DOMContentLoaded', function () {
  var sidebar = document.getElementById('adminSidebar');
  var overlay = document.getElementById('adminOverlay');
  var menuBtn = document.getElementById('adminMenuBtn');
  var closeBtn = document.getElementById('adminSidebarClose');

  function openSidebar() {
    if (!sidebar || !overlay) return;
    sidebar.classList.add('is-open');
    overlay.hidden = false;
    overlay.classList.add('is-visible');
    document.body.style.overflow = 'hidden';
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    if (!sidebar || !overlay) return;
    sidebar.classList.remove('is-open');
    overlay.classList.remove('is-visible');
    document.body.style.overflow = '';
    if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
    window.setTimeout(function () {
      if (!sidebar.classList.contains('is-open')) overlay.hidden = true;
    }, 250);
  }

  if (menuBtn) menuBtn.addEventListener('click', openSidebar);
  if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
  if (overlay) overlay.addEventListener('click', closeSidebar);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
      closeSidebar();
    }
  });

  // Close the drawer automatically if the viewport grows back to desktop size.
  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (window.innerWidth > 1080) closeSidebar();
    }, 150);
  });
});
