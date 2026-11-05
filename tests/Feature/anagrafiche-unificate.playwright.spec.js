import {test, expect, BASE_URL} from '../Support/playwright.js';
async function login(page, role) {
  await page.context().clearCookies();
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/Nome di accesso o email/i).fill(`anagrafica-${role}`);
  await page.getByLabel(/Password personale/i).fill('Password-anagrafica-sintetica-123!');
  await page.getByRole('button', {name:/Entra/i}).click();
  await expect(page).not.toHaveURL(/\/login$/);
}
for (const width of [1440,768,390]) {
  test(`Anagrafica unica e permessi a ${width}px`, async ({page}, info) => {
    test.setTimeout(90000);
    await page.setViewportSize({width,height:900});
    const errors=[];page.on('pageerror',e=>errors.push(e.message));
    await login(page,'super');
    const ids=await (await page.request.get(`${BASE_URL}/anagrafiche-fixture.json`)).json();
    await page.goto(`${BASE_URL}/superadmin/strutture/${ids.struttura}/edit`);
    await expect(page.locator('[name=classificazione_id]')).toHaveValue(String(ids.classificazione));
    await expect(page.locator('[name=camere_disponibili]')).toHaveValue('17');
    await expect(page.locator('[name=regione]')).toHaveValue('Lazio');
    await expect(page.locator('[name=provincia]')).toHaveValue('RM');
    await expect(page.locator('[name=citta]')).toHaveValue('Roma');
    await page.locator('[name=telefono]').fill(`06111${width}`);
    await page.getByRole('button',{name:'Salva struttura',exact:true}).click();
    const confirm=page.getByRole('button',{name:/Sì, (modifica|salva)/});
    await confirm.waitFor({state:'visible',timeout:3000}).catch(()=>{});
    if (await confirm.isVisible()) await confirm.click();
    const ok=page.getByRole('button',{name:'OK',exact:true});
    await ok.waitFor({state:'visible',timeout:3000}).catch(()=>{});
    if (await ok.isVisible()) await ok.click();
    await expect(page).toHaveURL(/\/superadmin\/strutture$/);
    await login(page,'admin');
    await page.goto(`${BASE_URL}/admin/strutture/${ids.struttura}/edit`);
    await expect(page.locator('[name=telefono]')).toHaveValue(`06111${width}`);
    await expect(page.locator('[name=classificazione_id]')).toHaveValue(String(ids.classificazione));
    await expect(page.locator('[name=nome_struttura]')).toBeEditable();
    await page.locator('[name=nome_struttura]').fill(`Hotel Canonico ${width}`);
    await expect(page.locator('[name=indirizzo]')).toBeEditable();
    await page.locator('[name=indirizzo]').fill(`Via Canonica ${width}`);
    await page.getByRole('button',{name:'Salva struttura',exact:true}).click();
    await page.getByRole('button',{name:/Sì, (modifica|salva)/}).click();
    await page.getByRole('button',{name:'OK',exact:true}).click();
    await expect(page).toHaveURL(/\/admin\/strutture$/);
    await page.goto(`${BASE_URL}/admin/strutture/${ids.struttura}/edit`);
    await expect(page.locator('[name=nome_struttura]')).toHaveValue(`Hotel Canonico ${width}`);
    await expect(page.locator('[name=indirizzo]')).toHaveValue(`Via Canonica ${width}`);
    await page.screenshot({path:info.outputPath(`admin-${width}.png`),fullPage:true});
    for (const role of ['owner','hotel','reception']) {
      await login(page,role);
      const response=await page.goto(`${BASE_URL}/struttura?sid=${ids.struttura}`);
      expect(response.status()).toBe(200);
      await expect(page.locator('[data-campo=telefono]')).toHaveText(`06111${width}`);
      await expect(page.locator('[data-campo=classificazione]')).toHaveText('3 stelle');
      await expect(page.locator('[data-campo=nome_struttura]')).toHaveText(`Hotel Canonico ${width}`);
      await expect(page.locator('[data-campo=indirizzo]')).toHaveText(`Via Canonica ${width}`);
      await expect(page.locator('[name=nome_struttura]')).toHaveCount(0);
      await expect(page.locator('[name=camere_reali_enabled]')).toHaveCount(role==='reception'?0:2);
      expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1)).toBe(true);
      await page.screenshot({path:info.outputPath(`${role}-${width}.png`),fullPage:true});
    }
    expect(errors).toEqual([]);
  });
}

test('Alta titolare: nuova anagrafica unica e consultazione senza modifica', async ({page}) => {
  test.setTimeout(60000);
  await login(page,'owner');
  const ids=await (await page.request.get(`${BASE_URL}/anagrafiche-fixture.json`)).json();
  await page.goto(`${BASE_URL}/strutture/nuova`);
  await page.locator('[name=tipologia_generale_id]').selectOption(String(ids.generale));
  await page.locator('[name=tipologia_struttura_id]').selectOption(String(ids.tipo));
  await page.locator('[name=classificazione_id]').selectOption(String(ids.classificazione));
  for (const [name,value] of Object.entries({nome_struttura:'Nuovo hotel del titolare',nazione:'Italia',regione:'Lazio',provincia:'RM',citta:'Roma',indirizzo:'Via Nuova',cap:'00100',telefono:'06123456',email:'nuova-struttura@example.invalid'})) await page.locator(`[name=${name}]`).fill(value);
  await page.locator('[name=tipo_apertura]').selectOption('Annuale');
  await page.getByRole('button',{name:'Registra struttura',exact:true}).click();
  await page.getByRole('button',{name:/Sì, salva/}).click();
  await page.getByRole('button',{name:'OK',exact:true}).click();
  await expect(page).toHaveURL(/\/struttura\?sid=\d+$/);
  await expect(page.locator('[data-campo=nome_struttura]')).toHaveText('Nuovo hotel del titolare');
  await expect(page.locator('[name=nome_struttura]')).toHaveCount(0);
});
