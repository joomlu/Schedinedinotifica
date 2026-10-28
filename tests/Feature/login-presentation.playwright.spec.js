import { test, expect, BASE_URL } from '../Support/playwright.js';

test.setTimeout(90000);

async function inviaAccesso(page) {
  await page.getByRole('button', { name: 'Accedi', exact: true }).click();
  await page.getByRole('button', { name: 'Sì, salva', exact: true }).click();
  const risposta = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/login');
  await page.getByRole('button', { name: 'OK', exact: true }).click();
  await risposta;
  await page.waitForLoadState('load');
}


for (const width of [1440, 768, 390, 320]) {
  test(`accesso accessibile e responsive a ${width}`, async ({ page }, info) => {
    await page.setViewportSize({ width, height: 1000 });
    await page.goto(`${BASE_URL}/login`);
    await expect(page.getByRole('heading', { name: 'Accesso struttura' })).toBeVisible();
    await expect(page.locator('label[for=login]')).toHaveCSS('font-size','16px');
    await expect(page.locator('label[for=login]')).toHaveCSS('font-weight','400');
    await expect(page.locator('label[for=\"password-input\"]')).toHaveCSS('font-weight','400');
    await expect(page.locator('.sn-login-story-copyright')).toHaveText('© 2026 @Schedine di Notifica - Tanggo Platform | Versione 2.0');
    const whatsapp = page.locator('.whatsapp-contact');
    await expect(whatsapp).toHaveAttribute('aria-label','Contattaci su WhatsApp, si apre in una nuova scheda');
    await expect(whatsapp).toHaveAttribute('href',/^https:\/\/wa.me\/393930991968\?/);
    await expect(whatsapp).toHaveAttribute('target','_blank');
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await expect.poll(() => page.locator('.sn-login img').evaluateAll(nodes => nodes.every(n => n.complete && n.naturalWidth > 0))).toBe(true);
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Vai al modulo di accesso' })).toBeFocused();
    await page.keyboard.press('Enter'); await expect(page.locator('#accesso')).toBeFocused();
    await page.keyboard.press('Tab'); await expect(page.locator('#login')).toBeFocused();
    await page.keyboard.press('Tab'); await expect(page.locator('#password-input')).toBeFocused();
    await page.locator('#password-input').fill('Solo-fixture-123!');
    await page.keyboard.press('Tab'); await expect(page.locator('#password-addon')).toBeFocused();
    await page.keyboard.press('Space');
    await expect(page.locator('#password-input')).toHaveAttribute('type', 'text');
    await expect(page.locator('#password-addon')).toHaveAttribute('aria-label', 'Nascondi password');
    await expect(page.locator('#password-addon')).toHaveAttribute('aria-pressed', 'true');
    await page.keyboard.press('Enter');
    await expect(page.locator('#password-input')).toHaveAttribute('type', 'password');
    await page.keyboard.press('Tab'); await expect(page.locator('#auth-remember-check')).toBeFocused();
    await page.keyboard.press('Space'); await expect(page.locator('#auth-remember-check')).toBeChecked();
    await page.keyboard.press('Tab'); await expect(page.getByRole('link', { name: 'Password dimenticata?' })).toBeFocused();
    await page.keyboard.press('Shift+Tab'); await expect(page.locator('#auth-remember-check')).toBeFocused();
    await page.locator('#password-input').fill('');
    await page.screenshot({ path: info.outputPath(`login-${width}.png`), fullPage: true });
    await page.locator('.footer-bottom').scrollIntoViewIfNeeded();
    await expect(whatsapp).toBeVisible();
    await expect(whatsapp.locator('svg')).toHaveCSS('width','34px');
    await page.getByRole('link', { name: 'Password dimenticata?' }).click();
    await expect(page).toHaveURL(`${BASE_URL}/password/reset`);
    await page.goto(`${BASE_URL}/login`);
    await page.getByRole('link', { name: 'Altri contatti Tanggo Software' }).click();
    await expect(page).toHaveURL(`${BASE_URL}/contatti`);
    await page.goto(`${BASE_URL}/login`);
    await page.getByRole('link', { name: 'Torna al sito' }).click();
    await expect(page).toHaveURL(`${BASE_URL}/`);
  });
}

test('errori reali, vecchio valore, password non ripopolata e CSRF nel browser isolato', async ({ page }, info) => {
  await page.setViewportSize({ width: 390, height: 1000 });
  await page.goto(`${BASE_URL}/login`);
  const csrf = await page.locator('[name=_token]').inputValue(); expect(csrf.length).toBeGreaterThan(20);
  await page.locator('#login').fill('login-assente@example.invalid');
  await page.locator('#password-input').fill('Password-fixture-errata!');
  await inviaAccesso(page);
  await expect(page.locator('#login-error')).toContainText('Credenziali di accesso non riconosciute');
  const dialog = page.locator('.swal2-confirm'); if (await dialog.isVisible()) await dialog.click();
  await expect(page.locator('#login')).toHaveValue('login-assente@example.invalid');
  await expect(page.locator('#password-input')).toHaveValue('');
  await expect(page.locator('#login')).toHaveAttribute('aria-invalid', 'true');
  await expect(page.locator('#login')).toHaveAttribute('aria-describedby', 'login-error');
  await page.screenshot({ path: info.outputPath('login-errore-mobile.png'), fullPage: true });
  await page.locator('#login').fill('');
  await inviaAccesso(page);
  await expect(page.locator('#password-error')).toBeVisible();
  await expect(page.locator('#login-error')).toBeVisible();
});
