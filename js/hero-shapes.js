(function () {
  'use strict';

  if (window.innerWidth < 768) return;

  var hero = document.querySelector('.hero');
  var container = document.querySelector('.hero-particles');
  var heroBg = document.querySelector('.hero-bg');
  if (!hero || !container || typeof THREE === 'undefined') return;

  var W = hero.offsetWidth;
  var H = hero.offsetHeight;

  var scene = new THREE.Scene();
  var camera = new THREE.PerspectiveCamera(45, W / H, 0.1, 100);
  camera.position.set(0, 0, 8);
  camera.lookAt(0, 0, 0);

  var renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(W, H);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.appendChild(renderer.domElement);

  var aspect = W / H;
  var halfHScene = 8 * Math.tan((45 * Math.PI) / 360);
  var halfWScene = halfHScene * aspect;

  var HEX_RADIUS = 0.6;
  var VISIBILITY_RADIUS = 3.5;
  var DEFORM_RADIUS = 1.8;
  var BULGE_STRENGTH = 0.5;
  var SPRING_BACK = 0.08;
  var VISIBILITY_LERP = 0.1;
  var AMBER = new THREE.Color(0xf0c850);
  var useLine2 = typeof THREE.Line2 !== 'undefined';

  var mouse = { x: Infinity, y: Infinity, active: false };
  var vertX, vertY, vertZ, vertAlpha;
  var vertexSlots;
  var lineGeometry, lineMaterial, lineObj;
  var positionArray, colorArray;
  var numVerts;

  function buildGrid() {
    var i;
    for (i = scene.children.length - 1; i >= 0; i--) {
      scene.remove(scene.children[i]);
    }

    var hexHeight = Math.sqrt(3) * HEX_RADIUS;
    var horizSpacing = HEX_RADIUS * 1.5;
    var vertSpacing = hexHeight;

    var numCols = Math.ceil(halfWScene * 2 / horizSpacing) + 4;
    var numRows = Math.ceil(halfHScene * 2 / vertSpacing) + 4;

    var vertexMap = {};
    var vertices = [];
    var edges = [];

    function getVertex(x, y) {
      var key = Math.round(x * 1000) + ',' + Math.round(y * 1000);
      if (vertexMap[key] === undefined) {
        vertexMap[key] = vertices.length;
        vertices.push({ x: x, y: y });
      }
      return vertexMap[key];
    }

    var col, row, cx, cy;
    for (col = 0; col < numCols; col++) {
      for (row = 0; row < numRows; row++) {
        cx = col * horizSpacing - halfWScene;
        cy = row * vertSpacing - halfHScene;
        if (col % 2 === 1) cy += vertSpacing / 2;

        var v0 = getVertex(cx + HEX_RADIUS, cy);
        var v1 = getVertex(cx + HEX_RADIUS * 0.5, cy + hexHeight * 0.5);
        var v2 = getVertex(cx - HEX_RADIUS * 0.5, cy + hexHeight * 0.5);
        var v3 = getVertex(cx - HEX_RADIUS, cy);
        var v4 = getVertex(cx - HEX_RADIUS * 0.5, cy - hexHeight * 0.5);
        var v5 = getVertex(cx + HEX_RADIUS * 0.5, cy - hexHeight * 0.5);

        edges.push(v0, v1, v1, v2, v2, v3, v3, v4, v4, v5, v5, v0);
      }
    }

    numVerts = vertices.length;
    vertX = new Float32Array(numVerts);
    vertY = new Float32Array(numVerts);
    vertZ = new Float32Array(numVerts);
    vertAlpha = new Float32Array(numVerts);

    for (i = 0; i < numVerts; i++) {
      vertX[i] = vertices[i].x;
      vertY[i] = vertices[i].y;
      vertZ[i] = 0;
      vertAlpha[i] = 0;
    }

    var edgeCount = edges.length / 2;
    var slotCount = edgeCount * 2;

    positionArray = new Float32Array(slotCount * 3);
    colorArray = new Float32Array(slotCount * 4);
    vertexSlots = new Array(numVerts);

    for (i = 0; i < numVerts; i++) vertexSlots[i] = [];

    var e, vA, vB, sA, sB, pA, pB, cA, cB;
    for (e = 0; e < edgeCount; e++) {
      vA = edges[e * 2];
      vB = edges[e * 2 + 1];
      sA = e * 2;
      sB = e * 2 + 1;

      pA = sA * 3;
      pB = sB * 3;
      positionArray[pA] = vertX[vA];
      positionArray[pA + 1] = vertY[vA];
      positionArray[pA + 2] = 0;
      positionArray[pB] = vertX[vB];
      positionArray[pB + 1] = vertY[vB];
      positionArray[pB + 2] = 0;

      cA = sA * 4;
      cB = sB * 4;
      colorArray[cA] = AMBER.r;
      colorArray[cA + 1] = AMBER.g;
      colorArray[cA + 2] = AMBER.b;
      colorArray[cA + 3] = 0;
      colorArray[cB] = AMBER.r;
      colorArray[cB + 1] = AMBER.g;
      colorArray[cB + 2] = AMBER.b;
      colorArray[cB + 3] = 0;

      vertexSlots[vA].push(sA);
      vertexSlots[vB].push(sB);
    }

    if (useLine2) {
      lineGeometry = new THREE.LineGeometry();
      lineGeometry.setPositions(positionArray);
      lineGeometry.setColors(colorArray);

      lineMaterial = new THREE.LineMaterial({
        linewidth: 6,
        vertexColors: true,
        transparent: true,
        worldUnits: false
      });
      lineMaterial.resolution.set(W, H);

      lineObj = new THREE.Line2(lineGeometry, lineMaterial);
      lineObj.renderOrder = 2;
      scene.add(lineObj);
    } else {
      lineGeometry = new THREE.BufferGeometry();
      lineGeometry.setAttribute('position', new THREE.BufferAttribute(positionArray, 3));
      lineGeometry.setAttribute('color', new THREE.BufferAttribute(colorArray, 4));

      lineMaterial = new THREE.LineBasicMaterial({
        vertexColors: true,
        transparent: true,
        opacity: 1
      });

      lineObj = new THREE.LineSegments(lineGeometry, lineMaterial);
      lineObj.renderOrder = 2;
      scene.add(lineObj);
    }
  }

  buildGrid();

  function toScene(clientX, clientY) {
    var rect = hero.getBoundingClientRect();
    var nx = ((clientX - rect.left) / rect.width) * 2 - 1;
    var ny = ((clientY - rect.top) / rect.height) * 2 - 1;
    return { x: nx * halfWScene, y: -ny * halfHScene };
  }

  hero.addEventListener('mouseenter', function () {
    mouse.active = true;
  }, { passive: true });

  hero.addEventListener('mouseleave', function () {
    mouse.active = false;
    mouse.x = Infinity;
    mouse.y = Infinity;
  }, { passive: true });

  hero.addEventListener('mousemove', function (e) {
    var s = toScene(e.clientX, e.clientY);
    mouse.x = s.x;
    mouse.y = s.y;
  }, { passive: true });

  var running = true;

  function animate() {
    if (!running) { requestAnimationFrame(animate); return; }

    var i, j, s, dx, dy, dist, targetA, force, alpha, r, g, b, slots;

    for (i = 0; i < numVerts; i++) {
      targetA = 0;
      dist = Infinity;

      if (mouse.active) {
        dx = vertX[i] - mouse.x;
        dy = vertY[i] - mouse.y;
        dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < VISIBILITY_RADIUS) {
          targetA = 1 - dist / VISIBILITY_RADIUS;
          targetA = targetA * targetA;
        }
      }

      vertAlpha[i] += (targetA - vertAlpha[i]) * VISIBILITY_LERP;
      if (vertAlpha[i] < 0.005) vertAlpha[i] = 0;

      if (dist < DEFORM_RADIUS && dist > 0.01) {
        force = (1 - dist / DEFORM_RADIUS) * BULGE_STRENGTH;
        vertZ[i] += (force - vertZ[i]) * 0.15;
      }

      vertZ[i] += (0 - vertZ[i]) * SPRING_BACK;

      alpha = vertAlpha[i];
      r = AMBER.r * alpha;
      g = AMBER.g * alpha;
      b = AMBER.b * alpha;

      slots = vertexSlots[i];
      for (j = 0; j < slots.length; j++) {
        s = slots[j];
        positionArray[s * 3 + 2] = vertZ[i];
        colorArray[s * 4] = r;
        colorArray[s * 4 + 1] = g;
        colorArray[s * 4 + 2] = b;
        colorArray[s * 4 + 3] = alpha;
      }
    }

    if (useLine2) {
      var posAttr = lineGeometry.getAttribute('position');
      var colAttr = lineGeometry.getAttribute('color');
      posAttr.array.set(positionArray);
      colAttr.array.set(colorArray);
      posAttr.needsUpdate = true;
      colAttr.needsUpdate = true;
    } else {
      lineGeometry.getAttribute('position').needsUpdate = true;
      lineGeometry.getAttribute('color').needsUpdate = true;
    }

    renderer.render(scene, camera);
    requestAnimationFrame(animate);
  }

  animate();

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
      if (useLine2 && lineMaterial) lineMaterial.resolution.set(W, H);
      buildGrid();
    }, 150);
  }, { passive: true });

  document.addEventListener('visibilitychange', function () {
    running = !document.hidden;
  });
})();
