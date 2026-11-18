import { test, expect, BASE_URL } from '../Support/playwright.js';

test.setTimeout(120000);

async function login(page, email, password) {
  await page.context().clearCookies();
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
  await page.getByLabel(/Nome di accesso o email/i).fill(email);
  await page.getByLabel(/Password personale/i).fill(password);
  await conferma(page, page.locator('#accesso').getByRole('button', { name: 'Accedi', exact: true }));
  await page.waitForLoadState('networkidle');
  await expect(page).not.toHaveURL(/\/login$/);
}

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

test('Scelta proprietario, duplicati prudenti, isolamento e tre dimensioni', async ({ page }, testInfo) => {
  const fixture = await (await page.request.get(`${BASE_URL}/customer-sharing-fixture.json`)).json();
  await login(page, 'sharing-owner@fixture.invalid', fixture.password);
  await page.goto(`${BASE_URL}/proprietario/condivisione-clienti`, { waitUntil: 'networkidle' });
  const create = page.locator('form').filter({ has: page.locator('#nome-gruppo') });
  await create.locator('#nome-gruppo').fill('Gruppo prova');
  await create.locator(`input[name="strutture[]"][value="${fixture.hotels.A}"]`).check();
  await create.locator(`input[name="strutture[]"][value="${fixture.hotels.B}"]`).check();
  await conferma(page, create.getByRole('button', { name: 'Verifica le scelte' }));
  await expect(page.getByRole('heading', { name: 'Conferma condivisione clienti' })).toBeVisible();
  await expect(page.locator('.card-body ul')).toContainText('Hotel prova A');
  await expect(page.locator('.card-body ul')).toContainText('Hotel prova B');
  await expect(page.locator('.card-body ul')).not.toContainText('Hotel prova C');
  await page.locator('#conferma-gruppo').check();
  await conferma(page, page.getByRole('button', { name: 'Conferma condivisione' }));
  await expect(page.locator('.alert-success')).toContainText('Condivisione aggiornata');
  await page.locator('summary').filter({ hasText: 'Verifica possibili duplicati' }).click();
  const separate = page.locator('form[action$="/mantieni-separati"]').first();
  await expect(separate).toBeVisible();
  await separate.locator('input[name="conferma"]').check();
  await conferma(page, separate.getByRole('button', { name: 'Mantieni separate', exact: true }));
  await expect(page.locator('.alert-success')).toContainText('mantenute separate');

  for (const width of [1440, 768, 390]) {
    await page.setViewportSize({ width, height: 1000 });
    await expect(page.locator('body')).not.toContainText(/QueryException|SQLSTATE|Fatal error/);
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
    await page.screenshot({ path: testInfo.outputPath(`condivisione-${width}.png`), fullPage: true });
  }

  await login(page, 'sharing-b@fixture.invalid', fixture.password);
  await page.goto(`${BASE_URL}/clienti`, { waitUntil: 'networkidle' });
  await expect(page.locator('tbody tr').filter({ hasText: 'Condivisa' })).toHaveCount(1);
  await expect(page.locator('tbody')).toContainText('Riservata');
  await login(page, 'sharing-c@fixture.invalid', fixture.password);
  await page.goto(`${BASE_URL}/clienti`, { waitUntil: 'networkidle' });
  await expect(page.locator('tbody')).not.toContainText('Condivisa');
  const forbidden = await page.goto(`${BASE_URL}/proprietario/condivisione-clienti`);
  expect(forbidden.status()).toBe(403);
  await login(page, 'sharing-owner@fixture.invalid', fixture.password);
  await page.goto(`${BASE_URL}/proprietario/condivisione-clienti`, { waitUntil: 'networkidle' });
  await page.locator('summary').filter({ hasText: 'Separa una struttura' }).click();
  const exit = page.locator('form[action$="/separa"]');
  await exit.locator('select[name="struttura_id"]').selectOption(String(fixture.hotels.B));
  await exit.locator('select[name="consegna"]').selectOption('0');
  await exit.locator('input[name="conferma"]').check();
  await conferma(page, exit.getByRole('button', { name: 'Conferma separazione' }));
  await expect(page.locator('.alert-success')).toContainText('Struttura separata');
  await expect(page.getByRole('link', { name: /Scarica pacchetto/ })).toHaveCount(0);
  await login(page, 'sharing-b@fixture.invalid', fixture.password);
  await page.goto(`${BASE_URL}/clienti`, { waitUntil: 'networkidle' });
  await expect(page.locator('tbody tr').filter({ hasText: 'Condivisa' })).toHaveCount(1);
  await expect(page.locator('tbody')).toContainText('Prudente');
  await expect(page.locator('tbody')).not.toContainText('Riservata');

});
