(function () {
  'use strict';

  if (typeof gsap === 'undefined') return;

  if (typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);
  }

  function init() {
    /* ─── HERO ENTRANCE ─────────────────────────────── */
    var heroContent = document.querySelector('.hero-content');
    var statsPanel = document.querySelector('.hero-stats-panel');
    var heroTl = gsap.timeline();

    /* ─── STATS COUNTER (defined early so timeline can call it) ── */
    var statEls = document.querySelectorAll('.hero-stat-number[data-target]');
    var counterStarted = false;

    function startCounters() {
      if (counterStarted) return;
      counterStarted = true;

      statEls.forEach(function (el, i) {
        var target = parseInt(el.getAttribute('data-target'));
        var suffix = el.getAttribute('data-suffix') || '';
        var obj = { val: 0 };

        gsap.to(obj, {
          val: target,
          duration: 2.2,
          delay: i * 0.18,
          ease: 'expo.out',
          onUpdate: function () {
            el.textContent = Math.floor(obj.val) + suffix;
          },
          onComplete: function () {
            el.textContent = target + suffix;
          },
        });
      });
    }

    if (heroContent) {
      var heroTag = heroContent.querySelector('.hero-tag');
      var heroH1 = heroContent.querySelector('h1');
      var heroP = heroContent.querySelector('p');
      var heroBtns = heroContent.querySelectorAll('.btn');

      gsap.set(heroContent, { opacity: 1 });
      gsap.set([heroTag, heroH1, heroP, heroBtns], { opacity: 0, y: 20 });
      if (statsPanel) {
        gsap.set(statsPanel, { opacity: 0, x: 30 });
      }

      heroTl.to(heroTag, { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' })
        .to(heroH1, { opacity: 1, y: 0, duration: 0.3, ease: 'power2.out' }, '-=0.15')
        .to(heroP, { opacity: 1, y: 0, duration: 0.25, ease: 'power2.out' }, '-=0.1')
        .to(heroBtns, { opacity: 1, y: 0, duration: 0.2, ease: 'power2.out', stagger: 0.05 }, '-=0.1');

      if (statsPanel && statEls.length) {
        heroTl.call(startCounters);
        heroTl.to(statsPanel, { opacity: 1, x: 0, duration: 0.5, ease: 'power3.out' }, '-=0.3');
      } else if (statsPanel) {
        heroTl.to(statsPanel, { opacity: 1, x: 0, duration: 0.5, ease: 'power3.out' }, '-=0.3');
      }
    }

    /* ─── FADE-UP SECTIONS (scroll trigger) ────────── */
    gsap.utils.toArray('.fade-up').forEach(function (el) {
      if (el.closest('.hero-content') || el.closest('.hero-stats-panel')) return;
      if (el.closest('.stagger-children')) return;

      ScrollTrigger.create({
        trigger: el,
        start: 'top 95%',
        once: true,
        onEnter: function () {
          gsap.to(el, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out', overwrite: 'auto' });
        },
      });
    });

    /* ─── SLIDE-LEFT ────────────────────────────────── */
    gsap.utils.toArray('.slide-left').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el,
        start: 'top 95%',
        once: true,
        onEnter: function () {
          gsap.to(el, { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out' });
        },
      });
    });

    /* ─── SLIDE-RIGHT ───────────────────────────────── */
    gsap.utils.toArray('.slide-right').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el,
        start: 'top 95%',
        once: true,
        onEnter: function () {
          gsap.to(el, { opacity: 1, x: 0, duration: 0.4, ease: 'power2.out' });
        },
      });
    });

    /* ─── SCALE-REVEAL ──────────────────────────────── */
    gsap.utils.toArray('.scale-reveal').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el,
        start: 'top 95%',
        once: true,
        onEnter: function () {
          gsap.to(el, { opacity: 1, scale: 1, duration: 0.4, ease: 'power2.out' });
        },
      });
    });

    /* ─── SECTION DIVIDER ───────────────────────────── */
    gsap.utils.toArray('.section-divider').forEach(function (el) {
      ScrollTrigger.create({
        trigger: el,
        start: 'top 95%',
        once: true,
        onEnter: function () {
          gsap.to(el, { scaleX: 1, duration: 0.5, ease: 'power2.inOut' });
        },
      });
    });

    /* ─── STAGGERED CHILDREN ────────────────────────── */
    document.querySelectorAll('.stagger-children').forEach(function (parent) {
      var children = parent.querySelectorAll('.fade-up');
      if (children.length) {
        ScrollTrigger.create({
          trigger: parent,
          start: 'top 93%',
          once: true,
          onEnter: function () {
            gsap.to(children, {
              opacity: 1,
              y: 0,
              duration: 0.35,
              ease: 'power2.out',
              stagger: 0.06,
              overwrite: 'auto',
            });
          },
        });
      }
    });

    if (ScrollTrigger) {
      ScrollTrigger.refresh();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
