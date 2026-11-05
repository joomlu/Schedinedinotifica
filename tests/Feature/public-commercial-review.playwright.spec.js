import { test, expect, BASE_URL } from '../Support/playwright.js';

test.setTimeout(150000);
const paths = ['/', '/moduli', '/ampliamenti', '/prezzi', '/demo', '/contatti', '/privacy', '/cookie', '/note-legali'];
for (const width of [360, 390, 768, 1024, 1440]) {
  test(`nove pagine pubbliche e navigazione a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('response', response => { if (response.url().startsWith(BASE_URL) && response.status() >= 400) errors.push(`${response.status()} ${response.url()}`); });
    for (const path of paths) {
      await page.goto(`${BASE_URL}${path}`, { waitUntil: 'load' });
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('link[rel=canonical]')).toHaveAttribute('href', `https://schedinedinotifica.tanggo.software${path === '/' ? '/' : path}`);
      await expect(page.locator('meta[property="og:image"]')).toHaveAttribute('content', /schedinanotifica-social\.png$/);
      await page.locator('img').evaluateAll(nodes => nodes.forEach(node => node.loading = 'eager'));
      await expect.poll(() => page.locator('img').evaluateAll(nodes => nodes.every(node => node.complete && node.naturalWidth > 0))).toBe(true);
      for (const image of await page.locator('main img').all()) {
        const details = image.locator('xpath=ancestor::details[1]');
        const openedForCheck = await details.count() > 0 && await details.getAttribute('open') === null;
        if (openedForCheck) await details.locator('summary').first().click();
        if (await image.isVisible()) {
          await image.scrollIntoViewIfNeeded();
          await image.evaluate(async node => { try { await node.decode(); } catch (error) { throw new Error(node.currentSrc + ': ' + error.message); } });
          await expect(image).toBeInViewport();
        }
        if (openedForCheck) await details.locator('summary').first().click();
      }
      await page.evaluate(() => new Promise(resolve => { window.scrollTo({ top: 0, behavior: 'instant' }); requestAnimationFrame(() => requestAnimationFrame(resolve)); }));
      await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
      await expect(page.locator('.whatsapp-contact')).toHaveCSS('background-color', 'rgb(37, 211, 102)');
      if (width >= 1281) {
        const centers = await page.locator('.tanggo-family-strip > *').evaluateAll(nodes => nodes.map(n => { const r = n.getBoundingClientRect(); return r.top + r.height / 2; }));
        expect(Math.max(...centers) - Math.min(...centers)).toBeLessThan(2);
        const demo = await page.locator('.site-nav .nav-cta').boundingBox();
        const access = await page.locator('.header-access').boundingBox();
        expect(access.x - demo.x - demo.width).toBeGreaterThanOrEqual(0);
        expect(access.x - demo.x - demo.width).toBeLessThanOrEqual(24);
      }

      const menu = page.locator('.menu-toggle');
      if (await menu.isVisible()) {
        await menu.click();
        await expect(menu).toHaveAttribute('aria-expanded', 'true');
        await expect(page.locator('#site-nav').getByRole('link', { name: 'Richiedi una demo' })).toBeVisible();
        await expect(page.locator('.whatsapp-contact')).toBeHidden();
        await menu.press('Escape');
        await expect(menu).toHaveAttribute('aria-expanded', 'false');
      }
      const invalid = await page.locator('main a[href^="#"]').evaluateAll(nodes => nodes.filter(a => !document.getElementById(decodeURIComponent(a.hash.slice(1)))).map(a => a.hash));
      expect(invalid).toEqual([]);
      await page.screenshot({ path: info.outputPath(`${path.slice(1) || 'home'}-${width}.png`), fullPage: true });
    }
    expect(errors).toEqual([]);
    await page.goto(`${BASE_URL}/contatti#demo`, { waitUntil: 'load' });
    await expect.poll(() => page.locator('#demo').evaluate(node => node.getBoundingClientRect().top >= document.querySelector('header').getBoundingClientRect().bottom - 2)).toBe(true);
    await expect(page.locator('[name=name]')).toBeVisible();
    await page.locator('[name=name]').fill('Referente sintetico');
    await page.locator('[name=company]').fill('Struttura sintetica');
    await page.getByLabel('Autorizzo il contatto in relazione').check();
    await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
    await expect(page.locator('#contact-error-email')).toContainText('almeno un recapito');
    await expect(page.locator('[name=email]')).toBeFocused();
    if (width < 768) await expect(page.locator('.whatsapp-contact')).toBeHidden();
    else if (await page.locator('.whatsapp-contact').isVisible()) {
      const button = await page.locator('.whatsapp-contact').boundingBox();
      const field = await page.locator('[name=email]').boundingBox();
      expect(button.x < field.x + field.width && button.x + button.width > field.x && button.y < field.y + field.height && button.y + button.height > field.y).toBe(false);
    }
    await page.screenshot({ path: info.outputPath(`errore-recapito-${width}.png`) });
  });
}

test('tour completo, immagini originali e percorsi commerciali', async ({ page }) => {
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(`${BASE_URL}/demo#demo-interattiva`);
  const tabs = page.getByRole('tab');
  await expect(tabs).toHaveCount(5);
  for (let index = 0; index < 5; index++) {
    await expect(tabs.nth(index)).toHaveAttribute('aria-selected', 'true');
    const panel = page.locator('[role=tabpanel]:visible');
    await expect(panel).toHaveCount(1);
    const imageLink = panel.getByRole('link', { name: /Apri schermata a dimensione originale/ });
    const popupPromise = page.waitForEvent('popup'); await imageLink.click();
    const popup = await popupPromise; await popup.waitForLoadState('load');
    expect(popup.url()).toContain('/site-assets/img/prodotto-'); await popup.close();
    if (index < 4) await page.locator('[data-tour-next]').click();
  }
  await expect(page.locator('[data-tour-next]')).toBeDisabled();
  for (let index = 3; index >= 0; index--) { await page.locator('[data-tour-previous]').click(); await expect(tabs.nth(index)).toHaveAttribute('aria-selected', 'true'); }
  await tabs.nth(0).press('End'); await expect(tabs.nth(4)).toBeFocused();
  await tabs.nth(4).press('Home'); await expect(tabs.nth(0)).toBeFocused();
  for (const code of ['solo', 'duo', 'multi', 'strutture', 'oltre7', 'oltre2']) {
    await page.goto(`${BASE_URL}/prezzi`);
    await page.locator(`[data-plan=${code}] a`).click();
    await expect(page.locator('[name=plan]')).toHaveValue(code);
    await expect(page.locator('[name=topic]')).toHaveValue('Informazioni e personalizzazioni');
  }
  await page.goto(`${BASE_URL}/prezzi#max-full`);
  await page.getByRole('link', { name: 'Richiedi il servizio Max Full', exact: true }).click();
  await expect(page.locator('[name=max_full]')).toBeChecked();
  await expect(page.locator('[name=topic]')).toHaveValue('Informazioni e personalizzazioni');
  const requests = new Map();
  for (const path of ['/', '/moduli', '/ampliamenti', '/prezzi', '/demo']) {
    await page.goto(`${BASE_URL}${path}`);
    for (const link of await page.locator('a[href^="/contatti?"]').evaluateAll(nodes => nodes.map(node => ({ href: node.getAttribute('href'), text: node.textContent })))) {
      const url = new URL(link.href, BASE_URL);
      if (!['piano', 'interesse', 'servizio'].some(key => url.searchParams.has(key))) continue;
      expect(url.searchParams.get('motivo'), link.href + ': ' + link.text).toBe(/demo/i.test(link.text) ? 'demo' : 'informazioni');
      requests.set(link.href, url);
    }
  }
  expect(requests.size).toBeGreaterThan(15);
  for (const [href, url] of requests) {
    await page.goto(`${BASE_URL}${href}`);
    await expect(page.locator('[name=topic]')).toHaveValue(url.searchParams.get('motivo') === 'demo' ? 'Richiesta demo' : 'Informazioni e personalizzazioni');
    if (url.searchParams.has('piano')) await expect(page.locator('[name=plan]')).toHaveValue(url.searchParams.get('piano'));
    if (url.searchParams.has('interesse')) await expect(page.locator('[name=interest]')).toHaveValue(url.searchParams.get('interesse'));
    if (url.searchParams.get('servizio') === 'max-full') await expect(page.locator('[name=max_full]')).toBeChecked();
  }
  await page.goto(`${BASE_URL}/`);
  await page.locator('#site-nav').getByRole('link', { name: 'Funzionalità', exact: true }).click(); await expect(page).toHaveURL(`${BASE_URL}/moduli`);
  await page.locator('#site-nav').getByRole('link', { name: 'Prezzi', exact: true }).click(); await expect(page).toHaveURL(`${BASE_URL}/prezzi`);
  await page.locator('.header-access').click(); await expect(page).toHaveURL(`${BASE_URL}/login`);
});

test('errore server conserva scelte e consente richiesta minima con solo telefono', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 1000 });
  await page.goto(`${BASE_URL}/contatti?piano=multi&servizio=max-full#demo`);
  await page.locator('[name=name]').fill('Referente Minimo Sintetico');
  await page.locator('[name=company]').fill('   ');
  await page.locator('[name=phone]').fill('+39 0541 987654');
  await page.getByLabel('Autorizzo il contatto in relazione').check();
  let reply = page.waitForResponse(r => r.request().method() === 'POST' && r.url().endsWith('/contatti/richiesta'));
  await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
  expect((await reply).status()).toBe(422);
  await expect(page.locator('#contact-error-company')).toContainText('Compila il campo');
  await expect(page.locator('[name=company]')).toBeFocused();
  await expect(page.locator('[name=plan]')).toHaveValue('multi');
  await expect(page.locator('[name=max_full]')).toBeChecked();
  await expect(page.locator('[name=phone]')).toHaveValue('+39 0541 987654');
  await page.locator('[name=company]').fill('MINIMO-ISOLATO');
  reply = page.waitForResponse(r => r.request().method() === 'POST' && r.url().endsWith('/contatti/richiesta'));
  await page.getByRole('button', { name: 'Invia la richiesta', exact: true }).click();
  const response = await reply; expect(response.status()).toBe(200); expect((await response.json()).ok).toBe(true);
  await expect(page.getByRole('dialog')).toContainText('richiesta di demo personalizzata è registrata');
  await expect(page.getByRole('dialog')).not.toContainText(/email inviata|controlla la.*posta/i);
});

for (const width of [390, 1440]) {
  test(`servizi e requisiti: immagini coerenti a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    await page.goto(`${BASE_URL}/ampliamenti`, { waitUntil: 'load' });
    await expect(page.locator('#tassa-soggiorno .editorial-aside img')).toHaveAttribute('src', '/site-assets/img/prodotto-tassa.webp');
    await expect(page.locator('#strutture .editorial-aside img')).toHaveAttribute('src', '/site-assets/img/ospitalita-ampliamenti-ai.webp');
    await expect(page.locator('#strutture .editorial-aside figcaption')).toContainText('illustrativa');
    let index = 0;
    for (const image of await page.locator('main img').all()) {
      const details = image.locator('xpath=ancestor::details[1]');
      const opened = await details.count() > 0 && await details.getAttribute('open') === null;
      if (opened) await details.locator('summary').first().click();
      await image.scrollIntoViewIfNeeded();
      await image.evaluate(async node => { try { await node.decode(); } catch (error) { throw new Error(node.currentSrc + ': ' + error.message); } });
      await expect(image).toBeInViewport();
      await image.screenshot({ path: info.outputPath(`immagine-${++index}-${width}.png`) });
      if (opened) await details.locator('summary').first().click();
    }
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.evaluate(() => new Promise(resolve => { scrollTo({ top: 0, behavior: 'instant' }); requestAnimationFrame(() => requestAnimationFrame(resolve)); }));
    await page.screenshot({ path: info.outputPath(`servizi-${width}.png`), fullPage: true });
  });
}
