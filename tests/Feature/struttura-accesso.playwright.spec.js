import { test, expect, BASE_URL } from '../Support/playwright.js';

test.setTimeout(180000);

async function conferma(page, button) {
  await button.click();
  await page.getByRole('button', { name: /^Sì,/ }).click();
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    page.getByRole('button', { name: 'OK', exact: true }).click(),
  ]);
  const flash = page.getByRole('dialog').getByRole('button', { name: 'OK', exact: true });
  try {
    await flash.click({ timeout: 2500 });
  } catch (error) {
    if (error.name !== 'TimeoutError' || await flash.isVisible()) throw error;
  }
}

async function proprietario(page, password) {
  await page.context().clearCookies();
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/Nome di accesso o email/i).fill('sharing-owner@fixture.invalid');
  await page.getByLabel(/Password personale/i).fill(password);
  await conferma(page, page.locator('#accesso').getByRole('button', { name: 'Accedi', exact: true }));
  await expect(page).not.toHaveURL(/\/login$/);
}

test('Il proprietario sceglie l’hotel e torna alla pagina operativa richiesta', async ({ page }) => {
  const fixture = await (await page.request.get(`${BASE_URL}/customer-sharing-fixture.json`)).json();
  for (const path of ['/struttura', '/clienti/nuovo', '/schedine/nuova', '/web-checkin', '/tassa_di_soggiorno']) {
    await proprietario(page, fixture.password);
    await page.goto(`${BASE_URL}/proprietario/condivisione-clienti`);
    await expect(page.getByRole('heading', { name: 'Crea un gruppo di condivisione' })).toBeVisible();
    await page.goto(`${BASE_URL}${path}`, { waitUntil: 'networkidle' });
    await expect(page).toHaveURL(`${BASE_URL}/strutture/seleziona`);
    const notice = page.getByRole('dialog').getByRole('button', { name: 'OK', exact: true });
    try {
      await notice.click({ timeout: 2500 });
    } catch (error) {
      if (error.name !== 'TimeoutError' || await notice.isVisible()) throw error;
    }
    const row = page.locator('tbody tr').filter({ hasText: 'Hotel prova B' });
    await expect(row).toBeVisible();
    await conferma(page, row.getByRole('button', { name: 'Seleziona', exact: true }));
    await expect(page).toHaveURL(`${BASE_URL}${path}`);
    await expect(page.locator('body')).not.toContainText(/403\s*Forbidden|SQLSTATE|Fatal error/);
    if (path === '/struttura') await expect(page.locator('input[name="nome_struttura"]')).toHaveValue('Hotel prova B');
  }
});
