import {test, expect, BASE_URL} from '../Support/playwright.js';
for (const width of [1440, 390]) {
  for (const role of ['admin', 'super_admin']) {
    test(`Presenze senza selezione: avviso e scelta ${role} a ${width}px`, async ({page}, info) => {
      await page.setViewportSize({width, height:1000});
      await page.goto(`${BASE_URL}/login`);
      await page.locator('[name="login"]').fill(`senza-struttura-${role}`);
      await page.locator('[name="password"]').fill('Fixture-selezione-123!');
      await page.getByRole('button', {name:/Accedi|Entra/i}).click();
      await page.getByRole('button', {name:'Sì, salva', exact:true}).click();
      const accesso = page.waitForResponse(r => r.request().method() === 'POST' && new URL(r.url()).pathname === '/login');
      await page.getByRole('button', {name:'OK', exact:true}).click();
      await accesso;
      await page.waitForLoadState('load');
      await page.goto(`${BASE_URL}/statistica/presenze`);
      await expect(page).toHaveURL(/\/strutture\/seleziona$/);
      await expect(page.getByRole('dialog')).toBeVisible();
      await expect(page.getByRole('heading', {name:'Seleziona una struttura', exact:true})).toBeVisible();
      await expect(page.locator('[data-server-alert="warning"]')).toHaveText('Seleziona una struttura per continuare.');
      const immagine = `/tmp/struttura-assente-avviso-${role}-${width}-${Date.now()}.png`;
      await page.screenshot({path:immagine, fullPage:true});
      console.log(`Evidenza avviso: ${immagine}`);
      await page.getByRole('button', {name:'OK', exact:true}).click();
      const riga = page.locator('tr').filter({hasText:`Hotel sintetico ${role}`});
      await riga.getByRole('button', {name:'Seleziona', exact:true}).click();
      await page.getByRole('button', {name:'Sì, salva', exact:true}).click();
      await page.getByRole('button', {name:'OK', exact:true}).click();
      await expect(riga).toContainText('✓');
      const risposta = await page.goto(`${BASE_URL}/statistica/presenze`);
      expect(risposta.status()).toBe(200);
      await expect(page.locator('#presenze-riepilogo')).toBeVisible();
      await page.reload();
      await expect(page.locator('#presenze-riepilogo')).toBeVisible();
    });
  }
}
