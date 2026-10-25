import { test, expect, BASE_URL } from '../Support/playwright.js';
import { readFileSync } from 'node:fs';
import { createHash } from 'node:crypto';

const digest = bytes => createHash('sha256').update(bytes).digest('hex');

const catalog = JSON.parse(readFileSync(new URL('../../web/assets/catalogo-commerciale.json', import.meta.url), 'utf8'));

const pages = [['/', 'Meno burocrazia.'], ['/moduli', 'Ogni attività ha il suo spazio.'], ['/demo', 'Dai primi dati'], ['/prezzi', 'Il piano giusto.'], ['/contatti', 'La tua prossima accoglienza']];

for (const width of [1440, 768, 390]) {
  test(`sito pubblico: cinque pagine, navigazione e accessi a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    const errors = [];
    const failed = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => { if (response.status() >= 400 && new URL(response.url()).pathname.startsWith('/site-assets/')) failed.push(response.url()); });
    for (const file of ['sitemap.xml', 'robots.txt']) {
      const effective = await page.request.get(`${BASE_URL}/${file}`);
      expect(effective.status()).toBe(200);
      expect(digest(await effective.body())).toBe(digest(readFileSync(new URL(`../../public/${file}`, import.meta.url))));
    }
    for (const name of ['arrivi', 'richiesta', 'ospite', 'schedina', 'tassa']) {
      const asset = `img/prodotto-${name}.webp`;
      const effective = await page.request.get(`${BASE_URL}/site-assets/${asset}`);
      expect(effective.status()).toBe(200);
      expect(digest(await effective.body())).toBe(digest(readFileSync(new URL(`../../web/assets/${asset}`, import.meta.url))));
    }
    for (const asset of ['styles.css', 'app.js', 'catalogo-commerciale.json', 'img/foto-reception.webp', 'img/foto-assistenza.webp', 'img/foto-sviluppo.webp']) {
      const effective = await page.request.get(`${BASE_URL}/site-assets/${asset}`);
      expect(effective.status()).toBe(200);
      expect(digest(await effective.body())).toBe(digest(readFileSync(new URL(`../../web/assets/${asset}`, import.meta.url))));
    }
    if (width === 1440) {
      for (const intermediateWidth of [1280, 1266, 1151, 1024]) {
        await page.setViewportSize({ width: intermediateWidth, height: 1000 });
        await page.goto(`${BASE_URL}/`, { waitUntil: 'load' });
        if (intermediateWidth === 1266) {
          const copyFits = await page.locator('.hero-copy').evaluate(copy => {
            const rect = copy.getBoundingClientRect();
            return rect.left >= 0 && rect.right <= innerWidth && rect.left < innerWidth / 2;
          });
          expect(copyFits).toBe(true);
          await expect(page.locator('.hero-photo')).toHaveCSS('background-image', /foto-reception\.webp/);
        }
        await page.getByRole('button', { name: 'Apri menu', exact: true }).click();
        const cta = page.locator('.site-nav .nav-cta');
        await expect(cta).toBeVisible();
        const bounds = await cta.evaluate(link => {
          const rect = link.getBoundingClientRect();
          const range = document.createRange();
          range.selectNodeContents(link);
          const text = range.getBoundingClientRect();
          return { fits: text.left >= rect.left && text.right <= rect.right + 1 && text.top >= rect.top && text.bottom <= rect.bottom + 1, viewport: rect.left >= 0 && rect.right <= innerWidth, overflow: document.documentElement.scrollWidth > innerWidth };
        });
        expect(bounds).toEqual({ fits: true, viewport: true, overflow: false });
        await page.keyboard.press('Escape');
      }
      await page.setViewportSize({ width, height: 1000 });
    }
    for (const [url, title] of pages) {
      expect((await page.goto(`${BASE_URL}${url}`, { waitUntil: 'load' })).status()).toBe(200);
      await expect(page.locator('h1')).toContainText(title);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('main')).toBeVisible();
      if (url === '/') await expect(page.locator('.hero')).toHaveCSS('display', 'grid');
      if (width === 1440) {
        const cta = page.locator('.site-nav .nav-cta');
        const fits = await cta.evaluate(link => {
          const rect = link.getBoundingClientRect();
          const range = document.createRange(); range.selectNodeContents(link);
          const text = range.getBoundingClientRect();
          return text.left >= rect.left && text.right <= rect.right + 1 && text.top >= rect.top && text.bottom <= rect.bottom + 1 && rect.right <= innerWidth;
        });
        expect(fits).toBe(true);
      }

      await expect(page.locator('.site-nav [aria-current="page"]')).toHaveCount(1);
      const layout = await page.evaluate(() => ({ viewport: innerWidth, scroll: document.documentElement.scrollWidth, elementi: [...document.querySelectorAll('body *')].map(el => ({ tag: el.tagName, classe: el.className, x: el.getBoundingClientRect().x, destra: el.getBoundingClientRect().right, larghezza: el.getBoundingClientRect().width })).filter(el => el.destra > innerWidth + 1 || el.x < -1).slice(0, 30) }));
      console.log('Misure layout', url, width, JSON.stringify(layout));
      if (layout.scroll > layout.viewport) await page.screenshot({ path: info.outputPath(`diagnostico-overflow-${width}.png`), fullPage: true });
      expect(layout.scroll <= layout.viewport).toBe(true);
      await page.locator('img').evaluateAll(images => images.forEach(img => { img.loading = 'eager'; }));
      await expect.poll(() => page.locator('img').evaluateAll(images => images.every(img => img.complete && img.naturalWidth > 0))).toBe(true);
      const brokenAnchors = await page.locator('a[href^="#"]').evaluateAll(links => links.filter(link => !document.getElementById(link.hash.slice(1))).map(link => link.hash));
      expect(brokenAnchors).toEqual([]);
      await expect(page.locator('body')).not.toContainText(/prova di 7 giorni|400\s*€|60 camere/i);
      if (url === '/' || url === '/prezzi') {
        for (const [code, plan] of Object.entries(catalog.plans)) {
          const card = page.locator(`[data-plan="${code}"]`);
          await expect(card).toContainText(plan.price);
          await expect(card).toContainText(plan.scope);
          await expect(card.getByRole('link')).toHaveAttribute('href', `/contatti?piano=${code}#demo`);
        }
        await expect(page.locator('[data-price-group="appartamenti"]')).toContainText(catalog.plans.multi.scope);
        await expect(page.locator('[data-price-group="strutture"]')).toContainText(catalog.plans.strutture.price);
        await expect(page.locator('body')).toContainText(catalog.annual_note);
        await expect(page.locator('body')).toContainText(catalog.extras_note);
      }
      if (url === '/prezzi') {
        await expect(page.locator('#max-full')).toContainText(catalog.max_full.subtitle);
        await expect(page.locator('#max-full')).toContainText(catalog.max_full.description);
        await expect(page.locator('#max-full')).toContainText(catalog.max_full.commercial_note);
        for (const item of catalog.max_full.items) await expect(page.locator('#max-full')).toContainText(item);
        await expect(page.locator('#max-full').getByRole('link', { name: /Richiedi il servizio Max Full/ })).toHaveAttribute('href', '/contatti?servizio=max-full#demo');
      }
      if (url === '/moduli') {
        for (const id of ['arrivi', 'web-checkin', 'ospiti', 'tassa-soggiorno', 'questura', 'istat', 'stato-funzionalita']) await expect(page.locator(`#${id}`)).toBeAttached();
        await expect(page.locator('#stato-funzionalita')).toContainText('Servizi da configurare');
        await expect(page.locator('#stato-funzionalita')).toContainText(/credenziali e abilitazioni/i);
        await expect(page.locator('#istat')).toContainText('XML Emilia-Romagna');
        await expect(page.locator('#istat')).toContainText(/storico/i);
      }
      if (url === '/demo') {
        const tour = page.locator('[data-product-tour]');
        const tabs = tour.getByRole('tab');
        await expect(tabs).toHaveCount(5);
        const dimensions = [[1190, 588], [1190, 828], [1440, 1000], [1190, 828], [1000, 770]];
        for (let step = 0; step < 5; step++) {
          await tabs.nth(step).click();
          const panel = tour.getByRole('tabpanel');
          await expect(panel.locator('img')).toBeVisible();
          await expect.poll(() => panel.locator('img').evaluate(image => [image.naturalWidth, image.naturalHeight])).toEqual(dimensions[step]);
          await expect(panel.getByRole('link', { name: /Apri schermata a dimensione originale/ })).toHaveAttribute('href', /\.webp$/);
        }
        await tabs.first().click();
        await tour.locator('#tour-panel-arrivi img').evaluate(async image => {
          image.scrollIntoView({ block: 'center', inline: 'center', behavior: 'instant' });
          await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        });

        await expect(tour.locator('#tour-panel-arrivi img')).toBeInViewport({ ratio: 1 });
        await page.screenshot({ path: info.outputPath(`sito-demo-percorso-${width}.png`), fullPage: false });

        await expect(tour.getByRole('tabpanel')).toHaveCount(1);
        await tabs.first().focus();
        await page.keyboard.press('ArrowRight');
        await expect(tabs.nth(1)).toBeFocused();
        await expect(tabs.nth(1)).toHaveAttribute('aria-selected', 'true');
        await expect(tour.locator('#tour-panel-richiesta')).toBeVisible();
        await page.keyboard.press('End');
        await expect(tabs.last()).toBeFocused();
        await expect(tour.locator('[data-tour-next]')).toBeDisabled();
        await tour.locator('[data-tour-previous]').click();
        await expect(tour.locator('#tour-panel-schedina')).toBeVisible();
        await tabs.first().click();
        await expect(tour.locator('[data-tour-previous]')).toBeDisabled();
      }
      if (url === '/prezzi') {
        const question = page.getByText('Il programma gestisce il mio Comune?', { exact: true });
        await question.click();
        await expect(question.locator('..')).toHaveAttribute('open', '');
      }
      // La pagina intera si acquisisce dalla sua origine, dopo le interazioni di controllo.
      // Evita la composizione di elementi sticky/fixed nella posizione intermedia della viewport.
      await page.evaluate(() => window.scrollTo({ top: 0, left: 0, behavior: 'instant' }));
      await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0);
      await expect.poll(() => page.locator('.site-header').evaluate(el => Math.round(el.getBoundingClientRect().top))).toBe(0);
      await expect.poll(() => page.evaluate(async () => {
        const measure = () => [scrollY, document.documentElement.scrollWidth, document.documentElement.scrollHeight];
        const before = measure();
        await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        return JSON.stringify(before) === JSON.stringify(measure());
      })).toBe(true);
      await page.screenshot({ path: info.outputPath(`sito-${url === '/' ? 'home' : url.slice(1)}-${width}.png`), fullPage: true });
      if (url === '/' && width === 1440) {
        await page.screenshot({ path: info.outputPath('sito-home-hero-desktop.png'), fullPage: false });
        await page.locator('#novita').scrollIntoViewIfNeeded();
        await expect(page.locator('#novita h2')).toBeInViewport();
        await page.screenshot({ path: info.outputPath('sito-novita-desktop.png'), fullPage: false });
      }
      if (width < 951) {
        const toggle = page.locator('.menu-toggle');
        await expect(toggle).toHaveAttribute('aria-label', 'Apri menu');
        await toggle.focus();
        await page.keyboard.press('Enter');
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        await expect(toggle).toHaveAttribute('aria-label', 'Chiudi menu');
        await expect(page.getByRole('navigation', { name: 'Navigazione principale' })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await expect(toggle).toHaveAttribute('aria-label', 'Apri menu');
        await expect(toggle).toBeFocused();
        await toggle.click();
      }
      await expect(page.locator('.site-nav').getByRole('link', { name: 'Accedi' })).toHaveAttribute('href', '/login');
    }
    await page.goto(`${BASE_URL}/`);
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Vai al contenuto' })).toBeFocused();
    if (width < 951) await page.getByRole('button', { name: 'Apri menu', exact: true }).click();
    await page.locator('.site-nav').getByRole('link', { name: 'Funzionalità', exact: true }).click();
    await expect(page).toHaveURL(`${BASE_URL}/moduli`);
    await page.getByRole('navigation', { name: 'Sezioni delle funzionalità' }).getByRole('link', { name: 'Web Check-in', exact: true }).click();
    await expect(page).toHaveURL(/\/moduli#web-checkin$/);
    await expect(page.locator('#web-checkin h2')).toBeInViewport();
    await page.getByRole('navigation', { name: 'Sezioni delle funzionalità' }).getByRole('link', { name: 'Tassa di soggiorno' }).click();
    await expect(page).toHaveURL(/\/moduli#tassa-soggiorno$/);
    await expect(page.locator('#tassa-soggiorno h2')).toBeInViewport();
    await page.locator('.site-footer').getByRole('link', { name: 'Accedi al gestionale' }).click();
    await expect(page).toHaveURL(`${BASE_URL}/login`);
    await expect(page.locator('input[name=password]')).toBeVisible();
    expect((await page.goto(`${BASE_URL}/register`)).status()).toBe(200);
    await expect(page.locator('input[name=email]')).toBeVisible();
    expect(errors).toEqual([]);
    expect(failed).toEqual([]);
  });

  test(`contatti: obbligatori, errori, invio sintetico e modale a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    const auditContactLayout = async stage => {
      const layout = await page.evaluate(() => ({ viewport: innerWidth, scroll: document.documentElement.scrollWidth, elementi: [...document.querySelectorAll('body *')].map(el => ({ tag: el.tagName, classe: el.className, nome: el.getAttribute('name'), testo: el.textContent?.trim().slice(0, 90), padre: el.parentElement?.className, x: el.getBoundingClientRect().left, destra: el.getBoundingClientRect().right, larghezza: el.getBoundingClientRect().width })).filter(el => el.destra > innerWidth + 1).slice(0, 30) }));
      console.log('Misure modulo', stage, width, JSON.stringify(layout));
      if (layout.scroll > layout.viewport) await page.screenshot({ path: info.outputPath(`diagnostico-modulo-${stage}-${width}.png`), fullPage: true });
      return layout;
    };
    await page.goto(`${BASE_URL}/contatti?piano=solo&servizio=max-full#demo`, { waitUntil: 'load' });
    await expect(page.locator('[data-demo-form]')).toBeVisible();
    await expect(page.locator('[data-commercial-plan]')).toHaveValue('solo');
    await expect(page.locator('[data-commercial-max]')).toBeChecked();
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.plans.solo.price);
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.max_full.price);
    for (const [code, plan] of Object.entries(catalog.plans)) {
      await page.locator('[data-commercial-plan]').selectOption(code);
      await expect(page.locator('[data-commercial-summary]')).toContainText(plan.price);
      await expect(page.locator('[data-commercial-summary]')).toContainText(plan.scope);
    }
    await page.locator('[data-commercial-clear]').click();
    await expect(page.locator('[data-commercial-plan]')).toHaveValue('');
    await expect(page.locator('[data-commercial-max]')).not.toBeChecked();
    await expect(page.locator('[data-commercial-summary]')).toContainText('Nessun piano o servizio selezionato');
    await page.locator('[data-commercial-plan]').selectOption('duo');
    await page.locator('[data-commercial-max]').check();
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.plans.duo.price);

    await expect(page.locator('select[name=topic]')).toHaveValue('Richiesta demo');
    await expect(page.getByRole('combobox', { name: /Motivo di contatto/ })).toBeVisible();
    let posts = 0;
    page.on('request', request => { if (request.method() === 'POST' && request.url().endsWith('/contatti/richiesta')) posts++; });
    const submit = page.getByRole('button', { name: 'Invia la richiesta' });
    await submit.click();
    await expect(page.locator('.form-status')).toContainText('Controlla i campi obbligatori');
    expect(posts).toBe(0);
    await page.getByLabel('Struttura *', { exact: true }).fill(`STRUTTURA-SITO-BROWSER-${width}`);
    await page.getByLabel('Nome e cognome *', { exact: true }).fill('Referente sintetico');
    await page.getByLabel('Email *', { exact: true }).fill(`sito-${width}@example.invalid`);
    await page.getByText('Aggiungi recapiti o preferenze (facoltativo)', { exact: true }).click();
    await auditContactLayout('recapiti-aperti');
    const anytime = page.getByLabel('Nessuna preferenza di orario');
    await expect(anytime).toHaveCSS('width', '18px');
    await expect(anytime).toHaveCSS('height', '18px');
    await page.getByLabel('Telefono', { exact: true }).fill('+39 000 0000000');
    await page.getByLabel('Messaggio *', { exact: true }).fill('Verifica sintetica del sito pubblico. Nessun dato reale.');
    await page.getByLabel('Autorizzo il contatto commerciale').check();
    await page.getByLabel('Sito internet', { exact: true }).fill('indirizzo non valido');
    await submit.click();
    await expect(page.locator('.form-status')).toContainText('Inserisci il sito internet in un formato valido');
    expect(posts).toBe(0);
    await page.getByLabel('Sito internet', { exact: true }).fill('struttura.example.invalid');
    await page.getByLabel('Preferenza di contatto', { exact: true }).fill('30/10/2026 10:30');
    await page.keyboard.press('Escape');
    await page.locator('#form-title').click();
    await expect(page.locator('.flatpickr-calendar')).not.toHaveClass(/\bopen\b/);
    await page.getByLabel('Nessuna preferenza di orario').check();
    await expect(page.locator('input[name=contact_time]')).toBeDisabled();
    await page.locator('select[name=topic]').locator('..').locator('.select2-selection').click();
    await page.getByRole('option', { name: 'Informazioni generali', exact: true }).click();
    const changedTopicRequest = page.waitForRequest(request => request.method() === 'POST' && request.url().endsWith('/contatti/richiesta'));
    await page.route('**/contatti/richiesta', route => route.fulfill({ status: 422, contentType: 'application/json', body: JSON.stringify({ errors: { message: ['Errore sintetico da correggere.'] } }) }), { times: 1 });
    await submit.click();
    await expect(page.locator('.form-status')).toContainText('Errore sintetico da correggere.');
    await auditContactLayout('errore422');
    expect((await changedTopicRequest).postDataJSON().topic).toBe('Informazioni generali');
    await expect(page.locator('select[name=topic]')).toHaveValue('Informazioni generali');
    await page.locator('select[name=topic]').locator('..').locator('.select2-selection').click();
    await page.getByRole('option', { name: 'Richiesta demo', exact: true }).click();

    await expect(submit).toBeEnabled();
    await page.route('**/contatti/richiesta', route => route.fulfill({ status: 429, contentType: 'application/json', body: JSON.stringify({ message: 'Too Many Attempts.' }) }), { times: 1 });
    await submit.click();
    await expect(page.locator('.form-status')).toContainText('Attendi un minuto prima di riprovare');
    await expect(page.getByLabel('Email *', { exact: true })).toHaveValue(`sito-${width}@example.invalid`);
    await expect(page.locator('select[name=topic]')).toHaveValue('Richiesta demo');
    await expect(submit).toBeEnabled();
    const response = page.waitForResponse(r => r.url().endsWith('/contatti/richiesta') && r.request().method() === 'POST');
    await submit.click();
    const sent = await response;
    expect(sent.status()).toBe(200);
    expect((await sent.json()).ok).toBe(true);
    expect(sent.request().postDataJSON().topic).toBe('Richiesta demo');
    expect(sent.request().postDataJSON().plan).toBe('duo');
    expect(sent.request().postDataJSON().max_full).toBe(true);
    expect(sent.request().postDataJSON()).not.toHaveProperty('price');
    expect(sent.request().postDataJSON()).not.toHaveProperty('amount');

    expect(sent.request().postDataJSON().contact_anytime).toBe(true);
    expect(sent.request().postDataJSON().contact_time).toBe('Qualsiasi orario');
    const modal = page.getByRole('dialog');
    await expect(modal).toBeVisible();
    await expect(modal).toBeFocused();
    await page.keyboard.press('Shift+Tab');
    await expect(page.getByRole('button', { name: 'Torna alla pagina' })).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Chiudi messaggio di conferma' })).toBeFocused();
    await page.screenshot({ path: info.outputPath(`sito-conferma-${width}.png`), fullPage: false });
    await page.keyboard.press('Escape');
    await expect(modal).not.toBeVisible();
    await expect(submit).toBeFocused();
    await expect(page.locator('[data-demo-form]')).not.toHaveAttribute('aria-busy', 'true');
    const finalLayout = await auditContactLayout('reset-conferma-chiusa');
    expect(finalLayout.scroll <= finalLayout.viewport).toBe(true);
  });
}
