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
  });

  // See More / See Less reusable component
  function setupExpandables(){
    var expandables = document.querySelectorAll('.expandable');
    expandables.forEach(function(el){
      var collapsedLines = parseInt(el.getAttribute('data-collapsed-lines') || '4',10);
      // approximate line-height 1.1em, font-size ~16 -> use rem-based clamp
      if(!el.classList.contains('expanded') && !el.classList.contains('collapsed')){
        el.classList.add('collapsed');
      }
    });

    var toggles = document.querySelectorAll('.see-more-toggle');
    toggles.forEach(function(btn){
      btn.addEventListener('click', function(){
        var target = btn.previousElementSibling;
        if(!target) return;
        var expanded = target.classList.toggle('expanded');
        target.classList.toggle('collapsed', !expanded);
        btn.textContent = expanded ? 'See Less' : 'See More';
        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      });
    });
  }
  setupExpandables();
});
