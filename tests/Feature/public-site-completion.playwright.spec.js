import { test, expect, BASE_URL } from '../Support/playwright.js';

async function menu(page, width, path) {
  if (width < 1281) {
    const button = page.locator('.menu-toggle');
    await expect(button).toHaveAccessibleName('Apri menu');
    await button.click(); await expect(button).toHaveAccessibleName('Chiudi menu');
  }
  await page.locator(`.site-nav a[href="${path}"]`).click();
  await expect(page).toHaveURL(`${BASE_URL}${path}`);
}
async function loaded(page) {
  await page.locator('img').evaluateAll(images => images.forEach(i => { i.loading = 'eager'; }));
  await expect.poll(() => page.locator('img').evaluateAll(images => images.every(i => i.complete && i.naturalWidth > 0))).toBe(true);
  await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
}
for (const width of [1440, 768, 390]) {
  test(`sito completo: percorso reale, storia browser, versioni e CRM a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    const pages = ['/', '/moduli', '/ampliamenti', '/prezzi', '/demo', '/contatti'];
    const targets = new Map(); const links = []; const versions = new Set();
    for (const path of pages) {
      const response = await page.goto(`${BASE_URL}${path}`, { waitUntil: 'load' });
      expect(response.status()).toBe(200); await loaded(page);
      const version = await page.locator('meta[name="sito-versione"]').getAttribute('content');
      expect(response.headers()['x-sito-versione']).toBe(version); versions.add(version);
      targets.set(path, await page.locator('[id]').evaluateAll(nodes => nodes.map(n => n.id)));
      links.push(...await page.locator('a[href]').evaluateAll(nodes => nodes.map(n => n.href)));
      const assets = await page.locator('script[src],link[rel=stylesheet]').evaluateAll(nodes => nodes.map(n => n.src || n.href));
      expect(assets.filter(x => /\/site-assets\/app\.[a-f0-9]{16}\.js$/.test(x))).toHaveLength(1);
      expect(assets.filter(x => /\/site-assets\/styles\.[a-f0-9]{16}\.css$/.test(x))).toHaveLength(1);
    }
    expect(versions.size).toBe(1);
    for (const link of links) { const u = new URL(link); if (u.origin === BASE_URL && targets.has(u.pathname) && u.hash) expect(targets.get(u.pathname), link).toContain(decodeURIComponent(u.hash.slice(1))); }
    await page.goto(`${BASE_URL}/`, { waitUntil: 'load' });
    await menu(page, width, '/moduli'); await expect(page.locator('h1')).toContainText('Ogni attività');
    await menu(page, width, '/ampliamenti'); await expect(page.locator('h1')).toContainText('Il percorso giusto.'); await loaded(page);
    await page.reload({ waitUntil: 'load' }); await expect(page.locator('h1')).toContainText('Il percorso giusto.');
    await page.goBack({ waitUntil: 'load' }); await expect(page).toHaveURL(`${BASE_URL}/moduli`);
    await page.goForward({ waitUntil: 'load' }); await expect(page).toHaveURL(`${BASE_URL}/ampliamenti`);
    await expect(page.locator('h1')).toContainText('Il percorso giusto.');
    await page.screenshot({ path: info.outputPath(`ampliamenti-${width}.png`), fullPage: true });
    await menu(page, width, '/prezzi'); await menu(page, width, '/prezzi#max-full');
    await expect(page.locator('#max-full h2')).toBeInViewport(); await loaded(page);
    const headerBottom = await page.locator('.site-header').evaluate(e => e.getBoundingClientRect().bottom);
    await expect.poll(() => page.locator('#max-full h2').evaluate(e => e.getBoundingClientRect().top)).toBeGreaterThanOrEqual(headerBottom);
    await page.locator('#max-full a[href="/contatti?servizio=max-full#demo"]').click();
    await expect(page.locator('[data-commercial-max]')).toBeChecked();
    await page.goBack({ waitUntil: 'load' }); await expect(page).toHaveURL(`${BASE_URL}/prezzi#max-full`);
    await page.goForward({ waitUntil: 'load' }); await expect(page.locator('[data-commercial-max]')).toBeChecked();
    await page.goto(`${BASE_URL}/ampliamenti`, { waitUntil: 'load' });
    await page.locator('a[href="/contatti?interesse=web-checkin#demo"]').first().click();
    await expect(page.locator('[data-commercial-interest]')).toHaveValue('web-checkin');
    await page.locator('[data-commercial-plan]').selectOption('strutture');
    await page.locator('[data-commercial-max]').check();
    await expect(page.locator('[data-commercial-summary]')).toContainText('Web Check-in');
    await page.getByLabel('Struttura *', { exact: true }).fill(`COMPLETAMENTO-ISOLATO-${width}`);
    await page.getByLabel('Nome e cognome *', { exact: true }).fill('Referente Sintetico');
    await page.getByLabel('Email *', { exact: true }).fill(`completamento-${width}@example.invalid`);
    await page.getByLabel('Messaggio *', { exact: true }).fill(`Richiesta browser sintetica ${width}.`);
    await page.getByLabel('Autorizzo il contatto commerciale').check();
    const responsePromise = page.waitForResponse(r => r.url().endsWith('/contatti/richiesta') && r.request().method() === 'POST');
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    const stored = await responsePromise; expect(stored.status()).toBe(200); const receipt = await stored.json(); expect(receipt.ok).toBe(true); expect(receipt.lead_id).toBeGreaterThan(0);
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.screenshot({ path: info.outputPath(`richiesta-interesse-${width}.png`) });
    await page.keyboard.press('Escape'); await expect(page.getByRole('dialog')).not.toBeVisible();
    await page.goto(`${BASE_URL}/login`); await page.locator('input[name=login]').fill('sito-crm-ui');
    await page.locator('input[name=password]').fill('Password-sito-fixture-123!'); await page.getByRole('button', { name: /Entra/i }).click();
    await expect(page).not.toHaveURL(/\/login$/);
    await page.goto(`${BASE_URL}/superadmin/crm/${receipt.lead_id}`);
    await expect(page.locator('body')).toContainText(`COMPLETAMENTO-ISOLATO-${width}`);
    await expect(page.locator('body')).toContainText('Interesse richiesto: Web Check-in [web-checkin]');
    await expect(page.locator('body')).toContainText('320 € + IVA / anno complessivi');
    await expect(page.locator('body')).toContainText('Servizio aggiuntivo richiesto: Max Full');
    expect(errors).toEqual([]);
  });
}

test('form: errore nativo conserva dati, piano, servizio, interesse e motivo anche con hash demo', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 1000 });
  const response = await page.request.post(`${BASE_URL}/contatti/richiesta`, {
    form: { company: 'Hotel $1 <Nativo>', name: 'Referente Sintetico', email: 'non-email', topic: 'Informazioni generali', message: 'Richiesta nativa sintetica.', plan: 'strutture', max_full: '1', interest: 'web-checkin', consent: '1', website: '' },
    headers: { Accept: 'text/html', Referer: `${BASE_URL}/contatti` }, maxRedirects: 0,
  });
  expect(response.status()).toBe(302);
  await page.goto(`${BASE_URL}/contatti#demo`, { waitUntil: 'load' });
  await expect(page.getByRole('alert')).toContainText('La richiesta non è stata registrata.');
  await expect(page.getByLabel('Struttura *', { exact: true })).toHaveValue('Hotel $1 <Nativo>');
  await expect(page.getByLabel('Email *', { exact: true })).toHaveValue('non-email');
  await expect(page.locator('[data-commercial-plan]')).toHaveValue('strutture');
  await expect(page.locator('[data-commercial-max]')).toBeChecked();
  await expect(page.locator('[data-commercial-interest]')).toHaveValue('web-checkin');
  await expect(page.locator('select[name=topic]')).toHaveValue('Informazioni generali');
  await expect(page.getByRole('dialog')).not.toBeVisible();
  await page.getByLabel('Email *', { exact: true }).fill('nativo-corretto@example.invalid');
  const stored = page.waitForResponse(r => r.url().endsWith('/contatti/richiesta') && r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
  const receipt = await stored; expect(receipt.status()).toBe(200); expect((await receipt.json()).ok).toBe(true);
  await expect(page.getByRole('dialog')).toBeVisible();
  for (const name of ['company', 'name', 'email', 'message']) await expect(page.locator(`[name=${name}]`)).toHaveValue('');
  await expect(page.locator('[data-commercial-plan]')).toHaveValue('');
  await expect(page.locator('[data-commercial-interest]')).toHaveValue('');
  await expect(page.locator('[data-commercial-max]')).not.toBeChecked();
  await expect(page.locator('[name=consent]')).not.toBeChecked();
  await expect(page.locator('[data-commercial-summary]')).toContainText('Nessun piano o servizio selezionato');
});
