/* Argentum — motion layer: smooth scrolling, text reveals, scroll scenes,
   magnetic buttons and the pointer follower. Built on GSAP + ScrollTrigger.
   Everything here is decoration on top of a page that already works:
   with reduced motion, or if a script fails, the content stays readable. */
(() => {
  'use strict';

  const html = document.documentElement;
  const finishLoading = () => {
    if (!html.classList.contains('is-loading')) return;
    html.classList.remove('is-loading');
    document.dispatchEvent(new Event('argentum:loaded'));
  };
  const gsap = window.gsap;
  if (!gsap || !window.ScrollTrigger) { finishLoading(); return; }

  const plugins = [window.ScrollTrigger, window.SplitText, window.DrawSVGPlugin, window.ScrambleTextPlugin].filter(Boolean);
  gsap.registerPlugin(...plugins);
  const ST = window.ScrollTrigger;
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = window.matchMedia('(pointer: fine)').matches;
  const rtl = html.dir === 'rtl';
  const arabic = html.lang === 'ar';
  html.classList.add('has-motion');

  /* ---------- smooth scrolling ---------- */
  let lenis = null;
  if (!reduced && window.Lenis) {
    lenis = new window.Lenis({ lerp: 0.1, smoothWheel: true });
    lenis.on('scroll', ST.update);
    gsap.ticker.add((t) => lenis.raf(t * 1000));
    gsap.ticker.lagSmoothing(0);
    // in-page links glide instead of jumping
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href*="#"]');
      if (!a || e.defaultPrevented) return;
      const url = new URL(a.href, location.href);
      if (url.pathname !== location.pathname || !url.hash) return;
      const target = document.getElementById(decodeURIComponent(url.hash.slice(1)));
      if (!target) return;
      e.preventDefault();
      lenis.scrollTo(target, { offset: -80, duration: 1.4 });
      history.replaceState(null, '', url.hash);
    });
    // freeze the page under the open mobile menu
    new MutationObserver(() => (document.body.hasAttribute('data-nav-open') ? lenis.stop() : lenis.start()))
      .observe(document.body, { attributes: true, attributeFilter: ['data-nav-open'] });
  }

  /* ---------- header: hide on the way down, show on the way up ---------- */
  const header = $('[data-header]');
  if (header) {
    const bar = document.createElement('span');
    bar.className = 'scroll-progress';
    bar.setAttribute('aria-hidden', 'true');
    header.appendChild(bar);
    ST.create({
      start: 0, end: 'max',
      onUpdate(self) {
        bar.style.setProperty('--p', self.progress.toFixed(4));
        const hide = self.direction === 1 && self.scroll() > 240 && !document.body.hasAttribute('data-nav-open') && !header.contains(document.activeElement);
        header.toggleAttribute('data-hidden', hide);
      },
    });
  }

  // the header turns light-on-dark over the home page's dark world
  const world = $('.world');
  if (header && world) {
    header.setAttribute('data-on-dark', '');
    ST.create({ trigger: world, start: 'top top', end: 'bottom 80px', onToggle: (self) => header.toggleAttribute('data-on-dark', self.isActive) });
  }

  /* ---------- text splitting ---------- */
  // Lines rise out of a mask. Arabic letters join, so Arabic is only ever split by line.
  // The animation is rebuilt by SplitText whenever the lines re-flow (resize, fonts).
  function splitReveal(el, vars) {
    if (!window.SplitText) return;
    window.SplitText.create(el, {
      type: arabic ? 'lines' : 'lines,words', mask: 'lines', linesClass: 'split-line', autoSplit: true,
      onSplit: (self) => gsap.from(arabic ? self.lines : self.words, Object.assign({ yPercent: 115, ease: 'expo.out', stagger: arabic ? 0.1 : 0.03 }, vars)),
    });
  }

  function heroIntro() {
    const title = $('[data-split="hero"]');
    if (!title) return;
    splitReveal(title, { duration: 1.3, stagger: arabic ? 0.12 : 0.045 });
    gsap.timeline({ defaults: { ease: 'expo.out' }, delay: 0.35 })
      .from($$('[data-hero-fade]'), { y: 28, autoAlpha: 0, duration: 1.1, stagger: 0.1 })
      .from('.hud-tile, .hud-rail li', { autoAlpha: 0, y: 10, duration: 0.8, stagger: 0.05 }, '-=0.9');
  }

  function revealHeadings() {
    const els = $$('[data-split]:not([data-split="hero"]), main h1:not([data-split]), main .section-head h2, main .form-layout__intro h2, main .step-full h2')
      .filter((el, i, all) => all.indexOf(el) === i && !el.closest('.doc, .sheet') && !(el.closest('.world') && !el.hasAttribute('data-split')));
    els.forEach((el) => splitReveal(el, { duration: 1.05, scrollTrigger: { trigger: el, start: 'top 88%', once: true } }));
  }

  /* ---------- generic reveals ---------- */
  function revealBlocks() {
    const sel = [
      'main .lead', 'main .section-head > p', '.benefit', '.paper', '.tags li', '.faq details', '.specs > div', '.issued > li',
      '.contact-list li', '.form-card', '.aside-card', '.readout__cell', '.trail-table tbody tr', '.chooser label', '.seal-steps li',
      '.checklist li', '.prose > div', '.statement', '.verify', '.note', '.cta-band .btn-row', '.page-hero .btn-row', '.page-hero .label',
    ].join(',');
    const items = $$(sel).filter((el) => !el.closest('.world'));
    gsap.set(items, { y: 36, autoAlpha: 0 });
    ST.batch(items, {
      start: 'top 92%', once: true,
      onEnter: (batch) => gsap.to(batch, { y: 0, autoAlpha: 1, duration: 1, ease: 'expo.out', stagger: 0.07, overwrite: true }),
    });
    $$('[data-reveal="door"]').forEach((el, i) => {
      gsap.from(el, { clipPath: 'inset(18% 6% 0% 6% round 14px)', y: 60, duration: 1.4, ease: 'expo.out', delay: i * 0.08, scrollTrigger: { trigger: el, start: 'top 90%', once: true } });
    });
  }

  /* ---------- numbers ---------- */
  function numbers() {
    $$('[data-count]').forEach((el) => {
      const to = parseFloat(el.dataset.count), d = +el.dataset.decimals || 0;
      const o = { v: 0 };
      gsap.to(o, { v: to, duration: 1.8, ease: 'power3.out', scrollTrigger: { trigger: el, start: 'top 90%', once: true }, onUpdate: () => { el.textContent = o.v.toFixed(d); } });
    });
    $$('[data-odometer]').forEach((odo) => {
      const digits = $$('.odo__digit', odo);
      digits.forEach((dg) => { dg.dataset.to = getComputedStyle(dg).getPropertyValue('--d').trim() || '0'; dg.style.setProperty('--d', '0'); });
      ST.create({
        trigger: odo, start: 'top 88%', once: true,
        onEnter: () => digits.forEach((dg, i) => gsap.fromTo(dg, { '--d': 0 }, { '--d': +dg.dataset.to, duration: 1.6 + i * 0.18, ease: 'expo.out', delay: 0.05 * i })),
      });
    });
  }

  /* ---------- marquee that answers the scroll ---------- */
  function marquee() {
    const track = $('[data-marquee]');
    if (!track) return;
    const loop = gsap.to(track, { xPercent: rtl ? 50 : -50, duration: 38, ease: 'none', repeat: -1 });
    let boost = gsap.to({}, {});
    ST.create({
      trigger: track, start: 'top bottom', end: 'bottom top',
      onUpdate(self) {
        const v = gsap.utils.clamp(-6, 6, self.getVelocity() / 260);
        boost.kill();
        loop.timeScale(v === 0 ? 1 : v);
        boost = gsap.to(loop, { timeScale: self.direction === -1 ? -1 : 1, duration: 1.2, ease: 'power2.out' });
      },
    });
  }

  /* ---------- documents: horizontal scroll, codes decode ---------- */
  function paperScroll() {
    const section = $('[data-paper-scroll]');
    if (!section) return;
    const track = $('[data-paper-track]', section);
    const items = $$('.paper-scroll__item', section);
    const mm = gsap.matchMedia();
    mm.add('(min-width: 64rem)', () => {
      html.classList.add('paper-h');
      ST.refresh();
      const head = $('.paper-scroll__head', section);
      const distance = () => Math.max(0, track.scrollWidth - (track.parentElement.getBoundingClientRect().width - head.getBoundingClientRect().width));
      const tween = gsap.to(track, {
        x: () => (rtl ? 1 : -1) * distance(), ease: 'none',
        scrollTrigger: { trigger: section, pin: '.paper-scroll__pin', start: 'top top', end: () => '+=' + distance(), scrub: 1, invalidateOnRefresh: true },
      });
      items.forEach((item) => {
        gsap.fromTo(item, { rotate: rtl ? -5 : 5, y: 40, scale: 0.94 }, {
          rotate: 0, y: 0, scale: 1, ease: 'none',
          scrollTrigger: { trigger: item, containerAnimation: tween, start: rtl ? 'right 95%' : 'left 95%', end: rtl ? 'right 55%' : 'left 55%', scrub: true, horizontal: true },
        });
        const code = $('[data-scramble-code]', item);
        if (code && window.ScrambleTextPlugin) {
          ST.create({ trigger: item, containerAnimation: tween, start: rtl ? 'right 80%' : 'left 80%', horizontal: true, once: true,
            onEnter: () => gsap.to(code, { duration: 1.2, scrambleText: { text: code.textContent, chars: 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789', speed: 0.5, revealDelay: 0.2 } }) });
        }
      });
      return () => { gsap.set(track, { x: 0 }); html.classList.remove('paper-h'); };
    });
    mm.add('(max-width: 63.99rem)', () => {
      items.forEach((item) => gsap.from(item, { y: 60, autoAlpha: 0, rotate: 3, duration: 1.1, ease: 'expo.out', scrollTrigger: { trigger: item, start: 'top 90%', once: true } }));
    });
  }

  /* ---------- drag to compare ---------- */
  function compare() {
    $$('[data-compare]').forEach((box) => {
      const handle = $('[data-compare-handle]', box);
      let pos = 50;
      const set = (p) => {
        pos = gsap.utils.clamp(8, 92, p);
        box.style.setProperty('--pos', pos + '%');
        handle.setAttribute('aria-valuenow', String(Math.round(pos)));
      };
      const fromPointer = (e) => {
        const r = box.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width * 100;
        set(rtl ? 100 - x : x);
      };
      let dragging = false;
      handle.addEventListener('pointerdown', (e) => { dragging = true; handle.setPointerCapture(e.pointerId); gsap.killTweensOf(proxy); });
      handle.addEventListener('pointermove', (e) => { if (dragging) fromPointer(e); });
      handle.addEventListener('pointerup', () => { dragging = false; });
      box.addEventListener('click', (e) => { if (e.target.closest('[data-compare-handle]')) return; gsap.killTweensOf(proxy); proxy.v = pos; gsap.to(proxy, { v: (() => { const r = box.getBoundingClientRect(); const x = (e.clientX - r.left) / r.width * 100; return rtl ? 100 - x : x; })(), duration: 0.6, ease: 'expo.out', onUpdate: () => set(proxy.v) }); });
      handle.addEventListener('keydown', (e) => {
        const step = e.shiftKey ? 10 : 4;
        const fwd = rtl ? 'ArrowLeft' : 'ArrowRight', back = rtl ? 'ArrowRight' : 'ArrowLeft';
        if (e.key === fwd) set(pos + step); else if (e.key === back) set(pos - step);
        else if (e.key === 'Home') set(8); else if (e.key === 'End') set(92); else return;
        e.preventDefault();
      });
      const proxy = { v: 50 };
      set(50);
      if (!reduced) {
        // a hint sweep the first time the slider comes into view
        ST.create({ trigger: box, start: 'top 70%', once: true, onEnter: () => {
          proxy.v = 50;
          gsap.timeline({ onUpdate: () => set(proxy.v) })
            .to(proxy, { v: 80, duration: 0.9, ease: 'power2.inOut' })
            .to(proxy, { v: 22, duration: 1.1, ease: 'power2.inOut' })
            .to(proxy, { v: 50, duration: 0.9, ease: 'expo.out' });
        } });
      }
      $$('[data-compare-fx]', box).forEach((c) => compareFx(c, c.dataset.compareFx));
    });
  }

  // Ambient particles: dust sinking into the ground, or silver rising and turning green
  function compareFx(canvas, kind) {
    const ctx = canvas.getContext('2d');
    let w = 0, h = 0, raf = 0, on = false;
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const P = Array.from({ length: 140 }, () => ({ x: Math.random(), y: Math.random(), s: 0.6 + Math.random() * 1.8, v: 0.02 + Math.random() * 0.05, p: Math.random() * 6.28 }));
    const size = () => { const r = canvas.getBoundingClientRect(); w = r.width; h = r.height; canvas.width = w * dpr; canvas.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); };
    const draw = (t) => {
      ctx.clearRect(0, 0, w, h);
      if (kind === 'dumped') {
        const g = ctx.createLinearGradient(0, h * 0.72, 0, h);
        g.addColorStop(0, 'rgba(75,85,83,0)'); g.addColorStop(1, 'rgba(75,85,83,.45)');
        ctx.fillStyle = g; ctx.fillRect(0, h * 0.72, w, h * 0.28);
      }
      P.forEach((q) => {
        if (!reduced) {
          q.y += (kind === 'dumped' ? 1 : -1) * q.v * 0.016;
          if (q.y > 1.02) q.y = -0.02; if (q.y < -0.02) q.y = 1.02;
        }
        const x = (q.x + Math.sin(t / 1600 + q.p) * 0.01) * w, y = q.y * h;
        if (kind === 'dumped') ctx.fillStyle = 'rgba(140,150,147,' + (0.25 + 0.4 * q.y).toFixed(2) + ')';
        else ctx.fillStyle = q.y < 0.35 ? 'rgba(120,226,75,' + (0.85 - q.y).toFixed(2) + ')' : 'rgba(191,198,196,.55)';
        ctx.fillRect(x, y, q.s, q.s);
      });
      if (on) raf = requestAnimationFrame(draw);
    };
    size();
    window.addEventListener('resize', size);
    new IntersectionObserver((en) => { on = en[0].isIntersecting && !reduced; cancelAnimationFrame(raf); if (on) raf = requestAnimationFrame(draw); else draw(0); }).observe(canvas);
  }

  /* ---------- documents assemble themselves (hospitals / buyers heroes) ---------- */
  function documentHeroes() {
    $$('.page-hero .doc').forEach((doc) => {
      const tl = gsap.timeline({ delay: 0.2, defaults: { ease: 'expo.out' } });
      tl.from(doc, { y: 80, rotate: rtl ? -4 : 4, autoAlpha: 0, duration: 1.3 })
        .from($$('.doc__fields > div, .doc__statement, .doc__result', doc), { y: 16, autoAlpha: 0, duration: 0.8, stagger: 0.07 }, '-=0.8');
      const sig = $$('.doc__sign path', doc);
      if (sig.length && window.DrawSVGPlugin) tl.from(sig, { drawSVG: '0%', duration: 1.2, ease: 'power2.inOut' }, '-=0.3');
      tl.from($('.doc__stamp', doc), { scale: 2.2, rotate: -40, autoAlpha: 0, duration: 0.55, ease: 'back.out(2.2)' }, '-=0.4');
      const code = $('[data-scramble-code]', doc);
      if (code && window.ScrambleTextPlugin) tl.to(code, { duration: 1.1, scrambleText: { text: code.textContent, chars: 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789', speed: 0.5 } }, 0.5);
    });
  }

  /* ---------- process page: illustrations draw themselves, rail fills ---------- */
  function processPage() {
    const list = $('.steps-full');
    if (!list) return;
    const rail = document.createElement('span');
    rail.className = 'steps-rail';
    rail.setAttribute('aria-hidden', 'true');
    rail.innerHTML = '<i></i>';
    list.prepend(rail);
    gsap.to($('i', rail), { scaleY: 1, ease: 'none', scrollTrigger: { trigger: list, start: 'top 60%', end: 'bottom 60%', scrub: true } });
    $$('.step-full__art .art').forEach((svg) => {
      const strokes = $$('path, line, rect, circle, ellipse', svg);
      if (window.DrawSVGPlugin) {
        gsap.from(strokes, { drawSVG: '0%', duration: 1.6, ease: 'power2.inOut', stagger: 0.02, scrollTrigger: { trigger: svg, start: 'top 80%', once: true } });
      }
      gsap.from($$('text', svg), { autoAlpha: 0, duration: 0.6, delay: 1.2, scrollTrigger: { trigger: svg, start: 'top 80%', once: true } });
    });
    $$('.step-full__num').forEach((n) => gsap.from(n, { scale: 0, rotate: -90, duration: 0.9, ease: 'back.out(2)', scrollTrigger: { trigger: n, start: 'top 85%', once: true } }));
  }

  /* ---------- pointer details ---------- */
  function pointerFx() {
    if (!finePointer || reduced) return;
    $$('[data-magnetic], .btn--primary, .btn--signal').forEach((el) => {
      const xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
      const yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'elastic.out(1, 0.4)' });
      el.addEventListener('pointermove', (e) => { const r = el.getBoundingClientRect(); xTo((e.clientX - r.left - r.width / 2) * 0.28); yTo((e.clientY - r.top - r.height / 2) * 0.35); });
      el.addEventListener('pointerleave', () => { xTo(0); yTo(0); });
    });
    $$('[data-spotlight]').forEach((el) => el.addEventListener('pointermove', (e) => {
      const r = el.getBoundingClientRect();
      el.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      el.style.setProperty('--my', (e.clientY - r.top) + 'px');
    }));
    const tile = $('.element');
    if (tile) {
      const rx = gsap.quickTo(tile, 'rotationX', { duration: 0.8, ease: 'power3.out' });
      const ry = gsap.quickTo(tile, 'rotationY', { duration: 0.8, ease: 'power3.out' });
      gsap.set(tile, { transformPerspective: 900 });
      tile.addEventListener('pointermove', (e) => { const r = tile.getBoundingClientRect(); ry(((e.clientX - r.left) / r.width - 0.5) * 22); rx(-((e.clientY - r.top) / r.height - 0.5) * 22); });
      tile.addEventListener('pointerleave', () => { rx(0); ry(0); });
    }
    const cursor = document.createElement('div');
    cursor.className = 'cursor';
    cursor.setAttribute('aria-hidden', 'true');
    document.body.appendChild(cursor);
    const cx = gsap.quickTo(cursor, 'x', { duration: 0.35, ease: 'power3.out' });
    const cy = gsap.quickTo(cursor, 'y', { duration: 0.35, ease: 'power3.out' });
    window.addEventListener('pointermove', (e) => { cx(e.clientX); cy(e.clientY); cursor.setAttribute('data-visible', ''); }, { passive: true });
    document.addEventListener('pointerleave', () => cursor.removeAttribute('data-visible'));
    document.addEventListener('pointerover', (e) => cursor.toggleAttribute('data-hover', !!e.target.closest('a, button, [role="slider"], [role="tab"], label, summary, input, select, textarea')));
  }

  /* ---------- footer wordmark ---------- */
  function footerMark() {
    const mark = $('.footer-mark svg');
    if (!mark) return;
    gsap.from(mark, { yPercent: 100, ease: 'none', scrollTrigger: { trigger: '.footer-mark', start: 'top bottom', end: 'bottom bottom', scrub: true } });
  }

  /* ---------- preloader ---------- */
  function preloader(done) {
    const el = $('[data-preloader]');
    if (!el || !html.classList.contains('is-loading')) { done(); return; }
    try { sessionStorage.setItem('argentum-intro', '1'); } catch (e) { /* private mode */ }
    const count = $('[data-preloader-count]', el);
    const o = { v: 0 };
    const tl = gsap.timeline();
    if (window.DrawSVGPlugin) tl.from($$('path', el), { drawSVG: '0%', duration: 1.3, ease: 'power2.inOut' }, 0);
    tl.to(o, { v: 47, duration: 1.3, ease: 'power2.inOut', onUpdate: () => { count.textContent = String(Math.round(o.v)).padStart(2, '0'); } }, 0)
      .to($$('path', el), { fill: '#78E24B', duration: 0.35 }, '-=0.15')
      .to(el, { yPercent: -100, duration: 0.95, ease: 'expo.inOut' }, '+=0.15')
      .add(done, '<+0.15')
      .add(finishLoading, '-=0.55')
      .set(el, { display: 'none' });
  }

  /* ---------- go ---------- */
  const start = () => {
    revealHeadings();
    revealBlocks();
    numbers();
    marquee();
    paperScroll();
    compare();
    documentHeroes();
    processPage();
    pointerFx();
    footerMark();
    preloader(heroIntro);
    html.classList.remove('motion-pending');
    ST.refresh();
  };
  if (reduced) {
    finishLoading();
    html.classList.remove('motion-pending');
    compare();
    return;
  }
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start); else start();
  // never leave a visitor behind the preloader
  setTimeout(finishLoading, 4500);
})();
