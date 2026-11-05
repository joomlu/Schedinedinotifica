import { createHash } from 'node:crypto';
import { readFile } from 'node:fs/promises';
import { test, expect, BASE_URL } from '../Support/playwright.js';

async function login(page, kind) {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="login"]').fill(`ux-${kind}`);
  await page.locator('input[name="password"]').fill('Password-ux-fixture-123!');
  await page.getByRole('button', { name: /Entra/i }).click();
  await expect(page).not.toHaveURL(/\/login$/);
}

async function conferma(page, button, download = false) {
  await button.click();
  await page.getByRole('button', { name: /^Sì,/ }).click();
  const [result] = await Promise.all([
    download ? page.waitForEvent('download') : page.waitForNavigation({ waitUntil: 'networkidle' }),
    page.getByRole('button', { name: 'OK', exact: true }).click(),
  ]);
  if (!download) {
    const flash = page.getByRole('button', { name: 'OK', exact: true });
    if (await flash.isVisible()) await flash.click();
  }
  return result;
}

for (const [name, width] of [['desktop', 1440], ['tablet', 768], ['mobile', 390]]) {
  test(`credenziali ${name}: stati sotto i campi e occhio allineato`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    await login(page, `vuote-${name}`);
    await page.goto(`${BASE_URL}/struttura`);
    const password = page.locator('#questura_password');
    await password.scrollIntoViewIfNeeded();
    const passwordColumn = password.locator('..').locator('..');
    await expect(passwordColumn.locator(':scope > small')).toHaveText('Non configurata');
    const wskey = page.locator('[name="questura_wskey"]');
    await expect(wskey.locator('..').locator(':scope > small')).toHaveText('Non configurata');
    for (const [input, status] of [[password, passwordColumn.locator(':scope > small')], [wskey, wskey.locator('..').locator(':scope > small')]]) {
      const a = await input.boundingBox(); const b = await status.boundingBox();
      expect(b.y).toBeGreaterThanOrEqual(a.y + a.height - 1);
      expect(b.x).toBeGreaterThanOrEqual(a.x - 1);
    }
    const eye = page.locator('[data-password-toggle="questura_password"]');
    const inputBox = await password.boundingBox(); const eyeBox = await eye.boundingBox();
    expect(Math.abs(inputBox.y - eyeBox.y)).toBeLessThan(2);
    expect(Math.abs(inputBox.height - eyeBox.height)).toBeLessThan(2);
    await eye.click(); await expect(password).toHaveAttribute('type', 'text');
    await eye.click(); await expect(password).toHaveAttribute('type', 'password');
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    await page.locator('[name="questura_username"]').fill('ACCOUNT-UI-SINTETICO');
    await password.fill('PASSWORD-UI-SINTETICA');
    await wskey.fill('WSKEY-UI-SINTETICA');
    await conferma(page, page.getByRole('button', { name: 'Salva configurazione operativa', exact: true }));
    await expect(password).toHaveValue(''); await expect(wskey).toHaveValue('');
    await expect(passwordColumn.locator(':scope > small')).toHaveText('Configurata');
    await expect(page.locator('body')).not.toContainText('PASSWORD-UI-SINTETICA');
    await expect(page.locator('body')).not.toContainText('WSKEY-UI-SINTETICA');
    await page.screenshot({ path: `test-results/questura-credenziali-${name}.png` });
  });

  test(`Questura ${name}: errore controllato nel layout e storico navigabile`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    await login(page, 'errore');
    const response = await page.goto(`${BASE_URL}/questura`);
    expect(response.status()).toBe(409);
    await expect(page.locator('#layout-wrapper')).toBeVisible();
    await expect(page.locator('[role="alert"]').filter({ hasText: 'Errore nel ciclo Questura' })).toBeVisible();
    await expect(page.locator('[data-confirm-kind="questura-verify"] button')).toBeDisabled();
    await expect(page.locator('[data-confirm-kind="questura-send"] button')).toBeDisabled();
    await page.locator('#questura-tab-storico-export').click();
    await expect(page.locator('#questura-pane-storico-export')).toHaveClass(/active/);
    await expect(page.locator('#questura-pane-storico-export')).toHaveCSS('opacity', '1');
    await expect(page.getByText('Nessun export registrato.', { exact: true })).toBeVisible();
    await page.locator('#questura-tab-storico-elettronico').click();
    await expect(page.locator('#questura-pane-storico-elettronico')).toHaveClass(/active/);
    await expect(page.locator('#questura-pane-storico-elettronico')).toHaveCSS('opacity', '1');
    await expect(page.locator('body')).not.toContainText('CIPHERTEXT-NON-VALIDO-UX');
    await expect(page.locator('body')).not.toContainText('DecryptException');
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: `test-results/questura-errore-${name}.png` });
  });
}

for (const [name, width] of [['desktop', 1440], ['tablet', 768], ['mobile', 390]]) {
  test(`Questura ${name}: anteprima UTF-8, download, Test/Send sintetici e storico`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    await login(page, `ciclo-${name}`);
    const response = await page.goto(`${BASE_URL}/questura`);
    expect(response.status()).toBe(200);
    expect(response.headers()['cache-control']).toContain('no-store');
    const preview = page.locator('#questura-anteprima');
    await preview.locator('summary').click();
    const txt = (await preview.locator('pre').textContent()).replace(/\r?\n/g, '\r\n');
    expect([...txt]).toHaveLength(name === 'desktop' ? 168 : 338);
    expect(txt).toContain('ŻÓŁĆ'); expect(txt).toContain('ÉLODIE');
    const hash = createHash('sha256').update(txt).digest('hex');
    await expect(preview.locator('code')).toHaveText(hash);
    const download = await conferma(page, page.getByRole('button', { name: 'Scarica TXT Questura', exact: true }), true);
    expect(await readFile(await download.path(), 'utf8')).toBe(txt);
    await preview.screenshot({ path: `test-results/questura-anteprima-dettaglio-${name}.png` });
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: `test-results/questura-anteprima-${name}.png`, fullPage: true });
    // Pulsanti e doppie conferme reali; il solo router isolato inietta SOAP double.
    for (const kind of ['questura-verify', 'questura-send']) {
      await conferma(page, page.locator(`[data-confirm-kind="${kind}"] button`));
      if (kind === 'questura-verify') await expect(page.locator('#questura-pane-storico-elettronico')).toContainText('Verifica positiva');
    }
    await page.locator('#questura-tab-storico-elettronico').click();
    await expect(page.locator('#questura-pane-storico-elettronico')).toContainText('Richiesta inviata');
    await expect(page.locator('#questura-pane-storico-elettronico')).toContainText('accettazione Questura non verificata');
    const [payload] = await Promise.all([page.waitForEvent('download'), page.getByRole('link', { name: 'Scarica payload temporaneo' }).first().click()]);
    expect(await readFile(await payload.path(), 'utf8')).toBe(txt);
    await page.locator('#questura-tab-storico-export').click();
    const [historical] = await Promise.all([page.waitForEvent('download'), page.getByRole('link', { name: 'Scarica TXT temporaneo', exact: true }).click()]);
    expect(await readFile(await historical.path(), 'utf8')).toBe(txt);
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    const dateBox = await page.locator('#questura-pane-storico-export input[type="date"]').boundingBox();
    const panelBox = await page.locator('#questura-pane-storico-export').boundingBox();
    expect(dateBox.x).toBeGreaterThanOrEqual(panelBox.x - 1);
    expect(dateBox.x + dateBox.width).toBeLessThanOrEqual(panelBox.x + panelBox.width + 1);
    await page.locator('#questura-pane-storico-export').screenshot({ path: `test-results/questura-storico-dettaglio-${name}.png` });
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: `test-results/questura-storico-${name}.png`, fullPage: true });
  });
}
