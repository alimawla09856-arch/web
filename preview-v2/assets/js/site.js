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
          if (A.demo) {
            // Static design preview: no server, show the confirmation as it will appear
            await new Promise((r) => setTimeout(r, 700));
            form.hidden = true;
            success.hidden = false;
            success.focus();
            return;
          }
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
    const wanted = new URLSearchParams(location.search).get('form');
    const radio = wanted && $('input[value="' + CSS.escape(wanted) + '"]', chooser);
    if (radio) radio.checked = true;
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
        let res = { status: 200 };
        let json;
        if (A.demo) {
          const doc = A.demo[code.toUpperCase()];
          json = doc ? Object.assign({ found: true, revoked: false }, doc) : { found: false };
          await new Promise((r) => setTimeout(r, 400));
        } else {
          res = await fetch(A.verify + '?lang=' + A.lang + '&code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
          json = await res.json();
        }
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
  initForms();
  initChooser();
  initVerify();
  initConsent();
})();
