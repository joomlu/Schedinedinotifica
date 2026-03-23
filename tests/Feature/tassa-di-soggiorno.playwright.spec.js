import { test, expect } from '@playwright/test';

const BASE_URL = 'https://schedinedinotifica.test';
const LOGIN = 'hotelK2@schedinedinotifica.test';
const PASSWORD = 'Passw0rd!';

test.use({
  headless: true,
  ignoreHTTPSErrors: true,
});

test.setTimeout(60000);

async function login(page) {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
  await page.getByLabel(/Nome di accesso o email/i).fill(LOGIN);
  await page.getByLabel(/Password personale/i).fill(PASSWORD);
  await page.getByRole('button', { name: /Entra/i }).click();
  await page.waitForLoadState('networkidle');
  await expect(page).not.toHaveURL(/\/login$/);
}

async function confirmPrimaryFlow(page, trigger) {
  await trigger.click({ force: true });
  await expect(page.locator('body')).toContainText(/Conferma (modifica|salvataggio)/i);
  await page.getByRole('button', { name: /Sì, (modifica|salva)/i }).click();
  await expect(page.locator('body')).toContainText(/(Modifica|Salvataggio) confermat/i);
  await page.getByRole('button', { name: /^OK$/i }).click();
}

async function showTab(page, triggerId, paneId) {
  await page.evaluate(({ triggerId }) => {
    const trigger = document.getElementById(triggerId);
    const tab = window.bootstrap.Tab.getOrCreateInstance(trigger);
    tab.show();
  }, { triggerId });

  await expect(page.locator(`#${paneId}`)).toHaveClass(/active.*show|show.*active/);
}

test('smoke tassa di soggiorno module', async ({ page }) => {
  await login(page);

  await page.goto(`${BASE_URL}/tassa_di_soggiorno`, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');

  await expect(page).toHaveURL(/\/tassa_di_soggiorno$/);
  await expect(page.locator('h4, h5').filter({ hasText: 'Tassa di soggiorno' }).first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Illuminate\\');
  await expect(page.locator('body')).not.toContainText('ErrorException');
  await expect(page.locator('body')).not.toContainText('QueryException');

  await expect(page.locator('#tab-dati')).toBeVisible();
  await expect(page.locator('#tab-regole')).toBeVisible();
  await expect(page.locator('#tab-esenzioni')).toBeVisible();
  await expect(page.locator('#tab-export')).toBeVisible();

  const inizio = page.locator("input[name='inizio']").first();
  const fine = page.locator("input[name='fine']").first();
  const originalInizio = await inizio.inputValue();
  const originalFine = await fine.inputValue();

  await inizio.evaluate((el) => {
    el.value = '2026-06-15';
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  });
  await fine.evaluate((el) => {
    el.value = '2026-05-15';
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  });

  await confirmPrimaryFlow(page, page.locator('#pane-dati button[type="submit"]').first());
  await page.waitForLoadState('networkidle');
  await expect(page.locator('body')).toContainText('La data di fine deve essere successiva o uguale alla data di inizio.');

  await inizio.evaluate((el, value) => {
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }, originalInizio);
  await fine.evaluate((el, value) => {
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
  }, originalFine);

  await showTab(page, 'tab-esenzioni', 'pane-esenzioni');
  await expect(page.locator('#pane-esenzioni')).toContainText('Qui vanno solo le esenzioni reali');
  await expect(page.locator('#pane-esenzioni')).not.toContainText('Pernottamenti oltre il limite massimo di giorni imponibili');

  await showTab(page, 'tab-export', 'pane-export');
  await expect(page.locator('#pane-export')).toContainText('Non sono esenzioni');

});
