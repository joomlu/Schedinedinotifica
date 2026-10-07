import { test, expect, BASE_URL } from '../Support/playwright.js';

for (const [name, width] of [['desktop', 1440], ['mobile', 390]]) {
  test(`ISTAT ${name}: configurazione protetta, trasporto OFF e storico`, async ({ page }) => {
    await page.setViewportSize({ width, height: 1000 });
    await page.goto(`${BASE_URL}/login`);
    await page.locator('input[name="login"]').fill('istat-ui');
    await page.locator('input[name="password"]').fill('Password-istat-fixture-123!');
    await page.getByRole('button', { name: /Entra/i }).click();
    await expect(page).not.toHaveURL(/\/login$/);
    await page.goto(`${BASE_URL}/istat-tabella-a?mese=2026-04`);
    await expect(page.locator('#layout-wrapper')).toBeVisible();
    await expect(page.locator('#istat-username')).toHaveValue('');
    await expect(page.locator('#istat-password')).toHaveValue('');
    await expect(page.locator('body')).not.toContainText('UI_SYNTHETIC');
    await expect(page.locator('body')).not.toContainText('Modalità prova invio');
    await expect(page.getByRole('button', { name: 'Invia direttamente', exact: true })).toBeDisabled();
    await expect(page.locator('body')).toContainText('Disabilitato');
    await expect(page.locator('body')).toContainText('Alternativa ufficiale');
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    await page.locator('#istat-preview summary').click();
    await expect(page.locator('#istat-preview')).toContainText('C9000001');
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    await page.screenshot({ path: `/private/tmp/istat-cycle-${name}.png` });
  });
}
