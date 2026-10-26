import { chromium } from 'playwright';
import { expect } from '@playwright/test';
import fs from 'node:fs';
import crypto from 'node:crypto';

// Esecuzione esplicita solo dopo C5. Questo file non crea fixture o account.
const O = new URL('./', import.meta.url);
const base = 'https://schedinedinotifica.tanggo.software';
const fixture = JSON.parse(fs.readFileSync(new URL('fixture-privata.json', O), 'utf8'));
const version = JSON.parse(fs.readFileSync(new URL('versione-attesa.json', O), 'utf8'));
expect(version.sha).toBe(fixture.sha);
expect(version.sito_versione).toMatch(/^[a-f0-9]{24}$/);
const catalogBytes = fs.readFileSync(new URL('catalogo-commerciale.json', O));
const catalog = JSON.parse(catalogBytes.toString('utf8'));
const expected = JSON.parse(fs.readFileSync(new URL('asset-attesi.json', O), 'utf8'));
const assets = ['styles.css', 'app.js', 'catalogo-commerciale.json', 'img/logo-tanggo.png', 'img/ospitalita-checkin.webp', ...['arrivi', 'richiesta', 'ospite', 'schedina', 'tassa'].map(n => `img/prodotto-${n}.webp`), ...['reception', 'assistenza', 'sviluppo'].map(n => `img/foto-${n}.webp`)];
const digest = bytes => crypto.createHash('sha256').update(bytes).digest('hex');
const plans = ['solo', 'duo', 'multi', 'oltre7', 'strutture', 'oltre2'];
const pages = [['/', 'Il software per'], ['/moduli', 'Ogni attività ha il suo spazio.'], ['/ampliamenti', 'Il percorso giusto.'], ['/demo', 'Dai primi dati'], ['/prezzi', 'Il piano giusto.'], ['/contatti', 'La tua prossima accoglienza'], ['/note-legali', 'Note legali'], ['/privacy', 'Informativa privacy'], ['/cookie', 'Cookie tecnici']];
expect(Object.keys(fixture).sort()).toEqual(['marker', 'sha']);
if (fs.existsSync(new URL('online-sito.json', O))) throw new Error('Esito online già presente: nessuna ripetizione automatica degli invii. Conservare il precedente e predisporre una nuova cartella prima di un eventuale nuovo tentativo autorizzato.');
expect(fixture.marker).toMatch(/^[A-Za-z0-9][A-Za-z0-9_-]{5,75}$/);
expect(fixture.sha).toMatch(/^[a-f0-9]{40}$/);
expect(Object.keys(catalog.plans)).toEqual(plans);
expect(Object.keys(expected).sort()).toEqual([...assets].sort());
for (const asset of assets) expect(expected[asset]).toMatch(/^[a-f0-9]{64}$/);
expect(digest(catalogBytes)).toBe(expected['catalogo-commerciale.json']);

const report = { inizio: new Date().toISOString(), esito: 'IN_CORSO', origine: base, sha_candidato_atteso: fixture.sha, marker: fixture.marker, prove: [], leadIds: [], richieste_bloccate: [], POST_contatti: 0, attivazioni_account: 0, acquisti: 0, invii_a_enti: 0, email_inviate_dallo_script: 0 };
const save = () => fs.writeFileSync(new URL('online-sito.json', O), JSON.stringify(report, null, 2));
const contexts = new Set();
let browser;
function rejectRequest(request, reason) {
  const url = new URL(request.url());
  report.richieste_bloccate.push({ metodo: request.method(), origine: url.origin, percorso: url.pathname, motivo: reason });
  save();
}
function sameOrigin(path) {
  const url = new URL(path, base);
  expect(url.protocol).toBe('https:');
  expect(url.origin).toBe(base);
  return url.href;
}
async function checkedGet(context, path) {
  // APIRequestContext non passa per context.route: URL fisso verificato e redirect disabilitati.
  const response = await context.request.get(sameOrigin(path), { maxRedirects: 0 });
  expect(response.status()).toBe(200);
  expect(new URL(response.url()).origin).toBe(base);
  return response;
}
async function uniformVersion(page, response) {
  expect(response.headers()['x-sito-versione']).toBe(version.sito_versione);
  await expect(page.locator('meta[name="sito-versione"]')).toHaveAttribute('content', version.sito_versione);
  await expect(page.locator('script[src^="/site-assets/app."]')).toHaveAttribute('src', `/site-assets/app.${expected['app.js'].slice(0, 16)}.js`);
  await expect(page.locator('link[href^="/site-assets/styles."]')).toHaveAttribute('href', `/site-assets/styles.${expected['styles.css'].slice(0, 16)}.css`);
  // Hash corretto non basta: il browser deve avere effettivamente interpretato il CSS.
  await expect.poll(() => page.evaluate(() => {
    const sheet = [...document.styleSheets].find(item => item.href && new URL(item.href).pathname.startsWith('/site-assets/styles.'));
    if (!sheet) return 0;
    try { return sheet.cssRules.length; } catch { return 0; }
  })).toBeGreaterThan(0);
}
async function realNavigation(page, width, code) {
  let response = await page.goto(sameOrigin('/'), { waitUntil: 'load' }); await uniformVersion(page, response);
  for (const path of ['/moduli', '/ampliamenti', '/prezzi']) {
    await page.waitForLoadState('load');
    if (width < 1281) {
      const toggle = page.locator('.menu-toggle');
      await toggle.click(); await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    }
    await page.locator(`.site-nav a[href="${path}"]`).click();
    await expect(page).toHaveURL(sameOrigin(path));
    response = await page.reload({ waitUntil: 'load' }); await uniformVersion(page, response); await layout(page, `reload-${path.slice(1)}-${width}`);
  }
  await page.locator('.actions a[href="#max-full"]').click();
  await expect(page).toHaveURL(sameOrigin('/prezzi#max-full'));
  await expect(page.locator('#max-full h2')).toBeInViewport();
  await page.locator('#max-full a[href="/contatti?servizio=max-full#demo"]').click();
  await expect(page).toHaveURL(sameOrigin('/contatti?servizio=max-full#demo'));
  await expect(page.locator('[data-commercial-max]')).toBeChecked();
  await page.goBack({ waitUntil: 'load' }); await expect(page).toHaveURL(sameOrigin('/prezzi#max-full'));
  await page.goForward({ waitUntil: 'load' }); await expect(page).toHaveURL(sameOrigin('/contatti?servizio=max-full#demo'));
  await expect(page.locator('meta[name="sito-versione"]')).toHaveAttribute('content', version.sito_versione);
  response = await page.goto(sameOrigin('/ampliamenti'), { waitUntil: 'load' }); await uniformVersion(page, response);
  await page.locator('#web-checkin a[href="/contatti?interesse=web-checkin#demo"]').click();
  await expect(page).toHaveURL(sameOrigin('/contatti?interesse=web-checkin#demo'));
  await expect(page.locator('[data-commercial-interest]')).toHaveValue('web-checkin');
  await page.locator('[data-commercial-plan]').selectOption(code); await page.locator('[data-commercial-max]').check();
  await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.interests['web-checkin']);
}
async function layout(page, stage) {
  const measured = await page.evaluate(() => ({ viewport: innerWidth, scroll: document.documentElement.scrollWidth, elementi: [...document.querySelectorAll('body *')].map(el => ({ tag: el.tagName, classe: String(el.className), destra: el.getBoundingClientRect().right, x: el.getBoundingClientRect().left })).filter(el => el.destra > innerWidth + 1).slice(0, 15) }));
  if (measured.scroll > measured.viewport) await page.screenshot({ path: new URL(`NONPASS-layout-${stage}.png`, O).pathname, fullPage: true });
  expect(measured.scroll <= measured.viewport, `Overflow nel passaggio ${stage}: ${JSON.stringify(measured)}`).toBe(true);
  return measured;
}
async function stableTop(page) {
  await page.evaluate(() => window.scrollTo({ top: 0, left: 0, behavior: 'instant' }));
  await expect.poll(() => page.evaluate(() => scrollY)).toBe(0);
  await expect.poll(() => page.locator('.site-header').evaluate(el => Math.round(el.getBoundingClientRect().top))).toBe(0);
  await expect.poll(() => page.evaluate(async () => {
    const measure = () => [scrollY, document.documentElement.scrollWidth, document.documentElement.scrollHeight];
    const first = measure();
    await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
    return JSON.stringify(first) === JSON.stringify(measure());
  })).toBe(true);
}
async function menu(page, width) {
  await page.waitForLoadState('load');
  let button;
  if (width < 1281) {
    button = page.locator('.menu-toggle');
    await expect(button).toHaveAccessibleName('Apri menu');
    await button.focus();
    await page.keyboard.press('Enter');
    await expect(button).toHaveAttribute('aria-expanded', 'true');
    await expect(button).toHaveAccessibleName('Chiudi menu');
  }
  await expect(page.locator('.site-nav').getByRole('link', { name: 'Accedi', exact: true })).toHaveAttribute('href', '/login');
  await expect(page.locator('.site-nav .nav-cta')).toHaveAttribute('href', '/contatti#demo');
  if (width < 1281) {
    await expect(page.locator('.site-nav .nav-cta')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(button).toHaveAttribute('aria-expanded', 'false');
    await expect(button).toHaveAccessibleName('Apri menu');
    await expect(button).toBeFocused();
  }
}
async function tour(page) {
  const root = page.locator('[data-product-tour]');
  const tabs = root.getByRole('tab');
  await expect(tabs).toHaveCount(5);
  for (let i = 0; i < 5; i++) {
    await tabs.nth(i).click();
    const image = root.getByRole('tabpanel').locator('img');
    await expect(image).toBeVisible();
    await expect.poll(() => image.evaluate(el => el.complete && el.naturalWidth > 0)).toBe(true);
  }
  await tabs.first().focus();
  await page.keyboard.press('Home');
  await expect(tabs.first()).toHaveAttribute('aria-selected', 'true');
  await page.keyboard.press('ArrowRight');
  await expect(tabs.nth(1)).toBeFocused();
  await expect(root.locator('#tour-panel-richiesta')).toBeVisible();
  await page.keyboard.press('End');
  await expect(tabs.last()).toBeFocused();
  await expect(root.locator('[data-tour-next]')).toBeDisabled();
  await root.locator('[data-tour-previous]').click();
  await expect(root.locator('#tour-panel-schedina')).toBeVisible();
  await tabs.first().click();
  await expect(root.locator('[data-tour-previous]')).toBeDisabled();
}

try {
  save();
  browser = await chromium.launch({ headless: true });
  for (const width of [1440, 768, 390]) {
    const code = width === 1440 ? 'solo' : width === 768 ? 'strutture' : 'oltre2';
    const context = await browser.newContext({ viewport: { width, height: 1000 }, serviceWorkers: 'block', ignoreHTTPSErrors: false });
    contexts.add(context);
    context.setDefaultTimeout(20000);
    context.setDefaultNavigationTimeout(30000);
    let postEnabled = false;
    let postCount = 0;
    await context.route('**/*', async route => {
      const request = route.request();
      const url = new URL(request.url());
      if (url.protocol !== 'https:' || url.origin !== base) {
        rejectRequest(request, 'Origine o protocollo estranei');
        return route.abort();
      }
      if (request.method() === 'GET' || request.method() === 'HEAD') return route.continue();
      if (request.method() !== 'POST' || url.pathname !== '/contatti/richiesta' || url.search !== '' || !postEnabled || postCount !== 0) {
        rejectRequest(request, 'Metodo o operazione non autorizzati');
        return route.abort();
      }
      let data;
      try { data = request.postDataJSON(); } catch { rejectRequest(request, 'Payload non JSON'); return route.abort(); }
      const valid = typeof data._token === 'string' && data._token.length >= 40 && data.company === `${fixture.marker}-${width}` && data.name === 'Referente sintetico' && data.email === `${fixture.marker.toLowerCase()}-${width}@example.invalid` && data.message === `Verifica sintetica ${fixture.marker}` && data.plan === code && data.max_full === true && data.interest === 'web-checkin' && data.consent === true && data.website === '' && data.topic === 'Richiesta demo' && !Object.hasOwn(data, 'price') && !Object.hasOwn(data, 'amount');
      if (!valid) { rejectRequest(request, 'Payload diverso dalla fixture autorizzata'); return route.abort(); }
      // Il permesso è consumato prima della rete: nessun retry o secondo invio possibile.
      postCount++;
      postEnabled = false;
      report.POST_contatti++;
      save();
      return route.continue();
    });
    if (typeof context.routeWebSocket === 'function') {
      await context.routeWebSocket('**/*', socket => {
        report.richieste_bloccate.push({ metodo: 'WEBSOCKET', motivo: 'Canale non autorizzato' });
        save();
        socket.close();
      });
    }
    const page = await context.newPage();
    const jsErrors = [];
    const httpErrors = [];
    page.on('pageerror', error => jsErrors.push(error.message));
    page.on('response', response => { if (response.status() >= 400) httpErrors.push({ percorso: new URL(response.url()).pathname, stato: response.status() }); });
    const checks = { larghezza: width, piano: code, prezzo_atteso: catalog.plans[code].price, max_full: true, interesse: 'web-checkin', pagine: [], asset_SHA256: {}, screenshot: [] };
    report.prove.push(checks);
    for (const asset of assets) {
      const assetPath = ['styles.css', 'app.js'].includes(asset) ? `/site-assets/${asset.split('.')[0]}.${expected[asset].slice(0, 16)}.${asset.split('.')[1]}` : `/site-assets/${asset}`;
      const response = await checkedGet(context, assetPath);
      if (['styles.css', 'app.js'].includes(asset)) {
        const mime = response.headers()['content-type']?.split(';')[0].trim().toLowerCase();
        expect(mime).toBe(asset === 'styles.css' ? 'text/css' : 'application/javascript');
        expect(response.headers()['cache-control']).toContain('immutable');
        expect(response.headers()['set-cookie']).toBeUndefined();
      }
      const sha = digest(await response.body());
      expect(sha, `Digest risorsa ${asset}`).toBe(expected[asset]);
      checks.asset_SHA256[asset] = sha;
    }
    const remoteCatalog = await (await checkedGet(context, '/site-assets/catalogo-commerciale.json')).json();
    expect(remoteCatalog).toEqual(catalog);
    const anchorInventory = {};
    for (const [path, heading] of pages) {
      const response = await page.goto(sameOrigin(path), { waitUntil: 'load' });
      expect(response.status()).toBe(200);
      await uniformVersion(page, response);
      expect(new URL(page.url()).origin).toBe(base);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('h1')).toContainText(heading);
      await expect(page.locator('body')).toHaveCSS('font-size', width <= 600 ? '17px' : '18px');
      await expect(page.locator('.brand-logo')).toHaveCount(2);
      await expect(page.locator('.brand strong')).toHaveText(['Tanggo Software', 'Tanggo Software']);
      await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', sameOrigin(path));
      await expect(page.locator((['/note-legali', '/privacy', '/cookie'].includes(path) ? '.footer-bottom' : '.site-nav') + ' [aria-current="page"]')).toHaveCount(1);
      await expect(page.locator('body')).not.toContainText(/prova di 7 giorni|400\s*€|60 camere/i);
      const broken = await page.locator('a[href^="#"]').evaluateAll(links => links.filter(link => !document.getElementById(link.hash.slice(1))).map(link => link.hash));
      expect(broken).toEqual([]);
      await page.locator('img').evaluateAll(images => images.forEach(image => { image.loading = 'eager'; }));
      await expect.poll(() => page.locator('img').evaluateAll(images => images.every(image => image.complete && image.naturalWidth > 0))).toBe(true);
      for (const image of await page.locator('img:visible').all()) {
        await image.scrollIntoViewIfNeeded();
        await image.evaluate(async img => { await img.decode(); await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
      }
      await page.evaluate(async () => { scrollTo({ top: 0, behavior: 'instant' }); await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))); });
      await layout(page, `${path === '/' ? 'home' : path.slice(1)}-${width}`);
      await menu(page, width);
      if (path === '/') {
        await expect(page.locator('.hero h1')).toContainText('Schedine di Notifica.');
        await expect(page.locator('.hero-photo')).toHaveCSS('background-image', /radial-gradient/);
        await expect(page.locator('.hero').getByRole('link', { name: 'Richiedi una demo', exact: true })).toHaveAttribute('href', '/contatti#demo');
        await expect(page.locator('.hero').getByRole('link', { name: 'Confronta i piani', exact: true })).toHaveAttribute('href', '/prezzi');
      }
      if (path === '/' || path === '/prezzi') {
        for (const [planCode, plan] of Object.entries(catalog.plans)) {
          const card = page.locator(`[data-plan="${planCode}"]`);
          await expect(card).toContainText(plan.price);
          await expect(card).toContainText(plan.scope);
          await expect(card.getByRole('link')).toHaveAttribute('href', `/contatti?piano=${planCode}#demo`);
        }
        await expect(page.locator('[data-price-group="appartamenti"]')).toContainText(catalog.plans.multi.scope);
        await expect(page.locator('[data-price-group="strutture"]')).toContainText(catalog.plans.strutture.price);
        await expect(page.locator('body')).toContainText(catalog.annual_note);
        await expect(page.locator('body')).toContainText(catalog.extras_note);
      }
      if (path === '/demo') await tour(page);
      if (path === '/moduli') {
        await expect(page.locator('#istat')).toContainText('XML Emilia-Romagna');
        await expect(page.locator('#istat')).toContainText(/storico/i);
      }
      if (path === '/prezzi') {
        const max = page.locator('#max-full');
        for (const text of [catalog.max_full.subtitle, catalog.max_full.description, catalog.max_full.price, catalog.max_full.commercial_note, ...catalog.max_full.items]) await expect(max).toContainText(text);
        await expect(max.getByRole('link', { name: /Richiedi il servizio Max Full/ })).toHaveAttribute('href', '/contatti?servizio=max-full#demo');
        const question = page.getByText('Il programma gestisce il mio Comune?', { exact: true });
        await question.click();
        await expect(question.locator('..')).toHaveAttribute('open', '');
      }
      // Completo non equivale a dipinto: visita e decodifica ogni immagine visibile.
      for (const image of await page.locator('img:visible').all()) {
        await image.scrollIntoViewIfNeeded();
        await image.evaluate(async el => {
          await el.decode();
          await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        });
        await expect.poll(() => image.evaluate(el => el.complete && el.naturalWidth > 0)).toBe(true);
      }
      await stableTop(page);
      const name = `online-sito-${path === '/' ? 'home' : path.slice(1)}-${width}.png`;
      await page.screenshot({ path: new URL(name, O).pathname, fullPage: true });
      checks.screenshot.push(name);
      if (path === '/prezzi') {
        await page.locator('#max-full h2').scrollIntoViewIfNeeded();
        await expect(page.locator('#max-full h2')).toBeInViewport();
        const detail = `online-sito-max-full-${width}.png`;
        await page.screenshot({ path: new URL(detail, O).pathname, fullPage: false });
        checks.screenshot.push(detail);
      }
      anchorInventory[path] = await page.evaluate(() => ({ ids: [...document.querySelectorAll('[id]')].map(el => el.id), links: [...document.querySelectorAll('a[href]')].map(el => el.getAttribute('href')) }));
      checks.pagine.push({ percorso: path, canonical: sameOrigin(path), stato: 200 });
      expect(report.richieste_bloccate).toEqual([]);
    }
    let crossAnchors = 0;
    for (const [sourcePath, inventory] of Object.entries(anchorInventory)) {
      for (const href of inventory.links) {
        const target = new URL(href, sameOrigin(sourcePath));
        if (target.origin === base && target.hash && anchorInventory[target.pathname]) {
          expect(anchorInventory[target.pathname].ids, `Ancora ${sourcePath} → ${href}`).toContain(decodeURIComponent(target.hash.slice(1)));
          crossAnchors++;
        }
      }
    }
    checks.ancore_anche_incrociate = crossAnchors;
    await realNavigation(page, width, code);
    checks.click_reload_back_forward = 'PASS';
    // Accesso preservato verificato nei link: nessuna autenticazione o navigazione nel gestionale.
    await page.goto(sameOrigin(`/contatti?piano=${code}&servizio=max-full&interesse=web-checkin#demo`), { waitUntil: 'load' });
    await expect(page.locator('[data-commercial-plan]')).toHaveValue(code);
    await expect(page.locator('[data-commercial-max]')).toBeChecked();
    await expect(page.locator('[data-commercial-interest]')).toHaveValue('web-checkin');
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.interests['web-checkin']);
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.plans[code].price);
    await expect(page.locator('[data-commercial-summary]')).toContainText(catalog.max_full.price);
    await expect(page.locator('select[name="topic"]')).toHaveValue('Richiesta demo');
    await expect(page.getByRole('combobox', { name: /Motivo di contatto/ })).toBeVisible();
    await page.getByText('Aggiungi recapiti o preferenze (facoltativo)', { exact: true }).click();
    await expect(page.getByLabel('Nessuna preferenza di orario')).toHaveCSS('width', '18px');
    await expect(page.getByLabel('Nessuna preferenza di orario')).toHaveCSS('height', '18px');
    await layout(page, `recapiti-aperti-${width}`);
    await page.getByLabel('Struttura *', { exact: true }).fill(`${fixture.marker}-${width}`);
    await page.getByLabel('Nome e cognome *', { exact: true }).fill('Referente sintetico');
    await page.getByLabel('Email *', { exact: true }).fill(`${fixture.marker.toLowerCase()}-${width}@example.invalid`);
    await page.getByLabel('Messaggio *', { exact: true }).fill(`Verifica sintetica ${fixture.marker}`);
    await page.getByLabel('Autorizzo il contatto commerciale').check();
    const submit = page.getByRole('button', { name: 'Invia la richiesta', exact: true });
    postEnabled = true;
    const responsePromise = page.waitForResponse(response => response.url() === `${base}/contatti/richiesta` && response.request().method() === 'POST');
    await submit.click();
    const response = await responsePromise;
    expect(postCount).toBe(1);
    expect(response.status()).toBe(200);
    const data = await response.json();
    // Registrazione immediata su disco per consentire cleanup anche in caso di errore successivo.
    if (Number.isSafeInteger(data.lead_id) && data.lead_id > 0) {
      report.leadIds.push(data.lead_id);
      checks.lead_id = data.lead_id;
      save();
    }
    expect(data.ok).toBe(true);
    expect(Number.isSafeInteger(data.lead_id) && data.lead_id > 0).toBe(true);
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await expect(dialog).toBeFocused();
    await page.keyboard.press('Shift+Tab');
    await expect(page.getByRole('button', { name: 'Torna alla pagina', exact: true })).toBeFocused();
    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Chiudi messaggio di conferma', exact: true })).toBeFocused();
    const confirmation = `online-sito-conferma-${width}.png`;
    await page.screenshot({ path: new URL(confirmation, O).pathname, fullPage: false });
    checks.screenshot.push(confirmation);
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();
    await expect(submit).toBeFocused();
    await expect(page.locator('[data-demo-form]')).not.toHaveAttribute('aria-busy', 'true');
    await expect(page.locator('[data-commercial-interest]')).toHaveValue('');
    await expect(page.locator('[data-commercial-plan]')).toHaveValue('');
    await expect(page.locator('[data-commercial-max]')).not.toBeChecked();
    await layout(page, `reset-conferma-chiusa-${width}`);
    expect(jsErrors).toEqual([]);
    expect(httpErrors).toEqual([]);
    expect(report.richieste_bloccate).toEqual([]);
    checks.errori_JS = jsErrors;
    checks.errori_HTTP = httpErrors;
    checks.POST_contatti = postCount;
    checks.esito = 'PASS';
    save();
    await context.close();
    contexts.delete(context);
  }
  expect(report.POST_contatti).toBe(3);
  expect(report.leadIds).toHaveLength(3);
  expect(new Set(report.leadIds).size).toBe(3);
  expect(report.richieste_bloccate).toEqual([]);
  expect(report.prove.every(proof => proof.pagine.length === 9)).toBe(true);
  report.sito_versione = version.sito_versione;
  report.esito = 'PASS_SITO_ONLINE';
} catch (error) {
  report.esito = 'NONPASS_SITO_ONLINE';
  report.errore = String(error.stack || error.message || error);
  process.exitCode = 1;
} finally {
  report.fine = new Date().toISOString();
  save();
  for (const context of contexts) await context.close().catch(() => {});
  if (browser) await browser.close();
  console.log(JSON.stringify(report));
}
