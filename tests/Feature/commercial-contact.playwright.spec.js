import { test, expect, BASE_URL } from '../Support/playwright.js';

const cases = [
  { width: 1440, topic: 'Richiesta demo', choice: 'Richiedi la tua demo →', mode: 'visita', confirmation: 'concordare modalità e disponibilità' },
  { width: 768, topic: 'Presentazione di gruppo', choice: 'Iscriviti alla presentazione →', confirmation: 'data e i dettagli dell’incontro' },
  { width: 390, topic: 'Informazioni e personalizzazioni', choice: 'Raccontaci cosa ti serve →', confirmation: 'soluzioni Tanggo Software' },
];
for (const sample of cases) {
  test(`contatto commerciale reale nel CRM isolato a ${sample.width}`, async ({ page }, info) => {
    const errors = []; page.on('pageerror', e => errors.push(e.message));
    await page.setViewportSize({ width: sample.width, height: 1000 });
    await page.goto(`${BASE_URL}/contatti?piano=duo&interesse=web-checkin&servizio=max-full#demo`, { waitUntil: 'load' });
    await expect(page.locator('h1')).toContainText('tua struttura');
    await expect(page.locator('[name=topic]')).toHaveValue('Richiesta demo');
    await expect(page.locator('[data-commercial-plan]')).toHaveValue('duo');
    await expect(page.locator('[data-commercial-interest]')).toHaveValue('web-checkin');
    await expect(page.locator('[data-commercial-max]')).toBeChecked();
    await page.locator('img').evaluateAll(nodes => nodes.forEach(n => { n.loading = 'eager'; }));
    await expect.poll(() => page.locator('img').evaluateAll(nodes => nodes.every(n => n.complete && n.naturalWidth > 0))).toBe(true);
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.screenshot({ path: info.outputPath(`contatti-${sample.width}.png`), fullPage: true });
    const posts = []; page.on('request', r => { if (r.method() === 'POST' && r.url().endsWith('/contatti/richiesta')) posts.push(r); });
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    await expect(page.locator('form .form-status')).toContainText('Controlla i campi obbligatori');
    expect(posts).toHaveLength(0);
    for (const item of cases) {
      await page.getByRole('link', { name: item.choice, exact: true }).click();
      await expect(page.locator('[name=topic]')).toHaveValue(item.topic);
      await expect(page.getByRole('combobox', { name: 'Motivo di contatto *', exact: true })).toBeFocused();
      if (item.topic === 'Richiesta demo') await expect(page.locator('[data-demo-preference]')).toBeVisible();
      else await expect(page.locator('[data-demo-preference]')).toBeHidden();
    }
    await page.getByRole('link', { name: sample.choice, exact: true }).click();
    await expect(page.locator('[name=topic]')).toHaveValue(sample.topic);
    await expect(page.locator('[data-commercial-plan]')).toHaveValue('duo');
    await page.getByLabel('Nome e cognome *', { exact: true }).fill('Referente Sintetico');
    await page.getByLabel('Email *', { exact: true }).fill(`contatti-${sample.width}@example.invalid`);
    await page.getByLabel('Telefono *', { exact: true }).fill('+39 0541 123456');
    await page.getByLabel('Nome della struttura *', { exact: true }).fill(`CONTATTI-ISOLATO-${sample.width}`);
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    await expect(page.getByRole('combobox', { name: 'Tipo di struttura *', exact: true })).toBeFocused();
    await expect(page.getByRole('combobox', { name: 'Tipo di struttura *', exact: true })).toHaveAttribute('aria-invalid', 'true');
    expect(posts).toHaveLength(0);
    await page.getByRole('combobox', { name: 'Tipo di struttura *', exact: true }).click();
    await page.getByRole('option', { name: 'Hotel', exact: true }).click();
    await page.getByLabel('Località *', { exact: true }).fill('Bellaria Igea Marina');
    if (sample.mode) {
      await page.getByRole('combobox', { name: 'Come preferisci la demo?', exact: true }).click();
      await page.getByRole('option', { name: 'Visita nella struttura', exact: true }).click();
    }
    if (sample.topic === 'Presentazione di gruppo') await expect(page.locator('[data-group-hint]')).toBeVisible();
    await page.getByLabel('Autorizzo il contatto in relazione').check();
    const csrf = await page.locator('[name=_token]').inputValue();
    const responsePromise = page.waitForResponse(r => r.url().endsWith('/contatti/richiesta') && r.request().method() === 'POST');
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    const response = await responsePromise; expect(response.status()).toBe(200);
    const receipt = await response.json(); expect(receipt.ok).toBe(true); expect(receipt.lead_id).toBeGreaterThan(0);
    expect(posts).toHaveLength(1);
    await expect(page.getByRole('dialog')).toBeVisible();
    await expect(page.getByRole('dialog')).toContainText(sample.confirmation);
    await expect(page.getByRole('dialog')).toBeFocused();
    await page.screenshot({ path: info.outputPath(`conferma-${sample.width}.png`) });
    await page.keyboard.press('Tab'); await page.keyboard.press('Shift+Tab');
    await expect(page.getByRole('button', { name: 'Torna alla pagina', exact: true })).toBeFocused();
    await page.keyboard.press('Escape'); await expect(page.getByRole('dialog')).toBeHidden();
    await expect(page.getByRole('button', { name: 'Invia la richiesta', exact: true })).toBeFocused();
    await expect(page.locator('[name=_token]')).toHaveValue(csrf);
    await expect(page.locator('[name=contact_form]')).toHaveValue('commercial-v2');
    await page.goto(`${BASE_URL}/login`);
    await page.locator('input[name=login]').fill('contatti-crm-ui');
    await page.locator('input[name=password]').fill('Password-contatti-fixture-123!');
    await page.getByRole('button', { name: /Entra/i }).click();
    await expect(page).not.toHaveURL(/\/login$/);
    await page.goto(`${BASE_URL}/superadmin/crm/${receipt.lead_id}`);
    for (const text of [`CONTATTI-ISOLATO-${sample.width}`, sample.topic, 'Tipo di struttura: Hotel', '100 € + IVA / anno', 'Interesse richiesto: Web Check-in [web-checkin]', 'Servizio aggiuntivo richiesto: Max Full']) await expect(page.locator('body')).toContainText(text);
    if (sample.mode) await expect(page.locator('body')).toContainText('Preferenza demo: Visita in struttura');
    else await expect(page.locator('body')).not.toContainText('Preferenza demo:');
    await page.screenshot({ path: info.outputPath(`crm-${sample.width}.png`), fullPage: true });
    expect(errors).toEqual([]);
  });
}

test('contatti: collegamenti storici, query, tastiera e tour demo conservati', async ({ page }) => {
  await page.goto(`${BASE_URL}/contatti?motivo=gruppo#demo`, { waitUntil: 'load' });
  await expect(page.locator('[name=topic]')).toHaveValue('Presentazione di gruppo');
  await page.goto(`${BASE_URL}/contatti#demo`, { waitUntil: 'load' });
  await expect(page.locator('[name=topic]')).toHaveValue('Richiesta demo');
  await page.getByRole('link', { name: 'Iscriviti alla presentazione →', exact: true }).press('Enter');
  await expect(page.locator('[name=topic]')).toHaveValue('Presentazione di gruppo');
  await page.goBack({ waitUntil: 'load' });
  await expect(page.locator('[name=topic]')).toHaveValue('Richiesta demo');
  await page.goto(`${BASE_URL}/demo?piano=solo&interesse=tassa-soggiorno&servizio=max-full`, { waitUntil: 'load' });
  await expect(page.locator('form[data-demo-form]')).toHaveCount(0);
  await page.getByRole('tab', { name: '2. Prepara il Web Check-in', exact: true }).click();
  await expect(page.getByRole('tab', { name: '2. Prepara il Web Check-in', exact: true })).toHaveAttribute('aria-selected', 'true');
  await page.locator('[data-contact-entry]').click();
  await expect(page.locator('[name=topic]')).toHaveValue('Richiesta demo');
  await expect(page.locator('[data-commercial-plan]')).toHaveValue('solo');
  await expect(page.locator('[data-commercial-interest]')).toHaveValue('tassa-soggiorno');
  await expect(page.locator('[data-commercial-max]')).toBeChecked();
});

for (const width of [1440, 390]) {
  test(`selettori e WhatsApp accessibili a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 950 });
    await page.goto(`${BASE_URL}/contatti#demo`, { waitUntil: 'load' });
    const topic = page.getByRole('combobox', { name: 'Motivo di contatto *', exact: true });
    await expect(page.locator('select.select2-hidden-accessible')).toHaveCount(5);
    await topic.scrollIntoViewIfNeeded();
    await page.screenshot({ path: info.outputPath(`selettore-chiuso-${width}.png`) });
    await topic.press('Enter');
    await expect(page.getByRole('option', { name: 'Demo personalizzata', exact: true })).toBeVisible();
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.screenshot({ path: info.outputPath(`selettore-aperto-${width}.png`) });
    await topic.press('ArrowDown'); await topic.press('Enter');
    await expect(page.locator('[name=topic]')).toHaveValue('Presentazione di gruppo');
    await expect(page.locator('[data-group-hint]')).toBeVisible();
    await expect(page.locator('[name=demo_mode]')).toBeDisabled();
    await topic.press('Enter'); await topic.press('Escape');
    await expect(topic).toHaveAttribute('aria-expanded', 'false');
    await topic.press('Tab'); await expect(page.locator('[name=name]')).toBeFocused();
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    await expect(page.locator('form .form-status')).toContainText('Controlla i campi obbligatori');
    await page.getByRole('combobox', { name: 'Piano', exact: true }).click();
    await page.getByRole('option', { name: /SOLO.*1 appartamento/i }).click();
    await expect(page.locator('[name=plan]')).toHaveValue('solo');
    await page.getByRole('combobox', { name: 'Piano', exact: true }).click();
    await page.getByRole('option', { name: 'Da valutare insieme', exact: true }).click();
    await expect(page.locator('[name=plan]')).toHaveValue('');
    await page.getByRole('button', { name: 'Annulla tutte le scelte', exact: true }).click();
    await expect(page.getByRole('combobox', { name: 'Piano', exact: true })).toContainText('Da valutare insieme');
    await page.goto(`${BASE_URL}/#contenuto`, { waitUntil: 'load' });
    const whatsapp = page.getByRole('link', { name: 'Contattaci su WhatsApp, si apre in una nuova scheda', exact: true });
    await expect(whatsapp).toBeVisible();
    await expect(whatsapp).toHaveAttribute('href', /^https:\/\/wa.me\/393930991968\?text=/);
    await expect(whatsapp).toHaveAttribute('target', '_blank');
    const geometry = await whatsapp.boundingBox(); expect(geometry.x + geometry.width).toBeLessThanOrEqual(width); expect(geometry.y + geometry.height).toBeLessThanOrEqual(950);
    for (const label of ['.hero-benefit-title', '.hero-mini-cards small']) {
      for (const text of await page.locator(label).all()) {
        const lines = await text.evaluate(element => { const range = document.createRange(); range.selectNodeContents(element); return [...range.getClientRects()].map(rect => ({ x: rect.x, y: rect.y, width: rect.width, height: rect.height })); });
        for (const rect of lines) expect(rect.x < geometry.x + geometry.width && rect.x + rect.width > geometry.x && rect.y < geometry.y + geometry.height && rect.y + rect.height > geometry.y).toBe(false);
      }
    }
    await whatsapp.focus(); await page.screenshot({ path: info.outputPath(`whatsapp-${width}.png`) });
    // Verifica l'apertura della scheda senza consentire traffico esterno al runtime isolato.
    const outbound = page.context().waitForEvent('request', { predicate: request => request.url().startsWith('https://wa.me/393930991968') });
    const popup = page.waitForEvent('popup'); await whatsapp.press('Enter');
    const opened = await popup; expect((await outbound).url()).toContain('https://wa.me/393930991968'); await opened.close();
    for (const route of ['/moduli', '/ampliamenti', '/prezzi', '/demo', '/privacy', '/cookie', '/note-legali']) {
      await page.goto(`${BASE_URL}${route}`, { waitUntil: 'load' }); await expect(whatsapp).toBeVisible();
    }
  });
}
