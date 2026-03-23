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

test('smoke configurazioni modules', async ({ page }) => {
  await login(page);

  const cases = [
    { path: '/gruppi', title: 'Gruppi', createButton: /Nuovo Gruppo/i, modalTitle: /Nuovo Gruppo/i },
    { path: '/titolo', title: 'Titoli', createButton: /Nuovo Titolo/i, modalTitle: /Nuovo Titolo/i },
    { path: '/tipo_cliente', title: 'Tipo Cliente', createButton: /Nuovo Tipo Cliente/i, modalTitle: /Nuovo Tipo Cliente/i },
    { path: '/tipo_alloggiato', title: 'Tipo Alloggiato', readonly: true },
    { path: '/tipo_documento', title: 'Tipo Documento', readonly: true },
    { path: '/rilasciato', title: 'Rilasciato da', createButton: /Nuovo Rilasciato da/i, modalTitle: /Nuovo Rilasciato da/i },
    { path: '/tipovia', title: 'Tipo Via', createButton: /Nuovo Tipo Via/i, modalTitle: /Nuovo Tipo Via/i },
  ];

  for (const item of cases) {
    await page.goto(`${BASE_URL}${item.path}`, { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');
    await expect(page).toHaveURL(new RegExp(item.path.replace('/', '\\/')));
    await expect(page.locator('h4, h5').filter({ hasText: item.title }).first()).toBeVisible();
    await expect(page.locator('body')).not.toContainText('Illuminate\\');
    await expect(page.locator('body')).not.toContainText('ErrorException');
    await expect(page.locator('body')).not.toContainText('QueryException');

    if (item.readonly) {
      await expect(page.locator('button[disabled]').first()).toBeVisible();
      continue;
    }

    const createButton = page.getByRole('button', { name: item.createButton });
    await expect(createButton).toBeVisible();
    await createButton.click();
    await expect(page.getByRole('heading', { name: item.modalTitle })).toBeVisible();
    await page.keyboard.press('Escape');
  }
});
