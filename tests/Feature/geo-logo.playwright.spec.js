import { test, expect, BASE_URL } from '../Support/playwright.js';

const PASSWORD = 'Password-fixture-123!';
const IMAGE = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAIAAAD91JpzAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAC0lEQVQImWNgQAYAAA4AAbGa6gYAAAAASUVORK5CYII=', 'base64');

async function login(page, role) {
  await page.goto(`${BASE_URL}/login`);
  await page.locator('input[name="login"]').fill(`fixture-${role}`);
  await page.locator('input[name="password"]').fill(PASSWORD);
  await page.getByRole('button', { name: /Entra/i }).click();
  await expect(page).not.toHaveURL(/\/login$/);
}

for (const role of ['super_admin', 'admin']) {
  test(`${role}: login, menu, HTTP 200, logo visibile e ricerca`, async ({ page }) => {
    await login(page, role);
    const response = await page.goto(`${BASE_URL}/geo/comuni/logo`);
    expect(response.status()).toBe(200);
    await expect(page.getByRole('link', { name: 'Geo Comuni (logo)', exact: true })).toBeVisible();
    const image = page.getByAltText('Logo Comune Fixture Logo');
    await expect(image).toBeVisible();
    await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
    const search = page.locator('#geo-comuni-search');
    await search.fill('990001');
    await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), search.press('Enter')]);
    await expect(page.locator('tbody tr')).toHaveCount(1);
    await expect(page.locator('tbody')).toContainText('Comune Fixture Logo');
    await search.fill('inesistente');
    await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), search.press('Enter')]);
    await expect(page.locator('tbody')).toContainText('Nessun comune trovato');
  });
}

test('admin: upload, sostituzione e rimozione su fixture con CSRF', async ({ page }) => {
  await login(page, 'admin');
  await page.goto(`${BASE_URL}/geo/comuni/logo?q=990001`);
  const row = page.locator('tbody tr').filter({ hasText: 'Comune Fixture Logo' });
  const input = row.locator('input[name="logo"]');
  for (const name of ['primo.png', 'sostituzione.png']) {
    await input.setInputFiles({ name, mimeType: 'image/png', buffer: IMAGE });
    await row.getByRole('button', { name: 'Carica', exact: true }).click();
    await page.locator('.swal2-confirm').click();
    await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), page.getByRole('button', { name: 'OK', exact: true }).click()]);
    await expect(page).toHaveURL(/q=990001/);
    await expect(page.locator('.alert-success').first()).toContainText('Logo caricato');
    await expect(page.locator('.swal2-title')).toHaveText('Operazione completata');
    await page.getByRole('button', { name: 'OK', exact: true }).click();
    const image = page.getByAltText('Logo Comune Fixture Logo');
    await expect.poll(() => image.evaluate(img => img.complete && img.naturalWidth > 0)).toBe(true);
  }
  // POST senza token: verificare il rifiuto sul server HTTP reale di test.
  const storeUrl = await row.locator('form').first().getAttribute('action');
  const rejected = await page.context().request.post(storeUrl, { multipart: { logo: { name: 'logo.png', mimeType: 'image/png', buffer: IMAGE } } });
  expect(rejected.status()).toBe(419);
  page.on('dialog', dialog => dialog.accept());
  await row.getByRole('button', { name: 'Rimuovi', exact: true }).click();
  await page.locator('.swal2-confirm').click();
  await Promise.all([page.waitForNavigation({ waitUntil: 'load' }), page.getByRole('button', { name: 'OK', exact: true }).click()]);
  await expect(page).toHaveURL(/q=990001/);
  await expect(page.locator('tbody')).toContainText('Nessun logo');
  await expect(page.getByAltText('Logo Comune Fixture Logo')).toHaveCount(0);
});

test('proprietario: menu assente e accesso diretto vietato', async ({ page }) => {
  await login(page, 'proprietario');
  await expect(page.getByRole('link', { name: 'Geo Comuni (logo)', exact: true })).toHaveCount(0);
  const response = await page.goto(`${BASE_URL}/geo/comuni/logo`);
  expect(response.status()).toBe(403);
});

test('isolamento browser: blocca URL esterni e redirect, inclusa catena locale', async ({ page, request }) => {
  const identity = await request.get(`${BASE_URL}/__test_identity`);
  expect(identity.status()).toBe(200);
  expect(identity.headers()['x-test-isolation']).toBe(process.env.ISOLATED_RUN_ID);
  const trap = (await identity.json()).trap;
  const forbidden = await request.get(`${trap}/forbidden`);
  expect(forbidden.status()).toBe(403);
  for (const path of ['/__test_external_redirect', '/__test_local_redirect']) {
    const redirected = await request.get(`${BASE_URL}${path}`);
    expect(redirected.status()).toBe(403);
  }
  await expect(page.goto(`${trap}/forbidden`)).rejects.toThrow();
  await expect(page.goto(`${BASE_URL}/__test_external_redirect`)).rejects.toThrow();
  await expect(page.goto('https://external.invalid/forbidden')).rejects.toThrow();
  const proof = await (await request.get(`${BASE_URL}/__test_identity`)).json();
  expect(proof.trapHits).toBe(0);
  expect(proof.deniedRequests).toBeGreaterThan(0);
});
