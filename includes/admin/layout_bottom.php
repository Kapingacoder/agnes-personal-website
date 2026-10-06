      </main>
    </div>
  </div>
  <script src="/assets/js/admin.js"></script>

  <!-- ── Admin session auto-logout after 10 minutes of inactivity ── -->
  <script>
  (function () {
    var TIMEOUT_MS   = 10 * 60 * 1000; // 10 minutes
    var WARNING_MS   = 60 * 1000;       // warn 1 minute before logout
    var LOGOUT_URL   = '/admin/logout.php';

    var warningShown = false;
    var lastActivity = Date.now();

    // Reset timer on any user activity
    ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'].forEach(function (evt) {
      document.addEventListener(evt, function () {
        lastActivity = Date.now();
        if (warningShown) hideWarning();
      }, { passive: true });
    });

    // Build warning banner (hidden by default)
    var banner = document.createElement('div');
    banner.id = 'session-warning';
    banner.innerHTML =
      '<span id="session-warning-text">Session inakwisha — utarudishwa login baada ya <strong id="session-countdown">60</strong>s bila shughuli.</span>' +
      '<button id="session-stay" type="button">Endelea</button>';
    banner.style.cssText = [
      'position:fixed', 'bottom:24px', 'left:50%', 'transform:translateX(-50%) translateY(120px)',
      'background:#1e293b', 'border:1px solid rgba(239,68,68,.5)', 'border-radius:14px',
      'padding:14px 20px', 'display:flex', 'align-items:center', 'gap:16px',
      'color:#fecaca', 'font-size:.88rem', 'font-family:inherit',
      'box-shadow:0 8px 32px rgba(0,0,0,.5)', 'z-index:99999',
      'transition:transform .3s ease', 'white-space:nowrap',
    ].join(';');

    var stayBtn = null;

    document.addEventListener('DOMContentLoaded', function () {
      document.body.appendChild(banner);
      stayBtn = document.getElementById('session-stay');
      stayBtn.style.cssText = 'background:#3b82f6;color:#fff;border:none;border-radius:8px;padding:7px 16px;font-size:.82rem;font-weight:600;cursor:pointer;flex-shrink:0;';
      stayBtn.addEventListener('click', function () {
        lastActivity = Date.now();
        hideWarning();
        // Ping server to refresh session
        fetch('/admin/dashboard.php', { method: 'HEAD', credentials: 'same-origin' }).catch(function(){});
      });
    });

    function showWarning() {
      warningShown = true;
      banner.style.transform = 'translateX(-50%) translateY(0)';
    }

    function hideWarning() {
      warningShown = false;
      banner.style.transform = 'translateX(-50%) translateY(120px)';
    }

    function tick() {
      var idle = Date.now() - lastActivity;
      var remaining = TIMEOUT_MS - idle;

      if (remaining <= 0) {
        // Time is up — logout
        window.location.href = LOGOUT_URL;
        return;
      }

      if (remaining <= WARNING_MS) {
        // Show warning with countdown
        var secs = Math.ceil(remaining / 1000);
        var el = document.getElementById('session-countdown');
        if (el) el.textContent = secs;
        if (!warningShown) showWarning();
      }

      setTimeout(tick, 1000);
    }

    // Start ticker after DOM ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () { setTimeout(tick, 1000); });
    } else {
      setTimeout(tick, 1000);
    }
  })();
  </script>

</body>
</html>
