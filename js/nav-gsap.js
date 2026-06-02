(function () {
  'use strict';

  const links = document.querySelectorAll('.desktop-nav a:not(.active)');
  const accent = '#d4a843';
  const muted = '#a09080';

  links.forEach(function (link) {
    link.addEventListener('mouseenter', function () {
      gsap.to(link, {
        scale: 0.95,
        color: accent,
        duration: 0.35,
        ease: 'power2.out',
        overwrite: 'auto',
      });
    });

    link.addEventListener('mouseleave', function () {
      gsap.to(link, {
        scale: 1,
        color: muted,
        duration: 0.35,
        ease: 'power2.out',
        overwrite: 'auto',
      });
    });
  });
})();
