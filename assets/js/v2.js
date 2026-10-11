/* Argentum v2 — scenes for the photo-led design (GSAP + ScrollTrigger).
   Runs after motion.js, which already provides smooth scrolling, line
   reveals, magnetic buttons and the pointer follower. */
(() => {
  'use strict';

  const gsap = window.gsap;
  const ST = window.ScrollTrigger;
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const html = document.documentElement;
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const rtl = html.dir === 'rtl';
  const dir = rtl ? -1 : 1;

  /* ---------- full-screen menu (works without GSAP too) ---------- */
  function menu() {
    const btn = $('[data-v2-menu-toggle]');
    const panel = $('[data-v2-menu]');
    if (!btn || !panel) return;
    const label = $('.v2-menu-btn__label', btn);
    const links = $$('.v2-menu__nav a', panel);
    const figures = $$('[data-photo-for]', panel);
    const show = (key) => figures.forEach((f) => f.toggleAttribute('data-on', f.dataset.photoFor === key));
    show(links[0] && links[0].dataset.photo);
    links.forEach((a) => {
      a.addEventListener('pointerenter', () => show(a.dataset.photo));
      a.addEventListener('focus', () => show(a.dataset.photo));
    });
    let open = false;
    const set = (state) => {
      open = state;
      btn.setAttribute('aria-expanded', String(open));
      if (label) label.textContent = open ? label.dataset.close : label.dataset.open;
      document.body.toggleAttribute('data-nav-open', open);
      if (open) {
        panel.hidden = false;
        if (gsap && !reduced) {
          gsap.fromTo(panel, { clipPath: 'inset(0% 0% 100% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 0.8, ease: 'expo.inOut' });
          gsap.from(links, { yPercent: 60, autoAlpha: 0, duration: 0.9, ease: 'expo.out', stagger: 0.06, delay: 0.25 });
        }
        // focus the first link once it is visible (it fades in)
        if (links[0]) setTimeout(() => links[0].focus({ preventScroll: true }), gsap && !reduced ? 450 : 0);
      } else if (gsap && !reduced) {
        gsap.to(panel, { clipPath: 'inset(0% 0% 100% 0%)', duration: 0.6, ease: 'expo.inOut', onComplete: () => { panel.hidden = true; } });
      } else {
        panel.hidden = true;
      }
    };
    btn.addEventListener('click', () => set(!open));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && open) { set(false); btn.focus(); } });
    links.forEach((a) => a.addEventListener('click', () => set(false)));
  }
  menu();

  if (!gsap || !ST) return;
  gsap.registerPlugin(...[ST, window.SplitText, window.ScrambleTextPlugin].filter(Boolean));
  const mm = gsap.matchMedia();

  /* ---------- hero: X-ray film becomes silver, silver becomes green ---------- */
  function hero() {
    const root = $('[data-v2-hero]');
    if (!root) return;
    const pin = $('.v2-hero__pin', root);
    const frame = $('[data-v2-frame]', root);
    const text = $('.v2-hero__text', root);
    const silver = $('[data-v2-layer="silver"]', root);
    const green = $('[data-v2-layer="green"]', root);
    const scan = $('[data-v2-scan]', root);
    const stack = $('[data-v2-words]', root);
    const meter = $$('[data-v2-meter] li', root);
    const slogan = $('[data-v2-slogan]', root);
    const filmImg = $('.v2-layer--film img', root);
    const extras = $$('.v2-word, .v2-meter, .v2-frame__tile', root);
    const setMeter = (i) => meter.forEach((li, k) => li.toggleAttribute('data-on', k === i));

    const wipes = (tl) => {
      tl.addLabel('silver')
        .fromTo(silver, { clipPath: 'inset(100% 0% 0% 0%)' }, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1, ease: 'none' }, 'silver')
        .fromTo(scan, { top: '100%', autoAlpha: 1 }, { top: '0%', duration: 1, ease: 'none' }, 'silver')
        .to(scan, { autoAlpha: 0, duration: 0.1 })
        .to(stack, { yPercent: -100 / 3, duration: 0.5, ease: 'power3.inOut' }, 'silver+=0.45')
        .to(filmImg, { scale: 1.1, duration: 1, ease: 'none' }, 'silver')
        .to({}, { duration: 0.4 })
        .addLabel('green')
        .fromTo(green, { clipPath: 'circle(0% at 50% 50%)' }, { clipPath: 'circle(80% at 50% 50%)', duration: 1, ease: 'power2.in' }, 'green')
        .to(stack, { yPercent: -200 / 3, duration: 0.5, ease: 'power3.inOut' }, 'green+=0.45')
        .fromTo(slogan, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: 0.5 }, 'green+=0.7')
        .to({}, { duration: 0.4 });
      return tl;
    };
    const meterFrom = (self) => {
      const p = self.progress;
      setMeter(p < 0.52 ? 0 : p < 0.8 ? 1 : 2);
    };

    mm.add('(min-width: 64rem)', () => {
      html.classList.add('v2-hero-on');
      // the frame starts cropped to the right-hand column, then opens to the full screen
      const startClip = () => {
        const pr = pin.getBoundingClientRect();
        const tr = text.getBoundingClientRect();
        const gutter = Math.max(24, (pr.width - 1216) / 2);
        const side = rtl ? pr.right - tr.left + 48 : tr.right - pr.left + 48;
        return rtl
          ? `inset(104px ${side}px 32px ${gutter}px round 26px)`
          : `inset(104px ${gutter}px 32px ${side}px round 26px)`;
      };
      const tl = gsap.timeline({
        scrollTrigger: { trigger: root, pin, start: 'top top', end: '+=260%', scrub: 1, invalidateOnRefresh: true, refreshPriority: 6, onUpdate: meterFrom },
      });
      tl.fromTo(frame, { clipPath: startClip }, { clipPath: 'inset(0px 0px 0px 0px round 0px)', duration: 1, ease: 'power2.inOut' })
        .to(text, { x: -80 * dir, autoAlpha: 0, duration: 0.55, ease: 'power2.in' }, 0)
        .fromTo(extras, { autoAlpha: 0, y: 24 }, { autoAlpha: 1, y: 0, duration: 0.4, stagger: 0.05 }, 0.65)
        .to({}, { duration: 0.3 });
      wipes(tl);
      return () => { html.classList.remove('v2-hero-on'); gsap.set([frame, text, extras], { clearProps: 'all' }); };
    });
    mm.add('(max-width: 63.99rem)', () => {
      const tl = gsap.timeline({
        scrollTrigger: { trigger: frame, pin: frame, start: 'center center', end: '+=170%', scrub: 1, refreshPriority: 6, onUpdate: meterFrom },
      });
      wipes(tl);
    });
  }

  /* ---------- manifesto: words light up as you read ---------- */
  function manifesto() {
    const p = $('[data-v2-manifesto]');
    if (!p) return;
    if (window.SplitText) {
      window.SplitText.create(p, {
        type: 'words', autoSplit: true,
        onSplit: (self) => gsap.fromTo(self.words, { opacity: 0.14 }, { opacity: 1, ease: 'none', stagger: 0.1, scrollTrigger: { trigger: p, start: 'top 82%', end: 'bottom 50%', scrub: true } }),
      });
    }
    $$('.v2-chip', p).forEach((chip) => {
      gsap.fromTo(chip, { scale: 0.3, rotate: -14 * dir }, { scale: 1, rotate: 0, ease: 'back.out(1.8)', scrollTrigger: { trigger: chip, start: 'top 85%', end: 'top 60%', scrub: true } });
    });
  }

  /* ---------- process: four large cards slide sideways ---------- */
  function processScroll() {
    const root = $('[data-v2-process]');
    if (!root) return;
    const track = $('[data-v2-track]', root);
    const bar = $('[data-v2-bar]', root);
    const now = $('[data-v2-step-now]', root);
    const steps = $$('.v2-step', root);
    mm.add('(min-width: 64rem)', () => {
      html.classList.add('v2-h');
      const dist = () => Math.max(0, track.scrollWidth - window.innerWidth);
      const tween = gsap.to(track, {
        x: () => -dir * dist(), ease: 'none',
        scrollTrigger: {
          trigger: root, pin: $('.v2-process__pin', root), start: 'top top', end: () => '+=' + dist(), scrub: 1, invalidateOnRefresh: true, refreshPriority: 5,
          onUpdate: (self) => {
            gsap.set(bar, { scaleX: self.progress });
            now.textContent = '0' + Math.min(steps.length, 1 + Math.floor(self.progress * steps.length * 0.999));
          },
        },
      });
      steps.forEach((step) => {
        const img = $('.v2-step__media img', step);
        gsap.fromTo(img, { xPercent: -7 * dir }, {
          xPercent: 7 * dir, ease: 'none',
          scrollTrigger: { trigger: step, containerAnimation: tween, start: rtl ? 'right left' : 'left right', end: rtl ? 'left right' : 'right left', scrub: true },
        });
      });
      return () => { html.classList.remove('v2-h'); gsap.set(track, { clearProps: 'all' }); };
    });
    mm.add('(max-width: 63.99rem)', () => {
      steps.forEach((s) => gsap.from(s, { y: 60, autoAlpha: 0, duration: 1, ease: 'expo.out', scrollTrigger: { trigger: s, start: 'top 88%', once: true } }));
    });
  }

  /* ---------- documents fan out like a hand of cards ---------- */
  function proofDeck() {
    const root = $('[data-v2-proof]');
    if (!root) return;
    const deck = $('[data-v2-deck]', root);
    const cards = $$('.v2-proof__card', root);
    const codes = $$('[data-scramble-code]', root);
    let decoded = false;
    const decode = () => {
      if (decoded || !window.ScrambleTextPlugin) return;
      decoded = true;
      codes.forEach((c, i) => gsap.to(c, { duration: 1.1, delay: i * 0.15, scrambleText: { text: c.textContent, chars: 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789', speed: 0.5 } }));
    };
    mm.add('(min-width: 64rem)', () => {
      html.classList.add('v2-deck-on');
      // documents keep their paper width and shrink to fit the deck height
      const fit = () => deck.style.setProperty('--fit', Math.min(1, (deck.clientHeight * 0.94) / Math.max(...cards.map((c) => c.offsetHeight))).toFixed(3));
      fit();
      ST.addEventListener('refreshInit', fit);
      gsap.set(cards, { xPercent: -50, yPercent: -50, rotate: (i) => [-7, 2, 8][i] * dir, y: (i) => [18, 0, 26][i] });
      const spread = () => deck.clientWidth * 0.29;
      const tl = gsap.timeline({
        scrollTrigger: { trigger: root, pin: $('.v2-proof__pin', root), start: 'top top', end: '+=130%', scrub: 1, invalidateOnRefresh: true, refreshPriority: 4,
          onUpdate: (self) => { if (self.progress > 0.45) decode(); } },
      });
      tl.to(cards[0], { x: () => -spread() * dir, y: 30, rotate: -9 * dir, duration: 1, ease: 'power2.out' }, 0)
        .to(cards[2], { x: () => spread() * dir, y: 40, rotate: 9 * dir, duration: 1, ease: 'power2.out' }, 0)
        .to(cards[1], { y: -24, rotate: 0, scale: 1.05, duration: 1, ease: 'power2.out' }, 0)
        .to({}, { duration: 0.35 });
      return () => { html.classList.remove('v2-deck-on'); ST.removeEventListener('refreshInit', fit); deck.style.removeProperty('--fit'); gsap.set(cards, { clearProps: 'all' }); };
    });
    mm.add('(max-width: 63.99rem)', () => {
      cards.forEach((c) => gsap.from(c, { y: 60, rotate: 4 * dir, autoAlpha: 0, duration: 1, ease: 'expo.out', scrollTrigger: { trigger: c, start: 'top 90%', once: true, onEnter: decode } }));
    });
  }

  /* ---------- Beirut: slow zoom, region names drift ---------- */
  function lebanon() {
    const root = $('[data-v2-lebanon]');
    if (!root) return;
    gsap.fromTo($('.v2-lebanon__media img', root), { scale: 1.22, yPercent: -4 }, { scale: 1, yPercent: 4, ease: 'none', scrollTrigger: { trigger: root, start: 'top bottom', end: 'bottom top', scrub: true } });
    const track = $('[data-v2-regions]', root);
    if (track) gsap.to(track, { xPercent: rtl ? 50 : -50, duration: 40, ease: 'none', repeat: -1 });
  }

  /* ---------- the word GREEN, filled with a real leaf ---------- */
  function greenWord() {
    const root = $('[data-v2-green]');
    if (!root) return;
    const word = $('.v2-green__word', root);
    gsap.fromTo(word, { scale: 1.9, backgroundPosition: '50% 0%' }, { scale: 1, backgroundPosition: '50% 100%', ease: 'none', scrollTrigger: { trigger: root, start: 'top bottom', end: 'center center', scrub: true } });
    gsap.from($$('.v2-green__copy > *', root), { y: 30, autoAlpha: 0, duration: 1, stagger: 0.1, ease: 'expo.out', scrollTrigger: { trigger: $('.v2-green__copy', root), start: 'top 85%', once: true } });
  }

  /* ---------- photos open up as they enter ---------- */
  function clipReveals() {
    $$('[data-reveal-clip]').forEach((el) => {
      const img = $('img', el);
      gsap.fromTo(el, { clipPath: 'inset(14% 10% 14% 10% round 26px)' }, { clipPath: 'inset(0% 0% 0% 0% round 0px)', duration: 1.5, ease: 'expo.out', scrollTrigger: { trigger: el, start: 'top 85%', once: true } });
      if (img) gsap.fromTo(img, { scale: 1.3 }, { scale: 1, duration: 1.8, ease: 'expo.out', scrollTrigger: { trigger: el, start: 'top 85%', once: true } });
    });
    $$('.v2-cta__media').forEach((el) => gsap.fromTo(el, { borderRadius: '26px' }, { borderRadius: '120px 26px 26px 26px', ease: 'none', scrollTrigger: { trigger: el, start: 'top 80%', end: 'bottom 30%', scrub: true } }));
  }

  /* ---------- interior pages: photo hero ---------- */
  function pageHero() {
    const root = $('[data-v2-page-hero]');
    if (!root) return;
    gsap.fromTo($('.v2-page-hero__media img', root), { scale: 1.08 }, { scale: 1.2, yPercent: 8, ease: 'none', scrollTrigger: { trigger: root, start: 'top top', end: 'bottom top', scrub: true } });
    const tl = gsap.timeline({ delay: 0.15, defaults: { ease: 'expo.out' } });
    tl.from($$('.v2-page-hero .v2-kicker, .v2-page-hero__sub, .v2-page-hero .btn-row', root), { y: 30, autoAlpha: 0, duration: 1.1, stagger: 0.1 }, 0.3);
    const doc = $('.v2-page-hero__doc .doc', root);
    if (doc) {
      tl.from(doc, { y: 120, rotate: 8 * dir, autoAlpha: 0, duration: 1.4 }, 0.2)
        .from($$('.doc__fields > div, .doc__statement, .doc__result', doc), { y: 14, autoAlpha: 0, duration: 0.7, stagger: 0.06 }, 0.8);
      const sig = $$('.doc__sign path', doc);
      if (sig.length && window.DrawSVGPlugin) tl.from(sig, { drawSVG: '0%', duration: 1.1, ease: 'power2.inOut' }, 1.2);
      tl.from($('.doc__stamp', doc), { scale: 2.2, rotate: -40, autoAlpha: 0, duration: 0.55, ease: 'back.out(2.2)' }, 1.6);
      const code = $('[data-scramble-code]', doc);
      if (code && window.ScrambleTextPlugin) tl.to(code, { duration: 1.1, scrambleText: { text: code.textContent, chars: 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789', speed: 0.5 } }, 0.9);
      gsap.to(doc, { rotate: -3 * dir, ease: 'none', scrollTrigger: { trigger: root, start: 'top top', end: 'bottom top', scrub: true } });
    }
  }

  /* ---------- hover a list item, its photo follows the pointer ---------- */
  function hoverPhotos() {
    const preview = $('[data-hover-preview]');
    if (!preview || !window.matchMedia('(pointer: fine)').matches) return;
    const figs = $$('[data-photo-for]', preview);
    const xTo = gsap.quickTo(preview, 'x', { duration: 0.45, ease: 'power3.out' });
    const yTo = gsap.quickTo(preview, 'y', { duration: 0.45, ease: 'power3.out' });
    $$('[data-hover-photos] li[data-photo]').forEach((li) => {
      li.addEventListener('pointerenter', () => {
        figs.forEach((f) => f.toggleAttribute('data-on', f.dataset.photoFor === li.dataset.photo));
        gsap.to(preview, { autoAlpha: 1, scale: 1, rotate: -4 * dir, duration: 0.35, ease: 'power3.out' });
      });
      li.addEventListener('pointerleave', () => gsap.to(preview, { autoAlpha: 0, scale: 0.9, duration: 0.25 }));
      li.addEventListener('pointermove', (e) => { xTo(e.clientX + 24 * dir); yTo(e.clientY - 120); });
    });
  }

  const start = () => {
    if (reduced) { html.classList.remove('motion-pending'); return; }
    hero();
    manifesto();
    processScroll();
    proofDeck();
    lebanon();
    greenWord();
    clipReveals();
    pageHero();
    hoverPhotos();
    ST.sort();
    ST.refresh();
  };
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(start); else start();
})();
