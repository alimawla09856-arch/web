/* Argentum — "the journey of a gram of silver".
   A WebGL particle world on the home page. Scrolling morphs ~26,000 points
   through six shapes: X-ray film → sealed container → shredded film →
   silver ingot → Lebanon → the Argentum leaf. Driven by GSAP ScrollTrigger.
   No 3D library: one small shader keeps the page light. */
(() => {
  'use strict';

  const root = document.querySelector('[data-world]');
  if (!root) return;
  const canvas = root.querySelector('[data-world-canvas]');
  const html = document.documentElement;
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const rtl = html.dir === 'rtl';
  let gl = null;
  try {
    gl = canvas.getContext('webgl', { antialias: false, alpha: false, depth: false, powerPreference: 'high-performance' });
  } catch (e) { gl = null; }
  if (!gl || !window.gsap || !window.ScrollTrigger) {
    html.classList.add('no-webgl');
    return;
  }
  html.classList.add('has-world');

  const SHAPES = 6;
  const lowPower = (navigator.hardwareConcurrency || 4) <= 4 || window.innerWidth < 760;
  const N = lowPower ? 14000 : 26000;
  const GREEN = [0, 0.11, 0, 0, 0.07, 0.5];  // share of green points per shape
  const BASE = [                                   // pose per shape: rotX, rotY, scale
    [0.08, -0.42, 1.0],
    [0.38, 0.62, 0.95],
    [0.12, -0.32, 1.0],
    [0.5, 0.78, 1.05],
    [-0.62, 0.0, 1.08],
    [0.0, 0.0, 1.0],
  ];

  /* ---------- seeded random so every visit draws the same world ---------- */
  let seed = 47;
  const rnd = () => { seed |= 0; seed = (seed + 0x6D2B79F5) | 0; let t = Math.imul(seed ^ (seed >>> 15), 1 | seed); t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t; return ((t ^ (t >>> 14)) >>> 0) / 4294967296; };
  const shuffle = (a, from = 0) => { for (let i = a.length - 1; i > from; i--) { const j = from + Math.floor(rnd() * (i - from + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };
  const lerp3 = (a, b, t) => [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t, a[2] + (b[2] - a[2]) * t];

  /* ---------- sampling helpers ---------- */
  function sampleCanvas(w, h, draw, weight, n) {
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const g = c.getContext('2d');
    draw(g, w, h);
    const d = g.getImageData(0, 0, w, h).data;
    const cum = new Float32Array(w * h);
    let total = 0;
    for (let i = 0; i < w * h; i++) { total += weight(d[i * 4], d[i * 4 + 1], d[i * 4 + 2], d[i * 4 + 3]); cum[i] = total; }
    const out = [];
    for (let k = 0; k < n; k++) {
      const r = rnd() * total;
      let lo = 0, hi = cum.length - 1;
      while (lo < hi) { const mid = (lo + hi) >> 1; if (cum[mid] < r) lo = mid + 1; else hi = mid; }
      out.push([(lo % w) + rnd(), Math.floor(lo / w) + rnd()]);
    }
    return out;
  }

  const quadArea = (q) => {
    const cross = (u, v) => [u[1] * v[2] - u[2] * v[1], u[2] * v[0] - u[0] * v[2], u[0] * v[1] - u[1] * v[0]];
    const sub = (a, b) => [a[0] - b[0], a[1] - b[1], a[2] - b[2]];
    const len = (v) => Math.hypot(v[0], v[1], v[2]);
    return 0.5 * (len(cross(sub(q[1], q[0]), sub(q[3], q[0]))) + len(cross(sub(q[1], q[2]), sub(q[3], q[2]))));
  };
  // Points on the faces (and, more densely, the edges) of solids made of quads
  function solid(quads, n, edgeShare) {
    const areas = quads.map(quadArea);
    const total = areas.reduce((a, b) => a + b, 0);
    const edges = [];
    quads.forEach((q) => { for (let i = 0; i < 4; i++) edges.push([q[i], q[(i + 1) % 4]]); });
    const lens = edges.map(([a, b]) => Math.hypot(b[0] - a[0], b[1] - a[1], b[2] - a[2]));
    const totalLen = lens.reduce((a, b) => a + b, 0);
    const pick = (weights, sum) => { let r = rnd() * sum; for (let i = 0; i < weights.length; i++) { r -= weights[i]; if (r <= 0) return i; } return weights.length - 1; };
    const out = [];
    for (let k = 0; k < n; k++) {
      if (rnd() < edgeShare) {
        const [a, b] = edges[pick(lens, totalLen)];
        const p = lerp3(a, b, rnd());
        out.push([p[0] + (rnd() - 0.5) * 0.012, p[1] + (rnd() - 0.5) * 0.012, p[2] + (rnd() - 0.5) * 0.012]);
      } else {
        const q = quads[pick(areas, total)];
        const u = rnd(), v = rnd();
        out.push(lerp3(lerp3(q[0], q[1], u), lerp3(q[3], q[2], u), v));
      }
    }
    return out;
  }
  function box(cx, cy, cz, w, h, d) {
    const x0 = cx - w / 2, x1 = cx + w / 2, y0 = cy - h / 2, y1 = cy + h / 2, z0 = cz - d / 2, z1 = cz + d / 2;
    return [
      [[x0, y1, z0], [x1, y1, z0], [x1, y1, z1], [x0, y1, z1]],
      [[x0, y0, z0], [x1, y0, z0], [x1, y0, z1], [x0, y0, z1]],
      [[x0, y0, z1], [x1, y0, z1], [x1, y1, z1], [x0, y1, z1]],
      [[x0, y0, z0], [x1, y0, z0], [x1, y1, z0], [x0, y1, z0]],
      [[x0, y0, z0], [x0, y0, z1], [x0, y1, z1], [x0, y1, z0]],
      [[x1, y0, z0], [x1, y0, z1], [x1, y1, z1], [x1, y1, z0]],
    ];
  }

  /* ---------- the six shapes (first `green` points of each list are green) ---------- */
  function radiograph(g, w, h) {
    g.fillStyle = '#000'; g.fillRect(0, 0, w, h);
    const cx = w / 2;
    let rg = g.createRadialGradient(cx, h * 0.55, w * 0.1, cx, h * 0.55, w * 0.55);
    rg.addColorStop(0, 'rgba(110,110,110,1)'); rg.addColorStop(1, 'rgba(110,110,110,0)');
    g.fillStyle = rg; g.fillRect(0, 0, w, h);
    g.fillStyle = 'rgba(0,0,0,.8)';
    g.beginPath(); g.ellipse(cx - w * 0.19, h * 0.5, w * 0.15, h * 0.27, 0.08, 0, Math.PI * 2); g.fill();
    g.beginPath(); g.ellipse(cx + w * 0.19, h * 0.5, w * 0.15, h * 0.27, -0.08, 0, Math.PI * 2); g.fill();
    rg = g.createRadialGradient(cx + w * 0.05, h * 0.62, 2, cx + w * 0.05, h * 0.62, w * 0.16);
    rg.addColorStop(0, 'rgba(190,190,190,1)'); rg.addColorStop(1, 'rgba(190,190,190,0)');
    g.fillStyle = rg; g.fillRect(0, 0, w, h);
    g.fillStyle = '#fff';
    for (let y = h * 0.12; y < h * 0.92; y += h * 0.045) g.fillRect(cx - w * 0.028, y, w * 0.056, h * 0.032);
    g.strokeStyle = '#fff'; g.lineCap = 'round';
    for (let i = 0; i < 8; i++) {
      const y = h * (0.24 + i * 0.07);
      g.lineWidth = Math.max(2, w * 0.018);
      [-1, 1].forEach((s) => {
        g.beginPath(); g.moveTo(cx + s * w * 0.04, y);
        g.bezierCurveTo(cx + s * w * 0.22, y - h * 0.05, cx + s * w * 0.36, y + h * 0.01, cx + s * w * 0.33, y + h * 0.09); g.stroke();
      });
    }
    g.lineWidth = Math.max(3, w * 0.024);
    [-1, 1].forEach((s) => { g.beginPath(); g.moveTo(cx + s * w * 0.05, h * 0.17); g.quadraticCurveTo(cx + s * w * 0.2, h * 0.12, cx + s * w * 0.36, h * 0.16); g.stroke(); });
    // redacted patient label
    g.fillStyle = '#fff'; g.fillRect(w * 0.64, h * 0.035, w * 0.3, h * 0.075);
  }

  function filmShape(n) {
    const W = 1.32, H = 1.62, cw = 220, ch = 270;
    const pts = sampleCanvas(cw, ch, radiograph, (r) => (r > 18 ? Math.pow(r / 255, 1.25) : 0), Math.round(n * 0.84))
      .map(([x, y]) => { const u = x / cw - 0.5; return [u * W, -(y / ch - 0.5) * H, -0.13 * (u * 2) * (u * 2) + (rnd() - 0.5) * 0.03]; });
    while (pts.length < n) { // the film sheet itself, faint and even
      const u = rnd() - 0.5;
      pts.push([u * W, (rnd() - 0.5) * H, -0.13 * (u * 2) * (u * 2)]);
    }
    return { pts: shuffle(pts), green: 0 };
  }

  function containerShape(n) {
    const g = Math.round(n * GREEN[1]);
    const seal = [];
    for (let k = 0; k < g; k++) {
      if (rnd() < 0.62) seal.push([(rnd() - 0.5) * 0.15, -0.55 + rnd() * 1.02, 0.43 + rnd() * 0.01]);
      else seal.push([(rnd() - 0.5) * 0.15, 0.475, (rnd() - 0.5) * 0.94]);
    }
    const quads = [...box(0, -0.1, 0, 1.3, 0.9, 0.85), ...box(0, 0.41, 0, 1.38, 0.13, 0.94), ...box(0, -0.62, 0, 1.75, 0.05, 1.15)];
    const rest = shuffle(solid(quads, n - g, 0.3));
    return { pts: shuffle(seal).concat(rest), green: g };
  }

  function shredShape(n) {
    const pts = [];
    const slot = Math.round(n * 0.07);
    for (let k = 0; k < slot; k++) pts.push([(rnd() - 0.5) * 1.9, 0.9 + (rnd() - 0.5) * 0.05, (rnd() - 0.5) * 0.5]);
    const ribbons = 42;
    const R = [];
    for (let i = 0; i < ribbons; i++) R.push({ x: -0.82 + (1.64 * (i + rnd() * 0.6)) / ribbons, z: (rnd() - 0.5) * 0.5, len: 0.55 + rnd() * 1.15, ph: rnd() * 6.28 });
    const totalLen = R.reduce((a, r) => a + r.len, 0);
    while (pts.length < n) {
      let r = rnd() * totalLen, rib = R[0];
      for (const x of R) { r -= x.len; if (r <= 0) { rib = x; break; } }
      const t = rnd() * rib.len;
      pts.push([rib.x + Math.sin(t * 4 + rib.ph) * 0.045 + (rnd() - 0.5) * 0.028, 0.86 - t, rib.z + Math.cos(t * 3 + rib.ph) * 0.06]);
    }
    return { pts: shuffle(pts), green: 0 };
  }

  function ingotShape(n) {
    const b = -0.26, t = 0.2;
    const B = [[-0.8, b, -0.4], [0.8, b, -0.4], [0.8, b, 0.4], [-0.8, b, 0.4]];
    const T = [[-0.58, t, -0.25], [0.58, t, -0.25], [0.58, t, 0.25], [-0.58, t, 0.25]];
    const quads = [T, B, [B[3], B[2], T[2], T[3]], [B[0], B[1], T[1], T[0]], [B[0], B[3], T[3], T[0]], [B[1], B[2], T[2], T[1]]];
    return { pts: shuffle(solid(quads, n, 0.32)), green: 0 };
  }

  function lebanonShape(n, data) {
    const H = 1.95, W = H * data.w;
    const cw = 230, ch = Math.round(230 / data.w);
    const path = (g, w, h) => { g.beginPath(); data.outline.forEach(([x, y], i) => (i ? g.lineTo((x / data.w) * w, y * h) : g.moveTo((x / data.w) * w, y * h))); g.closePath(); };
    const toWorld = (x, y) => { const u = x / cw - 0.5, v = y / ch - 0.5; return [u * W, -v * H, 0.04 * Math.sin(u * 9) * Math.cos(v * 7)]; };
    const g = Math.round(n * GREEN[4]);
    const regions = Object.values(data.regions);
    const spikes = [];
    for (let k = 0; k < g; k++) {
      const [rx, ry] = regions[k % regions.length];
      const p = toWorld((rx / data.w) * cw, ry * ch);
      if (rnd() < 0.55) spikes.push([p[0] + (rnd() - 0.5) * 0.02, p[1] + (rnd() - 0.5) * 0.02, p[2] + rnd() * rnd() * 0.42]);
      else { const a = rnd() * 6.28, r = Math.sqrt(rnd()) * 0.05; spikes.push([p[0] + Math.cos(a) * r, p[1] + Math.sin(a) * r, p[2] + 0.005]); }
    }
    const fill = sampleCanvas(cw, ch, (c, w, h) => { path(c, w, h); c.fillStyle = '#fff'; c.fill(); }, (r) => (r > 128 ? 1 : 0), Math.round((n - g) * 0.86));
    const edge = sampleCanvas(cw, ch, (c, w, h) => { path(c, w, h); c.strokeStyle = '#fff'; c.lineWidth = 2; c.stroke(); }, (r) => (r > 100 ? 1 : 0), n - g - fill.length);
    const rest = shuffle(fill.concat(edge).map(([x, y]) => toWorld(x, y)));
    const tops = {};
    Object.entries(data.regions).forEach(([key, [rx, ry]]) => { const p = toWorld((rx / data.w) * cw, ry * ch); tops[key] = [p[0], p[1], p[2] + 0.44]; });
    return { pts: shuffle(spikes).concat(rest), green: g, regions: tops };
  }

  function leafShape(n, d) {
    const H = 1.85, W = (H * 107.8) / 144, cw = 216, ch = 288;
    const icon = new Path2D(d);
    const draw = (stroke) => (g, w, h) => { g.scale(w / 107.8, h / 144); if (stroke) { g.strokeStyle = '#fff'; g.lineWidth = 1.6; g.stroke(icon); } else { g.fillStyle = '#fff'; g.fill(icon); } };
    const fill = sampleCanvas(cw, ch, draw(false), (r) => (r > 128 ? 1 : 0), Math.round(n * 0.78));
    const edge = sampleCanvas(cw, ch, draw(true), (r) => (r > 90 ? 1 : 0), n - fill.length);
    const pts = shuffle(fill.concat(edge).map(([x, y]) => [(x / cw - 0.5) * W, -(y / ch - 0.5) * H, (rnd() - 0.5) * 0.14]));
    return { pts, green: Math.round(n * GREEN[5]) };
  }

  /* ---------- GL program ---------- */
  const VS = `
precision highp float;
attribute vec3 p0; attribute vec3 p1; attribute vec3 p2; attribute vec3 p3; attribute vec3 p4; attribute vec3 p5;
attribute vec2 aSeed;
uniform mat4 uProj; uniform mat4 uMV;
uniform float uStage; uniform float uTime; uniform float uSize; uniform float uIntro; uniform float uDust; uniform float uScan;
uniform vec4 uGreenA; uniform vec2 uGreenB;
varying float vGreen; varying float vAlpha; varying float vScan;
vec3 shape(float i) { if (i < 0.5) return p0; if (i < 1.5) return p1; if (i < 2.5) return p2; if (i < 3.5) return p3; if (i < 4.5) return p4; return p5; }
float gfrac(float i) { if (i < 0.5) return uGreenA.x; if (i < 1.5) return uGreenA.y; if (i < 2.5) return uGreenA.z; if (i < 3.5) return uGreenA.w; if (i < 4.5) return uGreenB.x; return uGreenB.y; }
float hash(float n) { return fract(sin(n) * 43758.5453); }
void main() {
  float s = clamp(uStage, 0.0, 5.0);
  float i0 = floor(min(s, 4.999));
  float f = s - i0;
  float r = aSeed.y;
  float t = smoothstep(r * 0.42, 0.58 + r * 0.42, f);
  vec3 pos = mix(shape(i0), shape(i0 + 1.0), t);
  vec3 dir = normalize(vec3(hash(r * 91.7) - 0.5, hash(r * 47.3) - 0.5, hash(r * 13.1) - 0.5) + 0.0001);
  pos += dir * sin(t * 3.14159) * (0.35 + r * 0.55);
  pos = mix(dir * (2.6 + r * 3.4), pos, uIntro);
  pos += vec3(sin(uTime * 0.7 + r * 61.0), cos(uTime * 0.6 + r * 37.0), sin(uTime * 0.5 + r * 17.0)) * (0.005 + uDust * 0.12);
  vec4 mv = uMV * vec4(pos, 1.0);
  gl_Position = uProj * mv;
  vGreen = mix(step(aSeed.x, gfrac(i0)), step(aSeed.x, gfrac(i0 + 1.0)), t);
  vScan = uScan * exp(-abs(pos.y - (mod(uTime * 0.45, 2.6) - 1.3)) * 22.0) * (1.0 - smoothstep(0.0, 0.5, s));
  vAlpha = (0.42 + 0.58 * r) * mix(1.0, 0.28, uDust);
  gl_PointSize = uSize * (0.55 + r * 0.95) * (1.0 + vGreen * 0.4 + vScan * 0.8) / -mv.z;
}`;
  const FS = `
precision mediump float;
varying float vGreen; varying float vAlpha; varying float vScan;
void main() {
  vec2 c = gl_PointCoord - 0.5;
  float d = dot(c, c);
  if (d > 0.25) discard;
  float a = smoothstep(0.25, 0.0, d);
  vec3 col = mix(vec3(0.80, 0.84, 0.83), vec3(0.47, 0.886, 0.294), max(vGreen, vScan * 0.85));
  gl_FragColor = vec4(col, a * vAlpha * (1.0 + vScan));
}`;

  function compile(type, src) {
    const s = gl.createShader(type);
    gl.shaderSource(s, src);
    gl.compileShader(s);
    if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) throw new Error(gl.getShaderInfoLog(s));
    return s;
  }
  let prog;
  try {
    prog = gl.createProgram();
    gl.attachShader(prog, compile(gl.VERTEX_SHADER, VS));
    gl.attachShader(prog, compile(gl.FRAGMENT_SHADER, FS));
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) throw new Error(gl.getProgramInfoLog(prog));
  } catch (e) {
    html.classList.remove('has-world');
    html.classList.add('no-webgl');
    return;
  }
  gl.useProgram(prog);
  const U = {};
  ['uProj', 'uMV', 'uStage', 'uTime', 'uSize', 'uIntro', 'uDust', 'uScan', 'uGreenA', 'uGreenB'].forEach((k) => { U[k] = gl.getUniformLocation(prog, k); });
  const A = ['p0', 'p1', 'p2', 'p3', 'p4', 'p5', 'aSeed'].map((k) => gl.getAttribLocation(prog, k));

  function makeSet(shapes, seeds) {
    const bufs = shapes.map((arr) => { const b = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, b); gl.bufferData(gl.ARRAY_BUFFER, arr, gl.STATIC_DRAW); return b; });
    const sb = gl.createBuffer(); gl.bindBuffer(gl.ARRAY_BUFFER, sb); gl.bufferData(gl.ARRAY_BUFFER, seeds, gl.STATIC_DRAW);
    return { bufs, sb, count: seeds.length / 2 };
  }
  function bindSet(set) {
    set.bufs.forEach((b, i) => { gl.bindBuffer(gl.ARRAY_BUFFER, b); gl.enableVertexAttribArray(A[i]); gl.vertexAttribPointer(A[i], 3, gl.FLOAT, false, 0, 0); });
    gl.bindBuffer(gl.ARRAY_BUFFER, set.sb); gl.enableVertexAttribArray(A[6]); gl.vertexAttribPointer(A[6], 2, gl.FLOAT, false, 0, 0);
  }

  /* ---------- matrices ---------- */
  const mul = (a, b) => { const o = new Float32Array(16); for (let c = 0; c < 4; c++) for (let r = 0; r < 4; r++) { let s = 0; for (let k = 0; k < 4; k++) s += a[k * 4 + r] * b[c * 4 + k]; o[c * 4 + r] = s; } return o; };
  const perspective = (fov, asp, n, f) => { const t = 1 / Math.tan(fov / 2); return new Float32Array([t / asp, 0, 0, 0, 0, t, 0, 0, 0, 0, (f + n) / (n - f), -1, 0, 0, (2 * f * n) / (n - f), 0]); };
  const translate = (x, y, z) => new Float32Array([1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, x, y, z, 1]);
  const scale = (s) => new Float32Array([s, 0, 0, 0, 0, s, 0, 0, 0, 0, s, 0, 0, 0, 0, 1]);
  const rotX = (a) => { const c = Math.cos(a), s = Math.sin(a); return new Float32Array([1, 0, 0, 0, 0, c, s, 0, 0, -s, c, 0, 0, 0, 0, 1]); };
  const rotY = (a) => { const c = Math.cos(a), s = Math.sin(a); return new Float32Array([c, 0, -s, 0, 0, 1, 0, 0, s, 0, c, 0, 0, 0, 0, 1]); };
  const apply = (m, p) => { const x = p[0], y = p[1], z = p[2]; return [m[0] * x + m[4] * y + m[8] * z + m[12], m[1] * x + m[5] * y + m[9] * z + m[13], m[2] * x + m[6] * y + m[10] * z + m[14], m[3] * x + m[7] * y + m[11] * z + m[15]]; };

  /* ---------- state ---------- */
  const DIST = 5.4, FOV = (30 * Math.PI) / 180;
  const state = { stage: 0, target: 0, intro: reduced ? 1 : 0, px: 0, py: 0, tx: 0, ty: 0 };
  let W = 0, H = 0, dpr = 1, proj, main = null, dust = null, regionPts = null, running = false, raf = 0, t0 = performance.now(), last = t0;
  const rail = Array.from(root.querySelectorAll('[data-rail] li'));
  const huds = Array.from(root.querySelectorAll('[data-hud]'));
  const regionEls = Array.from(root.querySelectorAll('[data-region]'));
  const chapters = Array.from(root.querySelectorAll('[data-chapter]'));

  function resize() {
    const r = canvas.getBoundingClientRect();
    W = r.width; H = r.height;
    dpr = Math.min(window.devicePixelRatio || 1, lowPower ? 1.5 : 1.75);
    canvas.width = Math.round(W * dpr); canvas.height = Math.round(H * dpr);
    gl.viewport(0, 0, canvas.width, canvas.height);
    proj = perspective(FOV, W / H, 0.1, 60);
    gl.uniformMatrix4fv(U.uProj, false, proj);
  }

  // Where the object sits on screen for a given stage
  function pose(stage) {
    const i0 = Math.min(Math.floor(stage), SHAPES - 2), f = stage - i0;
    const e = f * f * (3 - 2 * f);
    const P = (i) => {
      const mobile = W / H < 0.9;
      const halfH = Math.tan(FOV / 2) * DIST, halfW = halfH * (W / H);
      const final = i === SHAPES - 1, hero = i === 0;
      const x = mobile ? 0 : halfW * (final ? 0 : hero ? 0.36 : 0.33) * (rtl ? -1 : 1);
      const y = mobile ? halfH * (final ? 0.36 : 0.36) : final ? halfH * 0.4 : 0;
      const s = BASE[i][2] * (mobile ? Math.min(0.78, (halfW * 1.6) / 2.2) : final ? 0.6 : hero ? 0.95 : 0.86);
      return [x, y, s, BASE[i][0], BASE[i][1] * (rtl ? -1 : 1)];
    };
    const a = P(i0), b = P(i0 + 1);
    return a.map((v, k) => v + (b[k] - v) * e);
  }

  function frame(now) {
    const dt = Math.min(0.05, (now - last) / 1000);
    last = now;
    const time = reduced ? 0 : (now - t0) / 1000;
    // ease toward the scroll target for a weighted, physical feel
    state.stage += (state.target - state.stage) * (reduced ? 1 : 1 - Math.exp(-dt * 4.5));
    state.px += (state.tx - state.px) * (1 - Math.exp(-dt * 3));
    state.py += (state.ty - state.py) * (1 - Math.exp(-dt * 3));

    const [x, y, s, rx, ry] = pose(state.stage);
    const sway = reduced ? 0 : Math.sin(time * 0.35) * 0.28 * (1 - 0.7 * Math.max(0, 1 - Math.abs(state.stage - 4)));
    const model = mul(translate(x, y, 0), mul(scale(s), mul(rotX(rx + state.py * 0.18), rotY(ry + sway + state.px * 0.35))));
    const mv = mul(translate(0, 0, -DIST), model);

    gl.clearColor(0.071, 0.094, 0.086, 1);
    gl.clear(gl.COLOR_BUFFER_BIT);
    gl.uniform1f(U.uTime, time);
    gl.uniform1f(U.uSize, (lowPower ? 2.4 : 2.1) * dpr * DIST);
    // ambient dust
    gl.uniformMatrix4fv(U.uMV, false, translate(0, 0, -DIST));
    gl.uniform1f(U.uStage, 0); gl.uniform1f(U.uIntro, 1); gl.uniform1f(U.uDust, 1); gl.uniform1f(U.uScan, 0);
    bindSet(dust); gl.drawArrays(gl.POINTS, 0, dust.count);
    // the silver
    gl.uniformMatrix4fv(U.uMV, false, mv);
    gl.uniform1f(U.uStage, state.stage); gl.uniform1f(U.uIntro, state.intro); gl.uniform1f(U.uDust, 0); gl.uniform1f(U.uScan, reduced ? 0 : 1);
    bindSet(main); gl.drawArrays(gl.POINTS, 0, main.count);

    // region labels follow the 3D map
    const near = Math.max(0, 1 - Math.abs(state.stage - 4) * 2.2);
    if (regionEls.length && regionPts) {
      const m = mul(proj, mv);
      regionEls.forEach((el) => {
        const pt = regionPts[el.dataset.region];
        if (!pt) return;
        const c = apply(m, pt);
        const sx = ((c[0] / c[3]) * 0.5 + 0.5) * W, sy = (1 - ((c[1] / c[3]) * 0.5 + 0.5)) * H;
        el.style.transform = 'translate(' + sx.toFixed(1) + 'px,' + sy.toFixed(1) + 'px)';
        el.style.opacity = near.toFixed(3);
      });
    }
    if (running) raf = requestAnimationFrame(frame);
  }

  function start() { if (!running && main) { running = true; last = performance.now(); raf = requestAnimationFrame(frame); } }
  function stop() { running = false; cancelAnimationFrame(raf); }

  /* ---------- scroll ---------- */
  function wireScroll() {
    const K = chapters.length;
    let active = -1;
    // Chapter positions, measured so that taller chapters (long text on phones) still line up
    let tops = [];
    const measure = () => { tops = chapters.map((c) => c.offsetTop); };
    const rawAt = (y) => {
      for (let i = 0; i < K - 1; i++) if (y < tops[i + 1]) return i + Math.max(0, (y - tops[i]) / (tops[i + 1] - tops[i]));
      return K - 1;
    };
    measure();
    ScrollTrigger.addEventListener('refreshInit', measure);
    ScrollTrigger.create({
      trigger: root.querySelector('.world__chapters'),
      start: 'top top',
      end: 'bottom bottom',
      onUpdate(self) {
        const raw = Math.min(K - 1, rawAt(self.scroll() - self.start));
        const i = Math.min(Math.floor(raw), K - 2), f = raw - i;
        const hold = Math.min(1, Math.max(0, (f - 0.22) / 0.56));
        state.target = reduced ? Math.round(raw) : i + hold;
        const now = Math.round(raw);
        if (now !== active) { active = now; setChapter(now); }
      },
    });
  }

  const counted = new Set();
  function setChapter(i) {
    rail.forEach((li, k) => { li.toggleAttribute('data-active', k === i); li.toggleAttribute('data-done', k < i); });
    huds.forEach((h) => h.toggleAttribute('data-on', +h.dataset.hud === i));
    chapters.forEach((c, k) => c.toggleAttribute('data-current', k === i));
    const hud = huds.find((h) => +h.dataset.hud === i);
    if (!hud || counted.has(i)) return;
    counted.add(i);
    hud.querySelectorAll('[data-hud-count]').forEach((el) => {
      const to = parseFloat(el.dataset.hudCount), d = +el.dataset.decimals || 0;
      if (reduced) { el.textContent = to.toFixed(d); return; }
      const o = { v: 0 };
      gsap.to(o, { v: to, duration: 1.6, ease: 'power3.out', onUpdate: () => { el.textContent = o.v.toFixed(d); } });
    });
    const scr = hud.querySelector('[data-hud-scramble]');
    if (scr && window.ScrambleTextPlugin && !reduced) {
      const blocks = scr.textContent;
      gsap.fromTo(scr, { scrambleText: { text: 'XXXXXX XXXXXXXX', chars: 'ABCDEFGHIJKLMNOPRSTUVWXYZ', speed: 0.6 } }, { duration: 1.4, delay: 0.2, scrambleText: { text: blocks, chars: '█▓▒░', speed: 0.5 } });
    }
  }

  window.addEventListener('pointermove', (e) => {
    if (reduced) return;
    state.tx = (e.clientX / window.innerWidth - 0.5) * 2;
    state.ty = (e.clientY / window.innerHeight - 0.5) * 2;
  }, { passive: true });

  canvas.addEventListener('webglcontextlost', (e) => { e.preventDefault(); stop(); html.classList.add('no-webgl'); html.classList.remove('has-world'); });

  /* ---------- build ---------- */
  async function build() {
    let lebanon = null;
    try { lebanon = await (await fetch(root.dataset.lebanon)).json(); } catch (e) { lebanon = null; }
    const icon = root.dataset.icon;
    const generated = [
      filmShape(N),
      containerShape(N),
      shredShape(N),
      ingotShape(N),
      lebanon ? lebanonShape(N, lebanon) : ingotShape(N),
      leafShape(N, icon),
    ];
    if (lebanon) regionPts = generated[4].regions;
    const arrays = generated.map((g) => { const a = new Float32Array(N * 3); g.pts.forEach((p, k) => { a[k * 3] = p[0]; a[k * 3 + 1] = p[1]; a[k * 3 + 2] = p[2]; }); return a; });
    const seeds = new Float32Array(N * 2);
    for (let k = 0; k < N; k++) { seeds[k * 2] = (k + 0.5) / N; seeds[k * 2 + 1] = rnd(); }
    main = makeSet(arrays, seeds);
    gl.uniform4f(U.uGreenA, generated[0].green / N, generated[1].green / N, generated[2].green / N, generated[3].green / N);
    gl.uniform2f(U.uGreenB, generated[4].green / N, generated[5].green / N);

    const D = lowPower ? 700 : 1400;
    const dp = new Float32Array(D * 3), ds = new Float32Array(D * 2);
    for (let k = 0; k < D; k++) { dp[k * 3] = (rnd() - 0.5) * 12; dp[k * 3 + 1] = (rnd() - 0.5) * 8; dp[k * 3 + 2] = -rnd() * 7 + 1.5; ds[k * 2] = 1; ds[k * 2 + 1] = rnd(); }
    dust = makeSet([dp, dp, dp, dp, dp, dp], ds);

    gl.disable(gl.DEPTH_TEST);
    gl.enable(gl.BLEND);
    gl.blendFunc(gl.SRC_ALPHA, gl.ONE);
    resize();
    wireScroll();
    setChapter(0);

    new IntersectionObserver((en) => { if (en[0].isIntersecting && !document.hidden) start(); else stop(); }).observe(root);
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); else if (root.getBoundingClientRect().bottom > 0) start(); });
    let rt;
    window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => { resize(); ScrollTrigger.refresh(); }, 150); });
    html.classList.add('world-ready');

    // particles gather from a cloud into the X-ray film
    const intro = () => { if (!reduced) gsap.to(state, { intro: 1, duration: 2.6, ease: 'power3.out' }); };
    if (html.classList.contains('is-loading')) document.addEventListener('argentum:loaded', intro, { once: true }); else intro();
  }
  build();
})();
