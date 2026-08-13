  <footer class="site-footer">
    <div class="container">
      <p>&copy; <?php echo date('Y'); ?> Dr. Agnes Kapinga</p>
      <button id="adminStar" class="admin-star" aria-hidden="true">★</button>
    </div>
  </footer>
  <!-- Admin login modal (hidden entry point) -->
  <div id="adminModal" class="admin-modal" aria-hidden="true">
    <div class="admin-modal-panel">
      <button id="adminModalClose" class="admin-modal-close" aria-label="Close">✕</button>
      <h3>Administrator Login</h3>
      <form id="adminLoginForm" method="post" action="/admin/login.php">
        <label>Username
          <input name="username" type="text" autocomplete="username" required>
        </label>
        <label>Password
          <input name="password" type="password" autocomplete="current-password" required>
        </label>
        <div class="admin-form-actions">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/assets/js/script.js"></script>
  </div><!-- #site-root -->
</body>
</html>
