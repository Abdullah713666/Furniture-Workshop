/**
 * particles.js — Antique Furniture Workshop
 * High-performance, zero-dependency canvas particle engine.
 *
 * Architecture:
 *  - Single <canvas> fixed behind all content (z-index:-1, pointer-events:none)
 *  - requestAnimationFrame loop; no DOM elements per particle
 *  - Mouse repulsion: ease-out proportional to proximity, strict radius
 *  - Friction damping on cursor exit / radius boundary
 */

(function AntiqueFurnitureParticles() {
  'use strict';

  /* ─── 1. CONFIGURATION ──────────────────────────────────────────────── */

  const CONFIG = {
    // Particle count range (resolved at init)
    minCount: 150,
    maxCount: 220,

    // Base drift speed (px/frame). Kept well below 0.5px as spec'd.
    driftSpeed: 0.12,

    // Mouse repulsion zone (px)
    influenceRadius: 135,

    // Force scale: how strongly a particle at the center is pushed (px/frame²)
    repelStrength: 1.4,

    // Damping factor applied each frame when particle is OUTSIDE radius or
    // mouse has left. Higher = snappier return to drift. 0.88 ≈ smooth.
    friction: 0.92,

    // Particle size range (px) — soft dots / light dashes
    minRadius:  1.2,
    maxRadius:  2.6,

    // Opacity range for each particle (randomised at birth)
    minOpacity: 0.10,
    maxOpacity: 0.55,

    // Site palette — warm amber / parchment / muted bronze tones
    // All match the dark-wood theme: bg-primary #1a1410, accent #d4a843
    palette: [
      '212, 168, 67',   // --accent golden amber
      '160, 144, 128',  // --text-secondary warm grey
      '245, 240, 232',  // --text-primary parchment (sparse)
      '112,  96,  80',  // --text-muted bronze
      '180, 140,  80',  // mid amber
      '120,  90,  55',  // dark wood
    ],

    // Bottom margin so particles thin out above the mobile nav bar
    bottomMargin: 70,

    // Minimum gap from viewport edges before particles are placed
    edgeMargin: 20,
  };

  /* ─── 2. CANVAS SETUP ───────────────────────────────────────────────── */

  const canvas = document.createElement('canvas');
  canvas.id = 'particle-bg';
  canvas.setAttribute('aria-hidden', 'true');
  canvas.style.cssText = [
    'position:fixed',
    'top:0',
    'left:0',
    'width:100%',
    'height:100%',
    'z-index:-1',
    'pointer-events:none',
    'will-change:transform',      // hint GPU compositing layer
  ].join(';');
  document.body.prepend(canvas);

  const ctx = canvas.getContext('2d', { alpha: true });

  /* ─── 3. STATE ──────────────────────────────────────────────────────── */

  let W = 0, H = 0;
  let particles = [];
  let mouse = { x: -9999, y: -9999, active: false };
  let rafId = null;

  /* ─── 4. MATH HELPERS ───────────────────────────────────────────────── */

  const rand    = (lo, hi) => lo + Math.random() * (hi - lo);
  const randInt = (lo, hi) => Math.floor(rand(lo, hi + 1));

  /**
   * Ease-out cubic — maps t∈[0,1] → smooth deceleration curve.
   * Used to scale repulsion force: gentle at radius edge, firm at center.
   */
  const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

  /* ─── 5. PARTICLE CLASS ─────────────────────────────────────────────── */

  class Particle {
    constructor() {
      this.reset(true);
    }

    /**
     * (Re)initialise position and intrinsic properties.
     * `initial` = true  → random screen position
     * `initial` = false → wrap from the opposite edge (seamless looping)
     */
    reset(initial = false) {
      if (initial) {
        this.x = rand(CONFIG.edgeMargin, W - CONFIG.edgeMargin);
        this.y = rand(CONFIG.edgeMargin, H - CONFIG.bottomMargin);
      }

      // Intrinsic drift vector — slow, random direction
      const angle = rand(0, Math.PI * 2);
      const speed = rand(CONFIG.driftSpeed * 0.5, CONFIG.driftSpeed);
      this.driftVx = Math.cos(angle) * speed;
      this.driftVy = Math.sin(angle) * speed;

      // Current velocity starts at drift
      this.vx = this.driftVx;
      this.vy = this.driftVy;

      // Visual properties — fixed at birth for performance
      this.r       = rand(CONFIG.minRadius, CONFIG.maxRadius);
      this.opacity = rand(CONFIG.minOpacity, CONFIG.maxOpacity);
      this.color   = CONFIG.palette[randInt(0, CONFIG.palette.length - 1)];

      // Slight opacity pulse for organic feel
      this.pulseSpeed  = rand(0.004, 0.012);
      this.pulseOffset = rand(0, Math.PI * 2);
      this.age         = 0;
    }

    update(now) {
      this.age++;

      /* Pulsing opacity */
      const pulse = Math.sin(now * this.pulseSpeed + this.pulseOffset) * 0.12;
      this.renderOpacity = Math.max(0, Math.min(1, this.opacity + pulse));

      /* ── Mouse repulsion physics ── */
      const dx = this.x - mouse.x;
      const dy = this.y - mouse.y;
      const distSq = dx * dx + dy * dy;
      const R = CONFIG.influenceRadius;

      if (mouse.active && distSq < R * R && distSq > 0.001) {
        const dist   = Math.sqrt(distSq);
        // Proximity ratio: 1 = right at cursor, 0 = at radius edge
        const ratio  = 1 - dist / R;
        // Ease-out so force is smooth (not linear / jerky)
        const force  = easeOutCubic(ratio) * CONFIG.repelStrength;

        const nx = dx / dist;   // unit vector away from cursor
        const ny = dy / dist;

        // Accelerate particle away from mouse
        this.vx += nx * force;
        this.vy += ny * force;
      } else {
        // Outside radius OR mouse inactive: apply friction toward drift velocity
        this.vx = this.vx * CONFIG.friction + this.driftVx * (1 - CONFIG.friction);
        this.vy = this.vy * CONFIG.friction + this.driftVy * (1 - CONFIG.friction);
      }

      /* Soft speed cap — prevents runaway velocity near cursor center */
      const speed = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
      if (speed > 6) {
        const s = 6 / speed;
        this.vx *= s;
        this.vy *= s;
      }

      /* Integrate position */
      this.x += this.vx;
      this.y += this.vy;

      /* Seamless wrapping — particle exits one edge, enters from opposite */
      if (this.x < -10)              this.x = W + 10;
      if (this.x > W + 10)           this.x = -10;
      if (this.y < -10)              this.y = H + 10;
      if (this.y > H + CONFIG.bottomMargin + 10) this.y = -10;
    }

    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${this.color},${this.renderOpacity.toFixed(3)})`;
      ctx.fill();
    }
  }

  /* ─── 6. INIT & RESIZE ──────────────────────────────────────────────── */

  function resize() {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    W = window.innerWidth;
    H = window.innerHeight;

    canvas.width  = W * dpr;
    canvas.height = H * dpr;
    ctx.scale(dpr, dpr);

    // Recount particles proportionally to viewport area
    const area    = W * H;
    const density = 0.000065; // particles per px²
    const target  = Math.min(
      CONFIG.maxCount,
      Math.max(CONFIG.minCount, Math.round(area * density))
    );

    // Adjust existing pool without full reset (preserves continuity)
    while (particles.length < target) {
      const p = new Particle();
      p.x = rand(0, W);
      p.y = rand(0, H - CONFIG.bottomMargin);
      particles.push(p);
    }
    if (particles.length > target) {
      particles.length = target;
    }
  }

  /* ─── 7. ANIMATION LOOP ─────────────────────────────────────────────── */

  function draw(now) {
    ctx.clearRect(0, 0, W, H);

    // Batch all particles in a single path group per color is more expensive
    // than individual arcs at this scale; keep simple per-particle draw.
    for (let i = 0; i < particles.length; i++) {
      particles[i].update(now);
      particles[i].draw();
    }

    rafId = requestAnimationFrame(draw);
  }

  /* ─── 8. EVENT LISTENERS ────────────────────────────────────────────── */

  window.addEventListener('mousemove', e => {
    mouse.x = e.clientX;
    mouse.y = e.clientY;
    mouse.active = true;
  }, { passive: true });

  window.addEventListener('mouseleave', () => {
    mouse.active = false;
    mouse.x = -9999;
    mouse.y = -9999;
  }, { passive: true });

  // Debounced resize — avoids layout thrashing on rapid resize drags
  let resizeTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(resize, 120);
  }, { passive: true });

  // Touch: treat finger as a soft repel point too
  window.addEventListener('touchmove', e => {
    if (e.touches.length > 0) {
      mouse.x = e.touches[0].clientX;
      mouse.y = e.touches[0].clientY;
      mouse.active = true;
    }
  }, { passive: true });

  window.addEventListener('touchend', () => {
    mouse.active = false;
  }, { passive: true });

  /* ─── 9. VISIBILITY API — pause when tab is hidden ──────────────────── */

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      cancelAnimationFrame(rafId);
      rafId = null;
    } else {
      if (!rafId) rafId = requestAnimationFrame(draw);
    }
  });

  /* ─── 10. BOOTSTRAP ─────────────────────────────────────────────────── */

  function init() {
    resize();
    rafId = requestAnimationFrame(draw);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
