/* Argentum — site behaviour. No framework, no build step.
   Every module starts only when its markup is on the page. */
(() => {
  'use strict';

  const A = window.ARGENTUM || {};
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const rtl = document.documentElement.dir === 'rtl';

  const store = {
    get(k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch (e) { /* storage blocked */ } },
  };

  function track(event, params) {
    if (typeof window.gtag === 'function') window.gtag('event', event, params || {});
  }

  function iconSvg(name) {
    return '<svg class="ico" aria-hidden="true"><use href="' + A.icons + '#' + name + '"></use></svg>';
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  // Elements hidden only when JavaScript runs (no-JS visitors see everything)
  $$('[data-js-hidden]').forEach((el) => { el.hidden = true; el.removeAttribute('data-js-hidden'); });

  /* ---------- Header: scroll state and mobile menu ---------- */
  function initHeader() {
    const header = $('[data-header]');
    if (!header) return;
    const onScroll = () => header.toggleAttribute('data-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const toggle = $('[data-nav-toggle]');
    const nav = $('[data-nav]');
    if (!toggle || !nav) return;
    const label = $('.sr-only', toggle);
    const setOpen = (open) => {
      toggle.setAttribute('aria-expanded', String(open));
      nav.toggleAttribute('data-open', open);
      document.body.toggleAttribute('data-nav-open', open);
      label.textContent = open ? label.dataset.labelClose : label.dataset.labelOpen;
    };
    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && nav.hasAttribute('data-open')) { setOpen(false); toggle.focus(); }
    });
    $$('a', nav).forEach((a) => a.addEventListener('click', () => setOpen(false)));
    window.matchMedia('(min-width: 64rem)').addEventListener('change', (mq) => { if (mq.matches) setOpen(false); });
  }

  /* ---------- Accessible tabs (timeline, impact routes) ---------- */
  function initTabs(tabs, onSelect) {
    const select = (tab, focus) => {
      tabs.forEach((t) => {
        const on = t === tab;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        const panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
      if (focus) tab.focus();
      if (onSelect) onSelect(tabs.indexOf(tab));
    };
    tabs.forEach((tab, i) => {
      tab.addEventListener('click', () => select(tab));
      tab.addEventListener('keydown', (e) => {
        const fwd = rtl ? 'ArrowLeft' : 'ArrowRight';
        const back = rtl ? 'ArrowRight' : 'ArrowLeft';
        let next = null;
        if (e.key === fwd) next = tabs[(i + 1) % tabs.length];
        if (e.key === back) next = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') next = tabs[0];
        if (e.key === 'End') next = tabs[tabs.length - 1];
        if (next) { e.preventDefault(); select(next, true); }
      });
    });
    select(tabs.find((t) => t.getAttribute('aria-selected') === 'true') || tabs[0]);
    return select;
  }

  function initTimeline() {
    $$('[data-timeline]').forEach((root) => {
      const tabs = $$('[role="tab"]', root);
      const rail = $('.timeline__rail', root);
      initTabs(tabs, (index) => {
        rail.style.setProperty('--p', String(index / (tabs.length - 1)));
        tabs.forEach((t, i) => t.toggleAttribute('data-reached', i <= index));
      });
      root.setAttribute('data-ready', '');
    });
  }

  function initRoutes() {
    $$('[data-routes]').forEach((root) => initTabs($$('[role="tab"]', root), null));
  }

  /* ---------- Counters ---------- */
  function initCounters() {
    const nums = $$('[data-count]');
    if (!nums.length) return;
    const fmt = (v, d) => v.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
    const run = (el) => {
      const to = parseFloat(el.dataset.count) || 0;
      const d = parseInt(el.dataset.decimals || '0', 10);
      if (reducedMotion || to === 0) { el.textContent = fmt(to, d); return; }
      const start = performance.now();
      const dur = 1400;
      const step = (now) => {
        const p = Math.min(1, (now - start) / dur);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = fmt(to * eased, d);
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    if (!('IntersectionObserver' in window)) return;
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (!en.isIntersecting) return;
        io.unobserve(en.target);
        run(en.target);
      });
    }, { threshold: 0.5 });
    nums.forEach((el) => {
      el.textContent = fmt(0, parseInt(el.dataset.decimals || '0', 10));
      io.observe(el);
    });
  }

  /* ---------- Hero lightbox: X-ray film dissolves into silver, silver becomes the leaf ---------- */
  function initLightbox() {
    const box = $('[data-lightbox]');
    if (!box) return;
    const canvas = $('[data-lightbox-canvas]', box);
    const ctx = canvas.getContext('2d');
    const caption = $('[data-lightbox-caption]', box);
    const stageEls = $$('[data-lightbox-stages] li', box);
    const replay = $('[data-lightbox-replay]', box);
    const captionTexts = JSON.parse(box.dataset.captions || 'null') || stageEls.map((li) => li.textContent);
    const icon = new Path2D(box.dataset.iconPath);
    const ICON_W = 107.8, ICON_H = 144;
    const SILVER = [191, 198, 196];
    const T = { film: 900, scan: 1700, travel: 1700, settle: 5200 };

    let W = 0, H = 0, dpr = 1, film = null, filmRect = null, parts = [], t0 = 0, raf = 0, visible = true, stage = -1;
    const pointer = { x: -9999, y: -9999 };

    function drawRadiograph(w, h) {
      // A stylised chest radiograph: bones bright, lungs dark. No patient data.
      const c = document.createElement('canvas');
      c.width = w; c.height = h;
      const g = c.getContext('2d');
      g.fillStyle = '#0B100E';
      g.beginPath(); g.roundRect ? g.roundRect(0, 0, w, h, 6) : g.rect(0, 0, w, h); g.fill();
      const cx = w / 2;
      // soft tissue
      let rg = g.createRadialGradient(cx, h * 0.55, w * 0.1, cx, h * 0.55, w * 0.55);
      rg.addColorStop(0, 'rgba(120,135,130,.55)'); rg.addColorStop(1, 'rgba(120,135,130,0)');
      g.fillStyle = rg; g.fillRect(0, 0, w, h);
      // lungs (dark)
      g.fillStyle = 'rgba(8,12,11,.75)';
      g.beginPath(); g.ellipse(cx - w * 0.19, h * 0.5, w * 0.15, h * 0.27, 0.08, 0, Math.PI * 2); g.fill();
      g.beginPath(); g.ellipse(cx + w * 0.19, h * 0.5, w * 0.15, h * 0.27, -0.08, 0, Math.PI * 2); g.fill();
      // heart
      rg = g.createRadialGradient(cx + w * 0.05, h * 0.62, 2, cx + w * 0.05, h * 0.62, w * 0.16);
      rg.addColorStop(0, 'rgba(200,210,206,.55)'); rg.addColorStop(1, 'rgba(200,210,206,0)');
      g.fillStyle = rg; g.fillRect(0, 0, w, h);
      // spine
      g.fillStyle = 'rgba(225,232,229,.85)';
      for (let y = h * 0.12; y < h * 0.92; y += h * 0.045) {
        g.beginPath(); g.roundRect ? g.roundRect(cx - w * 0.028, y, w * 0.056, h * 0.034, 3) : g.rect(cx - w * 0.028, y, w * 0.056, h * 0.034); g.fill();
      }
      // ribs
      g.strokeStyle = 'rgba(225,232,229,.7)'; g.lineCap = 'round';
      for (let i = 0; i < 8; i++) {
        const y = h * (0.24 + i * 0.07);
        g.lineWidth = Math.max(2, w * 0.016);
        [-1, 1].forEach((s) => {
          g.beginPath();
          g.moveTo(cx + s * w * 0.04, y);
          g.bezierCurveTo(cx + s * w * 0.22, y - h * 0.05, cx + s * w * 0.36, y + h * 0.01, cx + s * w * 0.33, y + h * 0.09);
          g.stroke();
        });
      }
      // clavicles
      g.lineWidth = Math.max(3, w * 0.022);
      [-1, 1].forEach((s) => {
        g.beginPath(); g.moveTo(cx + s * w * 0.05, h * 0.17); g.quadraticCurveTo(cx + s * w * 0.2, h * 0.12, cx + s * w * 0.36, h * 0.16); g.stroke();
      });
      // patient label, redacted
      g.fillStyle = 'rgba(238,242,240,.9)';
      g.fillRect(w * 0.64, h * 0.04, w * 0.3, h * 0.07);
      g.fillStyle = '#121816';
      g.fillRect(w * 0.66, h * 0.055, w * 0.22, h * 0.012);
      g.fillRect(w * 0.66, h * 0.08, w * 0.14, h * 0.012);
      return c;
    }

    function samplePoints(src, n, weightFn, step) {
      const { width: w, height: h } = src;
      const data = src.getContext('2d').getImageData(0, 0, w, h).data;
      const pts = [];
      for (let y = 0; y < h; y += step) {
        for (let x = 0; x < w; x += step) {
          const i = (y * w + x) * 4;
          const p = weightFn(data[i], data[i + 1], data[i + 2], data[i + 3]);
          if (p > 0 && Math.random() < p) pts.push([x + Math.random() * step, y + Math.random() * step]);
        }
      }
      for (let i = pts.length - 1; i > 0; i--) { const j = (Math.random() * (i + 1)) | 0; [pts[i], pts[j]] = [pts[j], pts[i]]; }
      while (pts.length < n && pts.length) pts.push(pts[(Math.random() * pts.length) | 0]);
      return pts.slice(0, n);
    }

    function build() {
      const r = canvas.getBoundingClientRect();
      W = r.width; H = r.height;
      if (!W || !H) return;
      dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(W * dpr); canvas.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

      const n = W < 420 ? 1100 : 1900;
      // film sheet (35 × 43 cm proportions)
      let fh = H * 0.56, fw = fh * 0.81;
      if (fw > W * 0.66) { fw = W * 0.66; fh = fw / 0.81; }
      filmRect = { x: (W - fw) / 2, y: H * 0.43 - fh / 2, w: fw, h: fh };
      film = drawRadiograph(Math.round(fw), Math.round(fh));
      const src = samplePoints(film, n, (r2, g2, b2) => {
        const l = (r2 + g2 + b2) / 765;
        return l > 0.12 ? Math.pow(l, 1.3) * 0.9 : 0;
      }, 2);

      // leaf target
      const lh = Math.min(H * 0.5, W * 0.62 * (ICON_H / ICON_W));
      const lw = lh * (ICON_W / ICON_H);
      const lc = document.createElement('canvas');
      lc.width = Math.ceil(lw); lc.height = Math.ceil(lh);
      const lg = lc.getContext('2d');
      lg.scale(lw / ICON_W, lh / ICON_H);
      lg.fillStyle = '#fff';
      lg.fill(icon);
      const dst = samplePoints(lc, n, (r2, g2, b2, a) => (a > 128 ? 1 : 0), 2);
      const lx = (W - lw) / 2, ly = H * 0.43 - lh / 2;

      // pair top-to-top so the flow reads as one movement
      const srcSorted = src.map((p) => [p[0] + filmRect.x, p[1] + filmRect.y]).sort((a, b) => a[1] - b[1] + (Math.random() - 0.5) * 30);
      const dstSorted = dst.map((p) => [p[0] + lx, p[1] + ly]).sort((a, b) => a[1] - b[1] + (Math.random() - 0.5) * 30);

      parts = srcSorted.map((s, i) => {
        const d = dstSorted[i] || dstSorted[0];
        const scanAt = T.film + ((s[1] - filmRect.y) / filmRect.h) * T.scan;
        return {
          sx: s[0], sy: s[1], tx: d[0], ty: d[1],
          start: scanAt + 150 + Math.random() * 500,
          dur: T.travel * (0.75 + Math.random() * 0.5),
          size: 1.1 + Math.random() * 1.3,
          alpha: 0.45 + Math.random() * 0.55,
          green: Math.random() < 0.08,
          phase: Math.random() * Math.PI * 2,
          ox: 0, oy: 0,
          // a slight arc so particles do not travel in straight lines
          bend: (Math.random() - 0.5) * 80,
        };
      });
    }

    const ease = (p) => (p < 0.5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2);

    function setStage(i) {
      if (i === stage) return;
      stage = i;
      stageEls.forEach((li, k) => { li.toggleAttribute('data-done', k <= i); li.toggleAttribute('data-active', k === i); });
      if (caption) caption.textContent = captionTexts[i] || '';
    }

    function frame(now) {
      const t = now - t0;
      ctx.clearRect(0, 0, W, H);
      const scanY = filmRect.y + Math.max(0, Math.min(1, (t - T.film) / T.scan)) * filmRect.h;

      // the film, still intact below the scan line
      if (scanY < filmRect.y + filmRect.h) {
        const cut = scanY - filmRect.y;
        ctx.globalAlpha = 1;
        ctx.drawImage(film, 0, cut, film.width, film.height - cut, filmRect.x, scanY, filmRect.w, filmRect.h - cut);
        ctx.strokeStyle = 'rgba(191,198,196,.25)';
        ctx.lineWidth = 1;
        ctx.strokeRect(filmRect.x + 0.5, Math.max(filmRect.y, scanY) + 0.5, filmRect.w - 1, filmRect.y + filmRect.h - Math.max(filmRect.y, scanY) - 1);
      }
      // scan line
      if (t > T.film && t < T.film + T.scan + 200) {
        const grad = ctx.createLinearGradient(0, scanY - 18, 0, scanY + 2);
        grad.addColorStop(0, 'rgba(120,226,75,0)');
        grad.addColorStop(1, 'rgba(120,226,75,.28)');
        ctx.fillStyle = grad;
        ctx.fillRect(filmRect.x - 12, scanY - 18, filmRect.w + 24, 20);
        ctx.fillStyle = 'rgba(238,242,240,.95)';
        ctx.fillRect(filmRect.x - 12, scanY, filmRect.w + 24, 1.5);
      }

      // particles
      const greenMix = Math.max(0, Math.min(1, (t - T.settle + 400) / 900));
      const idle = t > T.settle;
      let moving = false;
      for (let k = 0; k < parts.length; k++) {
        const p = parts[k];
        if (p.sy > scanY && t < p.start) continue; // not yet released
        let x, y;
        const q = (t - p.start) / p.dur;
        if (q <= 0) { x = p.sx; y = p.sy - Math.min(6, (t - (p.start - 650)) * 0.01); }
        else if (q >= 1) { x = p.tx; y = p.ty; }
        else {
          const e = ease(q);
          x = p.sx + (p.tx - p.sx) * e + Math.sin(e * Math.PI) * p.bend;
          y = p.sy + (p.ty - p.sy) * e;
          moving = true;
        }
        if (idle) {
          x += Math.sin(now / 900 + p.phase) * 0.6;
          y += Math.cos(now / 1100 + p.phase) * 0.6;
          // gentle push away from the pointer
          const dx = x - pointer.x, dy = y - pointer.y, d2 = dx * dx + dy * dy;
          if (d2 < 2500) { const f = (1 - d2 / 2500) * 14 / Math.sqrt(d2 + 1); p.ox += dx * f * 0.08; p.oy += dy * f * 0.08; }
          p.ox *= 0.9; p.oy *= 0.9;
          x += p.ox; y += p.oy;
        }
        if (p.green && greenMix > 0) {
          ctx.fillStyle = 'rgba(120,226,75,' + (greenMix * 0.95).toFixed(3) + ')';
          ctx.fillRect(x, y, p.size + 0.4, p.size + 0.4);
          if (greenMix >= 1) continue;
        }
        ctx.fillStyle = 'rgba(' + SILVER[0] + ',' + SILVER[1] + ',' + SILVER[2] + ',' + (p.alpha * (p.green ? 1 - greenMix : 1)).toFixed(3) + ')';
        ctx.fillRect(x, y, p.size, p.size);
      }

      setStage(t < T.film ? 0 : t < T.settle - 300 ? 1 : 2);
      if (visible && (moving || t < T.settle + 1200 || idle)) raf = requestAnimationFrame(frame);
    }

    function start() {
      cancelAnimationFrame(raf);
      build();
      if (!parts.length) return;
      if (reducedMotion) {
        // No movement: show the film, then the finished leaf.
        t0 = performance.now() - T.settle - 1500;
        setStage(2);
        frameStatic();
        return;
      }
      t0 = performance.now();
      stage = -1;
      raf = requestAnimationFrame(frame);
    }

    function frameStatic() {
      ctx.clearRect(0, 0, W, H);
      parts.forEach((p) => {
        ctx.fillStyle = p.green ? 'rgba(120,226,75,.95)' : 'rgba(191,198,196,' + p.alpha + ')';
        ctx.fillRect(p.tx, p.ty, p.size, p.size);
      });
    }

    replay.addEventListener('click', () => {
      if (reducedMotion) {
        build(); setStage(0);
        ctx.clearRect(0, 0, W, H);
        ctx.drawImage(film, filmRect.x, filmRect.y, filmRect.w, filmRect.h);
        setTimeout(() => { setStage(2); frameStatic(); }, 1600);
        return;
      }
      start();
    });
    canvas.addEventListener('pointermove', (e) => {
      const r = canvas.getBoundingClientRect();
      pointer.x = e.clientX - r.left; pointer.y = e.clientY - r.top;
    });
    canvas.addEventListener('pointerleave', () => { pointer.x = pointer.y = -9999; });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver((en) => {
        visible = en[0].isIntersecting;
        if (visible && !reducedMotion && t0) { cancelAnimationFrame(raf); raf = requestAnimationFrame(frame); }
      }).observe(box);
    }
    document.addEventListener('visibilitychange', () => {
      visible = !document.hidden;
      if (visible && !reducedMotion && t0) { cancelAnimationFrame(raf); raf = requestAnimationFrame(frame); }
    });
    let resizeTimer;
    let lastW = 0;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        const w = canvas.getBoundingClientRect().width;
        if (Math.abs(w - lastW) < 2) return;
        lastW = w;
        build();
        t0 = performance.now() - T.settle - 2000; // jump to the finished leaf
        if (reducedMotion) frameStatic(); else { cancelAnimationFrame(raf); raf = requestAnimationFrame(frame); }
      }, 200);
    });

    const begin = () => { lastW = canvas.getBoundingClientRect().width; start(); };
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(begin); else begin();
  }

  /* ---------- Lead forms: steps, conditional fields, validation, submit ---------- */
  function initForms() {
    const errors = (A.i18n && A.i18n.errors) || {};

    $$('[data-lead-form]').forEach((form) => {
      const card = form.closest('[data-form-card]');
      const success = $('[data-form-success]', card);
      const alertBox = $('[data-form-alert]', form);
      const steps = $$('.lead-form__step', form);
      const stepped = form.hasAttribute('data-steps');
      const nextBtn = $('[data-step-next]', form);
      const backBtn = $('[data-step-back]', form);
      const submitBtn = $('[data-submit]', form);
      const stepNames = steps.map((s) => ($('legend', s) || {}).textContent || '');
      let current = 0;
      let started = false;

      const valueOf = (name) => {
        const els = $$('[name="' + name + '"], [name="' + name + '[]"]', form);
        if (!els.length) return '';
        if (els[0].type === 'checkbox') return els.filter((e) => e.checked).map((e) => e.value);
        if (els[0].type === 'radio') { const c = els.find((e) => e.checked); return c ? c.value : ''; }
        return els[0].value;
      };

      function updateConditionals() {
        $$('[data-show-if]', form).forEach((field) => {
          const [name, val] = field.dataset.showIf.split('=');
          const v = valueOf(name);
          const show = Array.isArray(v) ? v.includes(val) : v === val;
          field.hidden = !show;
          $$('input, select, textarea', field).forEach((el) => { el.disabled = !show; });
          if (!show) clearError(field);
        });
        $$('[data-note-for]', form).forEach((note) => {
          const v = valueOf(note.dataset.noteFor);
          note.hidden = Array.isArray(v) ? !v.includes(note.dataset.noteValue) : v !== note.dataset.noteValue;
        });
      }

      function setError(field, code) {
        field.setAttribute('data-invalid', '');
        $('[data-error-text]', field).textContent = typeof code === 'string' && errors[code] ? errors[code] : code;
        $$('input, select, textarea', field).forEach((el) => el.setAttribute('aria-invalid', 'true'));
      }
      function clearError(field) {
        field.removeAttribute('data-invalid');
        $$('[aria-invalid]', field).forEach((el) => el.removeAttribute('aria-invalid'));
      }

      function validateField(field) {
        if (field.hidden) return true;
        const inputs = $$('input, select, textarea', field);
        const first = inputs[0];
        if (!first) return true;
        const required = inputs.some((el) => el.required);
        let v = valueOf(first.name.replace('[]', ''));
        if (typeof v === 'string') v = v.trim();
        const empty = Array.isArray(v) ? !v.length : v === '';
        let code = '';
        if (empty) {
          if (required) code = 'required';
        } else if (first.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) code = 'email';
        else if (first.type === 'tel' && ((v.match(/\d/g) || []).length < 7 || !/^[+\d\s().-]+$/.test(v))) code = 'phone';
        else if (first.type === 'number' && (isNaN(parseFloat(v)) || +v < +first.min || +v > +first.max)) code = 'number';
        else if (first.type === 'date' && first.min && v < first.min) code = 'date_past';
        else if (first.maxLength > 0 && v.length > first.maxLength) code = 'too_long';
        if (code) { setError(field, code); return false; }
        clearError(field);
        return true;
      }

      function validateStep(i) {
        const fields = $$('[data-field]', steps[i] || form);
        const bad = fields.filter((f) => !validateField(f));
        if (bad.length) {
          const el = $('input:not([type="hidden"]), select, textarea', bad[0]);
          if (el) el.focus({ preventScroll: false });
        }
        return !bad.length;
      }

      function showStep(i, focus) {
        current = i;
        steps.forEach((s, k) => s.toggleAttribute('data-current', k === i));
        if (stepped) {
          $('[data-step-name]', form).textContent = stepNames[i];
          $('[data-step-count]', form).textContent = $('[data-step-count]', form).textContent.replace(/\d+/, String(i + 1));
          $$('.progress__bars i', form).forEach((b, k) => b.toggleAttribute('data-on', k <= i));
          backBtn.hidden = i === 0;
          nextBtn.hidden = i === steps.length - 1;
          submitBtn.toggleAttribute('data-visible', i === steps.length - 1);
        }
        if (focus) {
          const el = $('input:not([type="hidden"]):not([disabled]), select, textarea', steps[i]);
          const top = card.getBoundingClientRect().top;
          if (top < 0) card.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
          if (el) el.focus({ preventScroll: true });
        }
      }

      form.addEventListener('change', (e) => {
        updateConditionals();
        const field = e.target.closest('[data-field]');
        if (field && field.hasAttribute('data-invalid')) validateField(field);
      });
      form.addEventListener('input', (e) => {
        if (!started) { started = true; track('form_start', { form: form.dataset.leadForm }); }
        const field = e.target.closest('[data-field]');
        if (field && field.hasAttribute('data-invalid')) validateField(field);
      });
      form.addEventListener('focusout', (e) => {
        const field = e.target.closest('[data-field]');
        if (field && e.target.value && e.target.value.trim() !== '+961') validateField(field);
      });
      form.addEventListener('keydown', (e) => {
        if (stepped && e.key === 'Enter' && e.target.tagName === 'INPUT' && current < steps.length - 1) {
          e.preventDefault();
          nextBtn.click();
        }
      });
      if (stepped) {
        nextBtn.addEventListener('click', () => { if (validateStep(current)) showStep(current + 1, true); });
        backBtn.addEventListener('click', () => showStep(current - 1, true));
      }

      async function captchaToken() {
        const c = A.captcha;
        if (!c) return '';
        try {
          if (c.provider === 'recaptcha' && window.grecaptcha) {
            await new Promise((r) => window.grecaptcha.ready(r));
            return await window.grecaptcha.execute(c.key, { action: 'lead' });
          }
          if (c.provider === 'turnstile' && window.turnstile) {
            const holder = form.querySelector('[data-turnstile]') || form.appendChild(Object.assign(document.createElement('div'), { hidden: true }));
            holder.setAttribute('data-turnstile', '');
            return await new Promise((resolve) => window.turnstile.render(holder, { sitekey: c.key, callback: resolve, 'error-callback': () => resolve('') }));
          }
        } catch (err) { /* fall through: the server decides */ }
        return '';
      }

      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        alertBox.hidden = true;
        for (let i = 0; i < steps.length; i++) {
          if (!validateStep(i)) { showStep(i, false); validateStep(i); return; }
        }
        const label = submitBtn.textContent;
        submitBtn.setAttribute('aria-busy', 'true');
        submitBtn.disabled = true;
        submitBtn.textContent = (A.i18n && A.i18n.sending) || '…';
        try {
          const data = new FormData(form);
          if (A.captcha) data.set('captcha', await captchaToken());
          const res = await fetch(form.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
          const json = await res.json().catch(() => ({}));
          if (res.ok && json.ok) {
            form.hidden = true;
            if (json.message) $('[data-success-text]', success).textContent = json.message;
            success.hidden = false;
            success.focus();
            track('generate_lead', { form: form.dataset.leadForm });
            return;
          }
          if (json.errors) {
            let firstStep = -1;
            Object.keys(json.errors).forEach((name) => {
              const field = $('[data-field="' + name + '"]', form);
              if (!field) return;
              setError(field, json.errors[name]);
              const s = steps.indexOf(field.closest('.lead-form__step'));
              if (firstStep === -1 || s < firstStep) firstStep = s;
            });
            if (firstStep > -1) showStep(firstStep, false);
          }
          $('[data-alert-text]', alertBox).textContent = json.message || (A.i18n && A.i18n.error) || '';
          alertBox.hidden = false;
          alertBox.scrollIntoView({ block: 'nearest', behavior: reducedMotion ? 'auto' : 'smooth' });
        } catch (err) {
          $('[data-alert-text]', alertBox).textContent = (A.i18n && A.i18n.error) || '';
          alertBox.hidden = false;
        } finally {
          submitBtn.removeAttribute('aria-busy');
          submitBtn.disabled = false;
          submitBtn.textContent = label;
        }
      });

      const again = $('[data-form-again]', card);
      if (again) {
        again.addEventListener('click', (e) => {
          e.preventDefault();
          form.reset();
          $$('[data-field]', form).forEach(clearError);
          updateConditionals();
          success.hidden = true;
          form.hidden = false;
          showStep(0, true);
        });
      }

      // Pre-select a quantity passed in the URL (?quantity=200-500)
      const q = new URLSearchParams(location.search).get('quantity');
      if (q) { const r = $('input[name="quantity"][value="' + CSS.escape(q) + '"]', form); if (r) r.checked = true; }

      updateConditionals();
      showStep(0, false);
    });
  }

  /* ---------- Contact page: pick the form that fits ---------- */
  function initChooser() {
    const chooser = $('[data-chooser]');
    if (!chooser) return;
    const panels = $$('[data-chooser-panel]');
    const apply = () => {
      const v = ($('input:checked', chooser) || {}).value || 'collection';
      panels.forEach((p) => { p.hidden = p.dataset.chooserPanel !== v; });
    };
    chooser.addEventListener('change', apply);
    apply();
  }

  /* ---------- Verify a document ---------- */
  function initVerify() {
    const root = $('[data-verify]');
    if (!root) return;
    const form = $('[data-verify-form]', root);
    const out = $('[data-verify-result]', root);
    const input = $('input[name="code"]', form);
    const btn = $('button', form);
    const t = (A.i18n && A.i18n.verify) || {};

    const render = (state, title, text, rows) => {
      out.dataset.state = state;
      out.innerHTML = '<h2>' + iconSvg(state === 'valid' ? 'check' : 'alert') + escapeHtml(title) + '</h2>'
        + (text ? '<p>' + escapeHtml(text) + '</p>' : '')
        + (rows && rows.length ? '<dl>' + rows.map((r) => '<div><dt>' + escapeHtml(r[0]) + '</dt><dd>' + escapeHtml(r[1]) + '</dd></div>').join('') + '</dl>' : '');
      out.hidden = false;
    };

    const check = async (code) => {
      if (!code) return;
      const label = btn.innerHTML;
      btn.disabled = true;
      btn.textContent = t.checking || '…';
      try {
        const res = await fetch(A.verify + '?lang=' + A.lang + '&code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (res.status === 429) { render('missing', t.rate_limited || '', '', []); return; }
        if (!json.found) { render('missing', t.not_found_title, t.not_found_text, []); return; }
        const f = t.fields || {};
        const rows = [[f.kind, json.kind], [f.issued_on, json.issued_on], [f.organisation, json.organisation]];
        if (json.weight_kg !== null) rows.push([f.weight, json.weight_kg + ' kg']);
        if (json.purity !== null) rows.push([f.purity, json.purity + '%']);
        if (json.reference) rows.push([f.reference, json.reference]);
        if (json.revoked) render('revoked', t.revoked_title, t.revoked_text, rows);
        else render('valid', t.valid_title, t.valid_text, rows);
        track('verify_document', { found: true });
      } catch (err) {
        render('missing', t.error || '', '', []);
      } finally {
        btn.disabled = false;
        btn.innerHTML = label;
      }
    };

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const code = input.value.trim().toUpperCase();
      history.replaceState(null, '', '?code=' + encodeURIComponent(code));
      check(code);
    });
    if (input.value) check(input.value.trim());
  }

  /* ---------- Analytics consent (only when a GA4 ID is set) ---------- */
  function initConsent() {
    if (!A.ga) return;
    const banner = $('[data-consent]');
    const load = () => {
      const s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(A.ga);
      document.head.appendChild(s);
      window.dataLayer = window.dataLayer || [];
      window.gtag = function () { window.dataLayer.push(arguments); };
      window.gtag('js', new Date());
      window.gtag('config', A.ga, { anonymize_ip: true });
    };
    const choice = store.get('argentum-consent');
    if (choice === 'yes') load();
    else if (!choice && banner) banner.hidden = false;
    $$('[data-consent-choice]').forEach((b) => b.addEventListener('click', () => {
      store.set('argentum-consent', b.dataset.consentChoice);
      banner.hidden = true;
      if (b.dataset.consentChoice === 'yes') load();
    }));
    $$('[data-track]').forEach((el) => el.addEventListener('click', () => track('contact_click', { channel: el.dataset.track })));
  }

  // Remember the chosen language for the root URL (the server reads the cookie)
  $$('[data-lang]').forEach((a) => a.addEventListener('click', () => {
    document.cookie = 'lang=' + a.dataset.lang + ';path=/;max-age=31536000;samesite=lax';
  }));

  initHeader();
  initTimeline();
  initRoutes();
  initCounters();
  initForms();
  initChooser();
  initVerify();
  initConsent();
  initLightbox();
})();
