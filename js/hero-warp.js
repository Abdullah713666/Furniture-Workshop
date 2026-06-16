(function () {
  'use strict';

  var heroBg = document.querySelector('.hero-bg');
  var hero = document.querySelector('.hero');
  if (!heroBg || !hero || typeof THREE === 'undefined') return;

  var W = hero.offsetWidth;
  var H = hero.offsetHeight;

  /* ---- constants (match hero-shapes.js) ---- */
  var DEFORM_RADIUS = 1.8;
  var BULGE_STRENGTH = 0.5;
  var SPRING_BACK = 0.08;
  var PLANE_SEGMENTS = 64;

  /* ---- Three.js setup ---- */
  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(45, W / H, 0.1, 100);
  camera.position.set(0, 0, 8);
  camera.lookAt(0, 0, 0);

  var renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(W, H);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  heroBg.appendChild(renderer.domElement);

  var aspect = W / H;
  var halfHScene = 8 * Math.tan((45 * Math.PI) / 360);
  var halfWScene = halfHScene * aspect;

  /* ---- plane that fills the viewport ---- */
  var planeW = halfWScene * 2;
  var planeH = halfHScene * 2;
  var geometry = new THREE.PlaneGeometry(planeW, planeH, PLANE_SEGMENTS, PLANE_SEGMENTS);
  var origPositions = Float32Array.from(geometry.attributes.position.array);

  /* ---- load hero image as texture ---- */
  var texture = new THREE.TextureLoader().load('images/hero-bg.jpg');
  texture.minFilter = THREE.LinearFilter;
  texture.magFilter = THREE.LinearFilter;

  /* ---- shader material ---- */
  var vertexShader = [
    'uniform vec2 uMouse;',
    'uniform float uDeformRadius;',
    'uniform float uBulgeStrength;',
    'varying vec2 vUv;',
    'float hexDist(vec2 p) {',
    '  p = abs(p);',
    '  return max(p.x * 0.866025 + p.y * 0.5, p.y);',
    '}',
    'void main() {',
    '  vUv = uv;',
    '  vec3 pos = position;',
    '  vec2 d = pos.xy - uMouse;',
    '  float dist = hexDist(d);',
    '  if (dist < uDeformRadius && dist > 0.01) {',
    '    float force = (1.0 - dist / uDeformRadius) * uBulgeStrength;',
    '    pos.z += force;',
    '  }',
    '  gl_Position = projectionMatrix * modelViewMatrix * vec4(pos, 1.0);',
    '}'
  ].join('\n');

  var fragmentShader = [
    'uniform sampler2D uTexture;',
    'varying vec2 vUv;',
    'void main() {',
    '  gl_FragColor = texture2D(uTexture, vUv);',
    '}'
  ].join('\n');

  var material = new THREE.ShaderMaterial({
    uniforms: {
      uTexture: { value: texture },
      uMouse: { value: new THREE.Vector2(Infinity, Infinity) },
      uDeformRadius: { value: DEFORM_RADIUS },
      uBulgeStrength: { value: BULGE_STRENGTH }
    },
    vertexShader: vertexShader,
    fragmentShader: fragmentShader,
    transparent: true,
    side: THREE.DoubleSide
  });

  var plane = new THREE.Mesh(geometry, material);
  plane.renderOrder = 0;
  scene.add(plane);

  /* ---- mouse tracking ---- */
  var mouse = { x: Infinity, y: Infinity, active: false };

  function toScene(clientX, clientY) {
    var rect = hero.getBoundingClientRect();
    var nx = ((clientX - rect.left) / rect.width) * 2 - 1;
    var ny = ((clientY - rect.top) / rect.height) * 2 - 1;
    return { x: nx * halfWScene, y: -ny * halfHScene };
  }

  hero.addEventListener('mouseenter', function () { mouse.active = true; }, { passive: true });
  hero.addEventListener('mouseleave', function () {
    mouse.active = false;
    mouse.x = Infinity;
    mouse.y = Infinity;
    material.uniforms.uMouse.value.set(Infinity, Infinity);
  }, { passive: true });
  hero.addEventListener('mousemove', function (e) {
    var s = toScene(e.clientX, e.clientY);
    mouse.x = s.x;
    mouse.y = s.y;
    material.uniforms.uMouse.value.set(s.x, s.y);
  }, { passive: true });

  /* ---- animation loop ---- */
  var running = true;

  function hexDist(px, py) {
    var ax = Math.abs(px), ay = Math.abs(py);
    return Math.max(ax * 0.866025 + ay * 0.5, ay);
  }

  function animate() {
    if (!running) { requestAnimationFrame(animate); return; }

    var posArr = geometry.attributes.position.array;
    var i, dx, dy, dist, force;

    for (i = 0; i < posArr.length / 3; i++) {
      var origZ = origPositions[i * 3 + 2];

      if (mouse.active) {
        dx = posArr[i * 3] - mouse.x;
        dy = posArr[i * 3 + 1] - mouse.y;
        dist = hexDist(dx, dy);

        if (dist < DEFORM_RADIUS && dist > 0.01) {
          force = (1 - dist / DEFORM_RADIUS) * BULGE_STRENGTH;
          posArr[i * 3 + 2] += (force - posArr[i * 3 + 2]) * 0.15;
        }
      }

      posArr[i * 3 + 2] += (origZ - posArr[i * 3 + 2]) * SPRING_BACK;
    }

    geometry.attributes.position.needsUpdate = true;
    renderer.render(scene, camera);
    requestAnimationFrame(animate);
  }

  animate();

  /* ---- resize handler ---- */
  var resizeTimer = null;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      W = hero.offsetWidth;
      H = hero.offsetHeight;
      aspect = W / H;
      halfHScene = 8 * Math.tan((45 * Math.PI) / 360);
      halfWScene = halfHScene * aspect;
      camera.aspect = W / H;
      camera.updateProjectionMatrix();
      renderer.setSize(W, H);

      planeW = halfWScene * 2;
      planeH = halfHScene * 2;
      geometry.dispose();
      geometry = new THREE.PlaneGeometry(planeW, planeH, PLANE_SEGMENTS, PLANE_SEGMENTS);
      origPositions = Float32Array.from(geometry.attributes.position.array);
      plane.geometry = geometry;
    }, 150);
  }, { passive: true });

  document.addEventListener('visibilitychange', function () {
    running = !document.hidden;
  });
})();
