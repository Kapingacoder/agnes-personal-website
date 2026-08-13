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
      // No visible text. This can be used to open admin login by devs.
      console.log('admin star clicked');
    });
  }
});
