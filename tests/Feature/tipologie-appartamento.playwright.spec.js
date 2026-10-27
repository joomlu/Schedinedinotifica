import {test, expect, BASE_URL} from '../Support/playwright.js';

for (const width of [1440, 768, 390]) {
  test(`Tipologie realmente selezionabili a ${width}px`, async ({page}, info) => {
    test.setTimeout(150000);
    await page.setViewportSize({width, height:900});
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/Nome di accesso o email/i).fill('anagrafica-super');
    await page.getByLabel(/Password personale/i).fill('Password-anagrafica-sintetica-123!');
    await page.getByRole('button', {name:/Entra/i}).click();
    const ids = await (await page.request.get(`${BASE_URL}/anagrafiche-fixture.json`)).json();
    await page.goto(`${BASE_URL}/superadmin/strutture/${ids.struttura}/edit`);
    const generale = page.locator('[name=tipologia_generale_id]');
    await generale.locator('..').locator('.select2-selection').click();
    await page.getByRole('option', {name:'Extra-alberghiera', exact:true}).click();
    const tipo = page.locator('[name=tipologia_struttura_id]');
    for (const nome of ['Agriturismo', 'Affittacamere', 'Locazione turistica imprenditoriale', 'Locazione turistica non imprenditoriale', 'Ostello']) {
      await tipo.locator('..').locator('.select2-selection').click();
      const option = page.getByRole('option', {name:nome, exact:true});
      await expect(option).not.toHaveAttribute('aria-disabled', 'true');
      await option.click();
      await expect(tipo.locator('option:checked')).toHaveText(new RegExp(nome));
      const classe = page.locator('[name=classificazione_id]');
      const className = nome === 'Agriturismo' ? '3 girasoli' : 'Nessuna';
      await classe.locator('..').locator('.select2-selection').click();
      await page.getByRole('option', {name:className, exact:true}).click();
      await page.getByRole('button', {name:'Salva struttura', exact:true}).click();
      await page.getByRole('button', {name:/^Sì,/}).click();
      await page.getByRole('button', {name:'OK', exact:true}).click();
      await expect(page).toHaveURL(/\/superadmin\/strutture$/);
      await page.goto(`${BASE_URL}/superadmin/strutture/${ids.struttura}/edit`);
      await expect(tipo.locator('option:checked')).toHaveText(new RegExp(nome));
      await expect(classe.locator('option:checked')).toHaveText(new RegExp(className));
    }
    await page.locator('[data-ui=tipologie-filtro]').screenshot({path:info.outputPath(`tipologie-${width}.png`)});
    await generale.locator('..').locator('.select2-selection').click();
    await page.getByRole('option', {name:'Alberghiera', exact:true}).click();
    await tipo.locator('..').locator('.select2-selection').click();
    await page.getByRole('option', {name:'Albergo', exact:true}).click();
    await page.locator('[name=classificazione_id]').locator('..').locator('.select2-selection').click();
    await page.getByRole('option', {name:'3 stelle', exact:true}).click();
    await expect(page.locator('[name=classificazione_id]')).toHaveValue(String(ids.classificazione));
  });

  test(`Profilo e ID nella configurazione Questura a ${width}px`, async ({page}, info) => {
    test.setTimeout(90000);
    await page.setViewportSize({width, height:900});
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/Nome di accesso o email/i).fill('anagrafica-owner');
    await page.getByLabel(/Password personale/i).fill('Password-anagrafica-sintetica-123!');
    await page.getByRole('button', {name:/Entra/i}).click();
    const ids = await (await page.request.get(`${BASE_URL}/anagrafiche-fixture.json`)).json();
    await page.goto(`${BASE_URL}/struttura?sid=${ids.struttura}#configurazione-questura`);
    await expect(page.getByRole('link', {name:'Questura / Alloggiati Web',exact:true,includeHidden:true})).toHaveAttribute('href', /#configurazione-questura$/);
    await page.getByLabel('Profilo Alloggiati Web', {exact:true}).selectOption('appartamenti');
    await page.getByLabel('ID appartamento Alloggiati Web', {exact:true}).fill('000000');
    await page.getByRole('button', {name:'Salva configurazione operativa',exact:true}).click();
    await page.getByRole('button', {name:/^Sì,/}).click();
    await page.getByRole('button', {name:'OK',exact:true}).click();
    await page.waitForLoadState('networkidle');
    const flash=page.getByRole('button',{name:'OK',exact:true});
    if (await flash.isVisible()) await flash.click();
    await expect(page.locator('.swal2-container')).toHaveCount(0);
    await expect(page.getByLabel('Profilo Alloggiati Web', {exact:true})).toHaveValue('appartamenti');
    await expect(page.getByLabel('ID appartamento Alloggiati Web', {exact:true})).toHaveValue('0');
    await expect(page.locator('[name=questura_password]')).toHaveValue('');
    await expect(page.locator('[name=questura_wskey]')).toHaveValue('');
    expect(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1)).toBe(true);
    await page.locator('#configurazione-questura').locator('..').screenshot({path:info.outputPath(`profilo-${width}.png`)});
  });
}
