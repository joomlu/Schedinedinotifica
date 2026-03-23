import { test, expect } from '@playwright/test';

const BASE_URL = 'https://schedinedinotifica.test';
const LOGIN = 'hotelK2@schedinedinotifica.test';
const PASSWORD = 'Passw0rd!';

test.use({
  headless: true,
  ignoreHTTPSErrors: true,
});

async function login(page) {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
  await page.getByLabel(/Nome di accesso o email/i).fill(LOGIN);
  await page.getByLabel(/Password personale/i).fill(PASSWORD);
  await page.getByRole('button', { name: /Entra/i }).click();
  await page.waitForLoadState('networkidle');
  await expect(page).not.toHaveURL(/\/login$/);
}

test('smoke struttura module', async ({ page }) => {
  await login(page);

  await page.goto(`${BASE_URL}/struttura`, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');

  await expect(page).toHaveURL(/\/struttura$/);
  await expect(page.locator('h4, h5').filter({ hasText: 'Dati struttura' }).first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Illuminate\\');
  await expect(page.locator('body')).not.toContainText('ErrorException');
  await expect(page.locator('body')).not.toContainText('QueryException');

  await expect(page.locator("input[name='nome_struttura']").first()).toBeVisible();
  await expect(page.locator("input[name='telefono']").first()).toBeVisible();
  await expect(page.locator("input[name='email']").first()).toBeVisible();
  await expect(page.locator('#struttura_latitudine')).toBeVisible();
  await expect(page.locator('#struttura_longitudine')).toBeVisible();

  const cirHelp = page.locator('[data-ui="help-popover"]').first();
  await expect(cirHelp).toBeVisible();
  await cirHelp.click();
  await expect(page.locator('.popover')).toBeVisible();
  await page.keyboard.press('Escape');

  const saveButton = page.getByRole('button', { name: /Salva/i }).last();
  await expect(saveButton).toBeVisible();
  await saveButton.click();
  await expect(page.locator('body')).toContainText('Conferma modifica');
  await page.getByRole('button', { name: /Sì, modifica/i }).click();
  await expect(page.locator('body')).toContainText('Modifica confermata');
  await page.getByRole('button', { name: /^OK$/i }).click();
  await page.waitForLoadState('networkidle');
  await expect(page.locator('body')).toContainText('Struttura aggiornata con successo.');
});
