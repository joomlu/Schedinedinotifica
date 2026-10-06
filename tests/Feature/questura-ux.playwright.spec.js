import { test, expect, BASE_URL } from '../Support/playwright.js';

async function login(page, kind) {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="login"]').fill(`ux-${kind}`);
  await page.locator('input[name="password"]').fill('Password-ux-fixture-123!');
  await page.getByRole('button', { name: /Entra/i }).click();
  await expect(page).not.toHaveURL(/\/login$/);
}

for (const [name, width] of [['desktop', 1440], ['mobile', 390]]) {
  test(`credenziali ${name}: stati sotto i campi e occhio allineato`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    await login(page, 'vuote');
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
    await page.screenshot({ path: `/private/tmp/questura-ux-credenziali-${name}.png` });
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
    await page.screenshot({ path: `/private/tmp/questura-ux-errore-${name}.png` });
  });
}
