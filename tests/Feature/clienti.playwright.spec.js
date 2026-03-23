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

test('smoke clienti module', async ({ page }) => {
  await login(page);

  await page.goto(`${BASE_URL}/clienti/nuovo`, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');

  await expect(page).toHaveURL(/\/clienti\/nuovo$/);
  await expect(page.locator('h4, h5').filter({ hasText: 'Cliente - aggiungere' }).first()).toBeVisible();
  await expect(page.locator('body')).not.toContainText('Illuminate\\');
  await expect(page.locator('body')).not.toContainText('ErrorException');
  await expect(page.locator('body')).not.toContainText('QueryException');

  await page.selectOption('#customer-type-cliente', { label: 'Richiesta' });
  await page.locator("input[name='name']").fill('Mario123');
  await expect(page.locator("input[name='name']")).toHaveValue('Mario');
  await page.locator("input[name='surname']").fill('Rossi456');
  await expect(page.locator("input[name='surname']")).toHaveValue('Rossi');

  await page.locator('#select2-customer-group-container').click();
  await page.getByRole('option', { name: 'Sport' }).click();
  await page.locator('#select2-customer-subgroup-container').click();
  await expect(page.getByRole('option', { name: 'Bike' })).toBeVisible();
  await expect(page.getByRole('option', { name: 'Sport invernali' })).toBeVisible();
  await page.getByRole('option', { name: 'Sport invernali' }).click();
  await page.locator('#select2-customer-subgroup1-container').click();
  await expect(page.getByRole('option', { name: 'Sci alpino' })).toBeVisible();
  await expect(page.getByRole('option', { name: 'Snowboard' })).toBeVisible();
  await page.keyboard.press('Escape');

  const cascadeCheck = await page.evaluate(() => {
    const group = document.getElementById('customer-group');
    const subgroup = document.getElementById('customer-subgroup');
    const subgroup1 = document.getElementById('customer-subgroup1');
    if (!group || !subgroup || !subgroup1) return { available: false };

    const groupOptions = Array.from(group.options).filter((option) => option.value);
    if (!groupOptions.length) return { available: false };

    group.value = groupOptions[0].value;
    group.dispatchEvent(new Event('change', { bubbles: true }));

    const selectedGroupId = group.options[group.selectedIndex]?.dataset.groupId || '';
    const visibleSubgroups = Array.from(subgroup.options).filter((option) => option.value && !option.disabled && !option.hidden);
    const subgroupMatchesParent = visibleSubgroups.every((option) => option.dataset.parentId === selectedGroupId);

    if (!visibleSubgroups.length) {
      return { available: true, subgroupCount: 0, subgroup1Count: 0, subgroupMatchesParent };
    }

    subgroup.value = subgroup.options[1]?.value || '';
    subgroup.dispatchEvent(new Event('change', { bubbles: true }));

    const selectedSubgroupId = subgroup.options[subgroup.selectedIndex]?.dataset.groupId || '';
    const visibleSubgroup1 = Array.from(subgroup1.options).filter((option) => option.value && !option.disabled && !option.hidden);
    const subgroup1MatchesParent = visibleSubgroup1.every((option) => option.dataset.parentId === selectedSubgroupId);

    return {
      available: true,
      subgroupCount: visibleSubgroups.length,
      subgroup1Count: visibleSubgroup1.length,
      subgroupMatchesParent,
      subgroup1MatchesParent,
    };
  });

  expect(cascadeCheck.available).toBeTruthy();
  expect(cascadeCheck.subgroupMatchesParent).toBeTruthy();
  expect(cascadeCheck.subgroup1MatchesParent ?? true).toBeTruthy();

  await expect(page.locator('#steparrow-gen-info button[type="submit"][name="save_mode"][value="final"]').first()).toBeVisible();
  await expect(page.locator('.nexttab')).toHaveCount(3);

  await page.getByRole('button', { name: /Anagrafica/i }).last().click();
  await expect(page.locator('#steparrow-description-info')).toHaveClass(/active.*show|show.*active/);

  await page.goto(`${BASE_URL}/clienti`, { waitUntil: 'domcontentloaded' });
  await page.waitForLoadState('networkidle');
  await page.locator("a[href*='/clienti/'][href*='/modifica']").first().click();
  await page.waitForLoadState('networkidle');

  await expect(page).toHaveURL(/\/clienti\/\d+\/modifica/);
  await expect(page.locator('h4, h5').filter({ hasText: 'Cliente - modifica' }).first()).toBeVisible();
  await expect(page.locator('.nexttab')).toHaveCount(0);

  await page.evaluate(() => {
    const trigger = document.getElementById('steparrow-description-info-tab');
    const tab = window.bootstrap.Tab.getOrCreateInstance(trigger);
    tab.show();
  });

  const saveButtons = page.locator('#steparrow-description-info button[type="submit"][name="save_mode"][value="final"]');
  await expect(saveButtons.first()).toBeVisible();
  await confirmPrimaryFlow(page, saveButtons.first());
  await page.waitForLoadState('networkidle');
  await expect(page.locator('body')).toContainText('Cliente aggiornato con successo.');
});
