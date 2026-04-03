const toggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.site-nav');

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

  const sanitize = (value) =>
    value
      .replace(/[<>]/g, '')
      .replace(/[\r\n]+/g, ' ')
      .replace(/\s{2,}/g, ' ')
      .trim();

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
    }

    const formData = new FormData(form);
    const name = sanitize(String(formData.get('name') || ''));
    const company = sanitize(String(formData.get('company') || ''));
    const email = sanitize(String(formData.get('email') || ''));
    const phone = sanitize(String(formData.get('phone') || ''));
    const topic = sanitize(String(formData.get('topic') || 'Informazioni generali'));
    const message = sanitize(String(formData.get('message') || ''));

    const subject = encodeURIComponent(`${topic} - Schedine di Notifica - ${company}`);
    const body = encodeURIComponent(
      [
        'Richiesta dal sito commerciale',
        '',
        `Motivo: ${topic}`,
        `Nome: ${name}`,
        `Struttura: ${company}`,
        `Email: ${email}`,
        `Telefono: ${phone || '-'}`,
        '',
        'Messaggio:',
        message,
      ].join('\n')
    );

    if (status) {
      status.textContent = 'Richiesta pronta: si apre il tuo client email con i dati gia compilati.';
      status.classList.remove('is-error');
      status.classList.add('is-success');
    }

    window.location.href = `mailto:info@tanggo.org?subject=${subject}&body=${body}`;
  });

  if (topicField && window.location.hash === '#demo') {
    topicField.value = 'Richiesta demo';
  }
});
