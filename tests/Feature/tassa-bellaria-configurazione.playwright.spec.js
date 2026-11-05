import { test, expect, BASE_URL } from '../Support/playwright.js';

test.use({headless:true});
for (const ruolo of ['con', 'admin']) {
  test(`Configurazione automatica e tre tab senza duplicazioni: ${ruolo}`, async ({page}) => {
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/Nome di accesso o email/i).fill(`tassa-${ruolo}`);
    await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
    await page.getByRole('button', {name:/Entra/i}).click();
    await expect(page).not.toHaveURL(/\/login$/);
    const ids = await (await page.request.get(`${BASE_URL}/tassa-fixture-ids.json`)).json();
    await page.goto(`${BASE_URL}/tassa_di_soggiorno?struttura_id=${ids.struttura_con}`);
    await expect(page.locator('[data-tassa-stato]')).toHaveAttribute('data-tassa-stato','automatica');
    await expect(page.locator('[data-configurazione-immagine]')).toHaveCount(1);
    await expect(page.locator('#pane-configurazione input')).toHaveCount(1);
    await expect(page.locator('#anno-fiscale')).toHaveValue('2026');
    await expect(page.locator('[name=tassa_soggiorno],[name=giorni_massimo],[name=inizio],[name=fine]')).toHaveCount(0);
    await page.locator('#tab-esenzioni').click();
    await expect(page.locator('#esenzioni-table')).toBeVisible();
    await expect(page.locator('#esenzioni-table tbody tr')).toHaveCount(8);
    await expect(page.locator('#pane-esenzioni img,#pane-esenzioni input[type=file],#pane-esenzioni form')).toHaveCount(0);
    await expect(page.locator('#pane-esenzioni')).toContainText('777');
    await page.locator('#tab-stampa').click();
    await expect(page.locator('[data-configurazione-immagine]')).toBeVisible();
    await expect(page.locator('#ricevuta-mostra')).toBeChecked();
    await page.locator('#ricevuta-mostra').uncheck();
    await expect(page.locator('#ricevuta-foto')).toBeDisabled();
    await expect(page.locator('#ricevuta-senza-immagine')).toHaveValue('1');
    await page.locator('#ricevuta-mostra').check();
    await expect(page.locator('#ricevuta-foto')).toBeEnabled();
    await expect(page.locator('#ricevuta-senza-immagine')).toHaveValue('0');
    const popup = page.waitForEvent('popup');
    await page.getByRole('link',{name:'Anteprima ricevuta',exact:true}).click();
    const ricevuta = await popup;
    await expect(ricevuta.locator('[data-tassa-totale]')).toHaveAttribute('data-tassa-totale','12');
    await expect(ricevuta.locator('.ricevuta-software')).toContainText('Tanggo Platform | Versione 2.0');
    await ricevuta.close();
    await page.screenshot({path:`/private/tmp/ids-auto-ui-stampa-${ruolo}.png`,fullPage:true});
    await page.setViewportSize({width:390,height:844});
    await page.locator('#tab-configurazione').click();
    await expect(page.locator('#pane-configurazione')).toBeVisible();
    await expect(page.locator('[data-configurazione-immagine]')).toHaveCount(1);
    await page.screenshot({path:`/private/tmp/ids-auto-ui-mobile-${ruolo}.png`,fullPage:true});
  });
}


test('Riallineamento legacy esplicito, valori visibili e rapporto utilizzabile', async ({page}) => {
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/Nome di accesso o email/i).fill('tassa-legacy');
  await page.getByLabel(/Password personale/i).fill('Password-tassa-fixture-123!');
  await page.getByRole('button', {name:/Entra/i}).click();
  await expect(page).not.toHaveURL(/\/login$/);
  await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto?mese=6&anno=2026`);
  await expect(page).toHaveURL(/tassa_di_soggiorno\?anno_fiscale=2026/);
  await expect(page.locator('[data-tassa-stato]')).toHaveAttribute('data-tassa-stato', 'configurazione_legacy_discordante');
  await expect(page.locator('#pane-configurazione')).toContainText('2026-03-01');
  await expect(page.locator('#pane-configurazione')).toContainText('2026-06-01');
  await expect(page.locator('#pane-configurazione')).toContainText('2026-10-01');
  await expect(page.locator('#pane-configurazione')).toContainText('2026-09-30');
  await page.getByRole('button', {name:'OK',exact:true}).click();
  await page.locator('#consenso-riallineamento').check();
  await page.getByRole('button', {name:'Riallinea al profilo certificato', exact:true}).click();
  await page.getByRole('button', {name:'Sì, salva',exact:true}).click();
  await page.getByRole('button', {name:'OK',exact:true}).click();
  await expect(page.locator('[data-tassa-stato]')).toHaveAttribute('data-tassa-stato','automatica');
  await expect(page.locator('#consenso-riallineamento')).toHaveCount(0);
  await page.goto(`${BASE_URL}/tassa_di_soggiorno/rapporto?mese=6&anno=2026`);
  await expect(page).toHaveURL(/rapporto/);
  await expect(page.locator('body')).not.toContainText('Configurazione legacy discordante');
});
