document.addEventListener('DOMContentLoaded', function(){
  var hamburger = document.getElementById('hamburger');
  var mobilePanel = document.getElementById('mobilePanel');
  var mobileClose = document.getElementById('mobileClose');

  function openPanel(){
    mobilePanel.classList.add('open');
    mobilePanel.setAttribute('aria-hidden','false');
    document.body.style.overflow = 'hidden';
  }
  function closePanel(){
    mobilePanel.classList.remove('open');
    mobilePanel.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }

  if(hamburger) hamburger.addEventListener('click', openPanel);
  if(mobileClose) mobileClose.addEventListener('click', closePanel);

  // Close when a mobile link is clicked
  var mobileLinks = mobilePanel ? mobilePanel.querySelectorAll('a') : [];
  mobileLinks.forEach(function(a){ a.addEventListener('click', closePanel); });

  // Close on Escape
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closePanel(); });

  // Admin star easter-spot (hidden) - placeholder hook
  var adminStar = document.getElementById('adminStar');
  if(adminStar){
    adminStar.addEventListener('click', function(){
      var modal = document.getElementById('adminModal');
      if(modal){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        // focus username
        var u = modal.querySelector('input[name="username"]'); if(u) u.focus();
      }
    });
  }
  var adminModal = document.getElementById('adminModal');
  var adminModalClose = document.getElementById('adminModalClose');
  if(adminModalClose) adminModalClose.addEventListener('click', function(){
    adminModal.classList.remove('open');
    adminModal.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  });

  // Close admin modal when clicking outside panel
  if(adminModal){
    adminModal.addEventListener('click', function(e){
      if(e.target === adminModal){
        adminModal.classList.remove('open');
        adminModal.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
      }
    });
  }

  // Close admin modal on Escape
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && adminModal && adminModal.classList.contains('open')){ adminModal.classList.remove('open'); adminModal.setAttribute('aria-hidden','true'); document.body.style.overflow = ''; } });

  // See More / See Less reusable component.
  // The toggle button only becomes visible when the associated text
  // actually overflows its collapsed height - short content never shows
  // a See More / See Less control.
  function setupExpandables(){
    var expandables = document.querySelectorAll('.expandable');
    expandables.forEach(function(el){
      if(!el.classList.contains('expanded') && !el.classList.contains('collapsed')){
        el.classList.add('collapsed');
      }
    });

    function findToggleFor(el){
      var next = el.nextElementSibling;
      return (next && next.classList.contains('see-more-toggle')) ? next : null;
    }

    function evaluateOverflow(){
      document.querySelectorAll('.expandable').forEach(function(el){
        var toggle = findToggleFor(el);
        if(!toggle) return;

        // Never re-measure content the user has already expanded.
        if(el.classList.contains('expanded')) return;

        var overflowing = el.scrollHeight > el.clientHeight + 2;
        toggle.classList.toggle('is-visible', overflowing);
        if(!overflowing){
          toggle.setAttribute('aria-hidden', 'true');
          toggle.setAttribute('tabindex', '-1');
        } else {
          toggle.removeAttribute('aria-hidden');
          toggle.removeAttribute('tabindex');
        }
      });
    }

    var toggles = document.querySelectorAll('.see-more-toggle');
    toggles.forEach(function(btn){
      btn.addEventListener('click', function(){
        var target = btn.previousElementSibling;
        if(!target) return;
        var expanded = target.classList.toggle('expanded');
        target.classList.toggle('collapsed', !expanded);
        var label = btn.querySelector('.toggle-label');
        if(label){
          label.textContent = expanded ? 'See Less' : 'See More';
        } else {
          btn.textContent = expanded ? 'See Less' : 'See More';
        }
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      });
    });

    // Measure once immediately, then again once fonts finish loading
    // (web fonts can reflow text and change whether it actually overflows).
    evaluateOverflow();
    if(document.fonts && document.fonts.ready){
      document.fonts.ready.then(evaluateOverflow).catch(function(){});
    }
    window.addEventListener('load', evaluateOverflow);

    var resizeTimer;
    window.addEventListener('resize', function(){
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(evaluateOverflow, 200);
    });
  }
  setupExpandables();
});
