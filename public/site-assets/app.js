const toggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.site-nav');
const fallbackCities = [
  'Bellaria-Igea Marina',
  'Rimini',
  'Riccione',
  'Cesenatico',
  'Milano',
  'Roma',
  'Bologna',
  'Torino',
  'Ancona',
  'Belluno',
  'Jesolo',
  'Cattolica',
  'Misano Adriatico',
  'Cervia',
  'Pesaro',
  'Firenze',
  'Venezia',
  'Napoli',
  'Verona',
  'Parma',
];

if (toggle && nav) {
  const closeMenu = () => {
    nav.classList.remove('open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Apri menu');
    document.body.classList.remove('menu-open');
  };
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && nav.classList.contains('open')) { closeMenu(); toggle.focus(); }
  });
  window.matchMedia('(min-width: 1281px)').addEventListener('change', closeMenu);
  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Chiudi menu' : 'Apri menu');
    document.body.classList.toggle('menu-open', open);
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-label', 'Apri menu');
      document.body.classList.remove('menu-open');
    });
  });
}

// Collega anche il controllo Select2 generato all'etichetta del campo nativo.
const labelContactSelect = select => {
  const label = select.labels?.[0]?.querySelector('span');
  const control = select.nextElementSibling?.querySelector('[role="combobox"]');
  if (!label || !control) return;
  label.id ||= `contact-${select.name}-label`;
  const valueId = control.querySelector('.select2-selection__rendered')?.id;
  control.setAttribute('aria-labelledby', [label.id, valueId].filter(Boolean).join(' '));
};

document.querySelectorAll('[data-ui="contact-city-select"]').forEach((select) => {
  const $ = window.jQuery;
  if (!$ || !$.fn || typeof $.fn.select2 !== 'function') {
    return;
  }

  const placeholder = select.getAttribute('data-placeholder') || 'Seleziona...';

  const buildFallbackResults = (term) => {
    const normalized = String(term || '').trim().toLowerCase();
    const matches = normalized
      ? fallbackCities.filter((city) => city.toLowerCase().includes(normalized))
      : fallbackCities;

    return Array.from(new Set(matches)).slice(0, 12).map((city) => ({
      id: city,
      text: city,
    }));
  };

  $(select).select2({
    width: '100%',
    language: 'it',
    placeholder,
    allowClear: true,
    minimumInputLength: 0,
    tags: true,
    theme: 'default',
    ajax: {
      delay: 180,
      transport: (params, success, failure) => {
        const term = params.data && params.data.term ? params.data.term : '';
        const query = String(term || '').trim();

        $.ajax({
          url: '/api/geo/comuni',
          dataType: 'json',
          data: { q: query, page: 1 },
        })
          .done((payload) => {
            const results = Array.isArray(payload?.results) ? payload.results : [];
            const normalized = results.map((item) => ({
              id: item.nome || item.text || item.id,
              text: item.nome || item.text || item.id,
            })).filter((item) => item.id && item.text);

            if (normalized.length > 0) {
              success({ results: normalized });
              return;
            }

            success({ results: buildFallbackResults(query) });
          })
          .fail(() => {
            success({ results: buildFallbackResults(query) });
            if (typeof failure === 'function') failure();
          });
      },
      processResults: (data) => data,
    },
    createTag: (params) => {
      const term = String(params.term || '').trim();
      if (!term) return null;

      return {
        id: term,
        text: term,
        newTag: true,
      };
    },
  });
  labelContactSelect(select);
});

document.querySelectorAll('[data-ui="contact-basic-select"]').forEach((select) => {
  const $ = window.jQuery;
  if (!$ || !$.fn || typeof $.fn.select2 !== 'function') {
    return;
  }

  const placeholder = select.getAttribute('data-placeholder') || 'Seleziona...';

  $(select).select2({
    width: '100%',
    language: 'it',
    placeholder,
    allowClear: true,
    minimumResultsForSearch: Infinity,
    theme: 'default',
  });
  labelContactSelect(select);
});

document.querySelectorAll('input[data-ui="contact-datetime"]').forEach((input) => {
  const flatpickr = window.flatpickr;
  if (typeof flatpickr !== 'function') {
    return;
  }

  if (window.flatpickr?.l10ns?.it) {
    flatpickr.localize(window.flatpickr.l10ns.it);
  }

  const picker = flatpickr(input, {
    locale: 'it',
    enableTime: true,
    time_24hr: true,
    dateFormat: 'd/m/Y H:i',
    allowInput: true,
    disableMobile: true,
    minuteIncrement: 15,
  });

  const wrapper = input.closest('.form-grid');
  const anytimeToggle = wrapper ? wrapper.querySelector('[data-contact-anytime]') : null;

  const syncAnytime = () => {
    if (!anytimeToggle) return;
    if (anytimeToggle.checked) {
      picker.clear();
      input.value = 'Qualsiasi orario';
      input.setAttribute('disabled', 'disabled');
      return;
    }

    input.removeAttribute('disabled');
    if (input.value === 'Qualsiasi orario') {
      input.value = '';
    }
  };

  if (anytimeToggle) {
    anytimeToggle.addEventListener('change', syncAnytime);
    syncAnytime();
  }
});

document.querySelectorAll('[data-tabs]').forEach((tabsRoot) => {
  const buttons = tabsRoot.querySelectorAll('.tab-button');
  const panels = tabsRoot.querySelectorAll('.tab-panel');

  buttons.forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-tab-target');

      buttons.forEach((item) => {
        item.classList.remove('active');
        item.setAttribute('aria-selected', 'false');
      });

      panels.forEach((panel) => {
        panel.classList.remove('active');
      });

      button.classList.add('active');
      button.setAttribute('aria-selected', 'true');

      const panel = tabsRoot.querySelector(`#${targetId}`);
      if (panel) {
        panel.classList.add('active');
      }
    });
  });
});

document.querySelectorAll('[data-demo-form]').forEach((form) => {
  const status = form.querySelector('.form-status');
  const topicField = form.querySelector('[data-contact-topic]');
  const confirmModal = document.querySelector('[data-contact-confirm]');

  let previousFocus = null;
  const openConfirmModal = () => {
    previousFocus = form.querySelector('[type=submit]') || document.activeElement;
    if (!confirmModal) return;
    confirmModal.hidden = false;
    confirmModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    confirmModal.querySelector('[role=dialog]').focus();
  };

  const closeConfirmModal = () => {
    if (!confirmModal) return;
    confirmModal.hidden = true;
    confirmModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    previousFocus?.focus();
  };

  if (confirmModal && !confirmModal.dataset.bound) {
    confirmModal.dataset.bound = 'true';
    confirmModal.querySelectorAll('[data-contact-confirm-close]').forEach((node) => {
      node.addEventListener('click', closeConfirmModal);
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Tab' && !confirmModal.hidden) {
        const buttons = [...confirmModal.querySelectorAll('button:not([disabled]), a[href]')];
        const first = buttons[0], last = buttons[buttons.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement.matches('[role=dialog]'))) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
      }
      if (event.key === 'Escape' && !confirmModal.hidden) {
        closeConfirmModal();
      }
    });
  }

  const sanitize = (value) =>
    value
      .replace(/[<>]/g, '')
      .replace(/[\r\n]+/g, ' ')
      .replace(/\s{2,}/g, ' ')
      .trim();

  const isValidWebsite = (value) => {
    const normalized = String(value || '').trim();
    if (!normalized) return true;

    const withoutProtocol = normalized.replace(/^https?:\/\//i, '');
    return /^(?:[a-z0-9-]+\.)+[a-z]{2,}(?:[/:?#].*)?$/i.test(withoutProtocol);
  };

  form.addEventListener('submit', (event) => {
    event.preventDefault();

    const honeypot = form.querySelector('input[name="website"]');
    if (honeypot && honeypot.value.trim() !== '') {
      return;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      if (status) {
        status.textContent = 'Controlla i campi obbligatori prima di continuare.';
        status.classList.remove('is-success');
        status.classList.add('is-error');
      }
      return;
    }

    const formData = new FormData(form);
    const name = sanitize(String(formData.get('name') || ''));
    const company = sanitize(String(formData.get('company') || ''));
    const contactPerson = sanitize(String(formData.get('contact_person') || ''));
    const city = sanitize(String(formData.get('city') || ''));
    const email = sanitize(String(formData.get('email') || ''));
    const phone = sanitize(String(formData.get('phone') || ''));
    const mobile = sanitize(String(formData.get('mobile') || ''));
    const websiteUrl = sanitize(String(formData.get('website_url') || ''));
    const contactTime = sanitize(String(formData.get('contact_time') || ''));
    const contactAnytime = String(formData.get('contact_anytime') || '') === '1';
    const topic = sanitize(String(formData.get('topic') || 'Informazioni generali'));
    const message = sanitize(String(formData.get('message') || ''));

    if (!isValidWebsite(websiteUrl)) {
      if (status) {
        status.textContent = 'Inserisci il sito internet in un formato valido, per esempio dominio.ext oppure https://dominio.ext.';
        status.classList.remove('is-success');
        status.classList.add('is-error');
      }

      const websiteField = form.querySelector('input[name="website_url"]');
      if (websiteField) {
        websiteField.focus();
      }
      return;
    }

    if (status) {
      status.textContent = '';
      status.classList.remove('is-error');
      status.classList.remove('is-success');
    }

    const payload = {
      company,
      name,
      contact_person: contactPerson,
      city,
      email,
      phone,
      mobile,
      website_url: websiteUrl,
      contact_time: contactAnytime ? 'Qualsiasi orario' : contactTime,
      contact_datetime_iso: (!contactAnytime && contactTime) ? (() => {
        const date = window.flatpickr && typeof window.flatpickr.parseDate === 'function'
          ? window.flatpickr.parseDate(contactTime, 'd/m/Y H:i')
          : null;
        return date ? date.toISOString() : null;
      })() : null,
      contact_anytime: contactAnytime,
      topic,
      plan: String(formData.get('plan') || ''),
      interest: String(formData.get('interest') || ''),
      consent: form.querySelector('[name=consent]')?.checked === true,
      max_full: form.querySelector('[data-commercial-max]')?.checked === true,
      message,
      website: '',
    };

    const submitButton = form.querySelector('[type=submit]');
    if (form.dataset.submitting === 'true') return;
    form.dataset.submitting = 'true';
    if (submitButton) submitButton.disabled = true;
    form.setAttribute('aria-busy', 'true');
    fetch('/contatti/richiesta', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    })
      .then(async (response) => {
        if (response.status === 429) {
          throw { message: 'Hai inviato diverse richieste in poco tempo. Attendi un minuto prima di riprovare.' };
        }
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          throw data;
        }
        if (data.ok !== true || !Number.isSafeInteger(data.lead_id) || data.lead_id <= 0) {
          throw { message: 'La conferma di registrazione non è valida. I dati del modulo sono conservati: contattaci prima di ripetere la richiesta.' };
        }
        return data;
      })
      .then(() => {
        form.reset();
        if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
          window.jQuery(form.querySelector('[data-ui="contact-city-select"]')).val(null).trigger('change');
          window.jQuery(form.querySelector('[data-contact-topic]')).val(null).trigger('change');
        }
        const anytimeToggle = form.querySelector('[data-contact-anytime]');
        if (anytimeToggle) {
          anytimeToggle.dispatchEvent(new Event('change'));
        }
        openConfirmModal();
      })
      .catch((error) => {
        let messageText = 'Non siamo riusciti a registrare la richiesta. Riprova tra poco.';
        if (error && typeof error === 'object') {
          const firstError = Object.values(error.errors || {})[0];
          if (Array.isArray(firstError) && firstError[0]) {
            messageText = firstError[0];
          } else if (typeof error.message === 'string' && error.message.trim() !== '') {
            messageText = error.message;
          }
        }

        if (status) {
          status.textContent = messageText;
          status.classList.remove('is-success');
          status.classList.add('is-error');
        }
      })
      .finally(() => {
        form.dataset.submitting = 'false';
        form.removeAttribute('aria-busy');
        if (submitButton) submitButton.disabled = false;
      });
  });

  if (topicField && window.location.hash === '#demo' && form.dataset.contactRestored !== 'true') {
    topicField.value = 'Richiesta demo';
    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
      window.jQuery(topicField).trigger('change');
    }
  }
});

window.addEventListener('hashchange', () => {
  const field = document.querySelector('[data-contact-topic]');
  if (!field || location.hash !== '#demo' || field.closest('form')?.dataset.contactRestored === 'true') return;
  field.value = 'Richiesta demo';
  field.dispatchEvent(new Event('change', { bubbles: true }));
  if (window.jQuery?.fn?.select2) window.jQuery(field).trigger('change');
});
// Percorso del prodotto: i pannelli restano leggibili anche senza JavaScript.
document.querySelectorAll('[data-product-tour]').forEach(tour => {
  const tabs = [...tour.querySelectorAll('[role="tab"]')];
  const panels = [...tour.querySelectorAll('[role="tabpanel"]')];
  const previous = tour.querySelector('[data-tour-previous]');
  const next = tour.querySelector('[data-tour-next]');
  const counter = tour.querySelector('[data-tour-counter]');
  let current = 0;
  const activate = (index, focus = false) => {
    current = index;
    tabs.forEach((tab, i) => {
      tab.setAttribute('aria-selected', String(i === current));
      tab.tabIndex = i === current ? 0 : -1;
      panels[i].hidden = i !== current;
    });
    previous.disabled = current === 0;
    next.disabled = current === tabs.length - 1;
    counter.textContent = `Passaggio ${current + 1} di ${tabs.length}`;
    if (focus) tabs[current].focus();
  };
  tabs.forEach((tab, index) => {
    tab.addEventListener('click', () => activate(index));
    tab.addEventListener('keydown', event => {
      let target;
      if (event.key === 'ArrowRight') target = (index + 1) % tabs.length;
      if (event.key === 'ArrowLeft') target = (index + tabs.length - 1) % tabs.length;
      if (event.key === 'Home') target = 0;
      if (event.key === 'End') target = tabs.length - 1;
      if (target !== undefined) { event.preventDefault(); activate(target, true); }
    });
  });
  previous.addEventListener('click', () => activate(Math.max(0, current - 1), true));
  next.addEventListener('click', () => activate(Math.min(tabs.length - 1, current + 1), true));
  activate(0);
});

// Il catalogo condiviso è l'unica fonte di nomi, ambiti e prezzi nel riepilogo.
document.querySelectorAll('[data-demo-form]').forEach(form => {
  const plan = form.querySelector('[data-commercial-plan]');
  const maxFull = form.querySelector('[data-commercial-max]');
  const interest = form.querySelector('[data-commercial-interest]');
  const summary = form.querySelector('[data-commercial-summary]');
  const clear = form.querySelector('[data-commercial-clear]');
  if (!plan || !maxFull || !interest || !summary || !clear) return;
  const validPlans = ['solo', 'duo', 'multi', 'oltre7', 'strutture', 'oltre2'];
  const params = new URLSearchParams(location.search);
  if (form.dataset.contactRestored !== 'true') {
    plan.value = validPlans.includes(params.get('piano')) ? params.get('piano') : '';
    maxFull.checked = params.get('servizio') === 'max-full';
    interest.value = [...interest.options].some(option => option.value === params.get('interesse')) ? params.get('interesse') : '';
  }
  let catalog = null;
  const update = () => {
    summary.replaceChildren();
    const add = (text, strong = false) => {
      const node = document.createElement(strong ? 'strong' : 'p');
      node.textContent = text;
      summary.append(node);
    };
    if (!plan.value && !maxFull.checked && !interest.value) {
      add('Nessun piano o servizio selezionato. Definiamo insieme il tuo contesto.');
      return;
    }
    if (!catalog) {
      add('Scelta conservata. I dettagli del piano, del modulo e del servizio saranno confermati nella risposta.');
      return;
    }
    if (interest.value && catalog.interests?.[interest.value]) add(`Interesse richiesto: ${catalog.interests[interest.value]}`, true);
    const selected = catalog.plans[plan.value];
    if (selected) {
      add(`${selected.name} · ${selected.scope}`, true);
      add(selected.price);
      add(catalog.annual_note);
    }
    if (maxFull.checked) {
      add(`${catalog.max_full.name} · ${catalog.max_full.price}`, true);
      add('Servizio aggiuntivo all’abbonamento.');
      add(catalog.max_full.commercial_note);
    }
    add(catalog.extras_note);
  };
  plan.addEventListener('change', update);
  maxFull.addEventListener('change', update);
  interest.addEventListener('change', update);
  clear.addEventListener('click', () => { plan.value = ''; maxFull.checked = false; interest.value = ''; update(); });
  form.addEventListener('reset', () => queueMicrotask(update));
  window.addEventListener('pageshow', update);
  update();
  fetch('/site-assets/catalogo-commerciale.json', { cache: 'no-store', headers: { Accept: 'application/json' } })
    .then(response => { if (!response.ok) throw new Error('Catalogo non disponibile'); return response.json(); })
    .then(data => {
      if (!data.plans || !data.max_full || !data.annual_note || !data.extras_note) throw new Error('Catalogo incompleto');
      catalog = data;
      update();
    })
    .catch(() => { catalog = null; update(); });
});
