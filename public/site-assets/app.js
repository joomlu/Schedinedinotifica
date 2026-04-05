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
  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(open));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
}

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

  const openConfirmModal = () => {
    if (!confirmModal) return;
    confirmModal.hidden = false;
    confirmModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeConfirmModal = () => {
    if (!confirmModal) return;
    confirmModal.hidden = true;
    confirmModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  if (confirmModal && !confirmModal.dataset.bound) {
    confirmModal.dataset.bound = 'true';
    confirmModal.querySelectorAll('[data-contact-confirm-close]').forEach((node) => {
      node.addEventListener('click', closeConfirmModal);
    });

    document.addEventListener('keydown', (event) => {
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

    if (topicField && window.location.hash === '#demo') {
      topicField.value = 'Richiesta demo';
      if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
        window.jQuery(topicField).trigger('change');
      }
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

    const subject = encodeURIComponent(`${topic} - Schedine di Notifica - ${company}`);
    const body = encodeURIComponent(
      [
        'Richiesta dal sito commerciale',
        '',
        `Motivo: ${topic}`,
        `Nome: ${name}`,
        `Struttura: ${company}`,
        `Persona di contatto: ${contactPerson || '-'}`,
        `Localita: ${city || '-'}`,
        `Email: ${email}`,
        `Telefono: ${phone || '-'}`,
        `Cellulare: ${mobile || '-'}`,
        `Sito internet: ${websiteUrl || '-'}`,
        `Orario preferito di contatto: ${contactAnytime ? 'Qualsiasi orario' : (contactTime || '-')}`,
        '',
        'Messaggio:',
        message,
      ].join('\n')
    );

    if (status) {
      status.textContent = '';
      status.classList.remove('is-error');
      status.classList.remove('is-success');
    }

    openConfirmModal();
    window.location.href = `mailto:info@tanggo.org?subject=${subject}&body=${body}`;
  });

  if (topicField && window.location.hash === '#demo') {
    topicField.value = 'Richiesta demo';
    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
      window.jQuery(topicField).trigger('change');
    }
  }
});
